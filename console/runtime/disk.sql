SET SESSION query_cache_type=OFF;
SET SESSION group_concat_max_len = 60000;

			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_order_type_group_disk (
				region_id INTEGER NOT NULL,
				shops_from_region_id INTEGER NOT NULL,
				zone_id INTEGER NOT NULL,
				region_group_id INTEGER NOT NULL,
				user_group_id INTEGER NOT NULL,
				group_id INTEGER UNSIGNED NOT NULL,
				order_type_id INTEGER NOT NULL,
				INDEX region_id (region_id),
				INDEX idx_group_region (group_id, shops_from_region_id, zone_id)
			)
			SELECT
				DISTINCT
				r.region_id,
			    r.shops_from_region_id,
			    IF(r.alt_zone_id IS NOT NULL AND r.alt_zone_id != 0, r.alt_zone_id, r.zone_id) zone_id,
				otg.id region_group_id,
				uc.order_type_group_id user_group_id,
				CRC32(CONCAT_WS(',', r.region_id, otg.id, uc.order_type_group_id)) group_id,
				otrRegion.order_type_id
			FROM
				`regions` r
			INNER JOIN order_type_group otg ON otg.id = r.order_type_group_id
			INNER JOIN (
				SELECT DISTINCT
					ou.region_id,
					ouc.order_type_group_id
				FROM `opt_user_category` ouc
				INNER JOIN `opt_users` ou ON ou.ou_category_id = ouc.ou_category_id
			) uc ON uc.region_id = r.region_id
			INNER JOIN `order_type_group_rel` otrRegion ON otrRegion.group_id = otg.id
			INNER JOIN `order_type_group_rel` otrUser ON otrUser.group_id = uc.order_type_group_id AND otrUser.order_type_id = otrRegion.order_type_id
		;

			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_shops_group_disk (
				region_id INTEGER NOT NULL,
				item_idx VARCHAR(50) NOT NULL,
				shops JSON NOT NULL,
				shops_id JSON NOT NULL,
				INDEX idx_reg_item (region_id, item_idx)
			)
			SELECT
				s.region_id,
				ss.item_idx,
				JSON_OBJECTAGG(
					ss.shop_id, ss.amount
				) shops,
				JSON_ARRAYAGG(ss.shop_id) shops_id
			FROM shop_stock ss
			INNER JOIN shops s ON s.shop_id = ss.shop_id
			GROUP BY
				s.region_id, ss.item_idx
			HAVING COUNT(ss.shop_id) > 0
		;

			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_order_type_stock_disk (
				item_idx VARCHAR(50) NOT NULL,
				order_type_group_id BIGINT NOT NULL,
				order_types JSON NOT NULL,
				INDEX idx_order_type_group_id (order_type_group_id, item_idx),
				INDEX idx_order_type_group_id_rev (order_type_group_id, item_idx)
			)
			  SELECT
			    ots.item_idx,
			    ots.order_type_group_id,
			    MAX(IFNULL(ots.amount,0)) amount,
			    JSON_ARRAYAGG(
			      JSON_OBJECT(
			        'id', ots.order_type_id,
			        'amount', ots.amount
			      )
			    ) order_types
			  FROM order_type_stock ots
			  GROUP BY
			    ots.item_idx, ots.order_type_group_id
	    	;
		;
SET @cnt := 100000;
(
		SELECT
		    (@cnt := @cnt + 1) id,
			20 `type`,
			CONCAT_WS(' ', 'диск', 'диски', 'колесо', mt.title, mv.title, mc.title) good_words,
			g.disk_id `good_id`,
			g.disk_id `sku`,
			g.manuf_code `sku_brand`,
			g.disk_id `sku_1c`,
			g.origin_country `country`,
			b.d_producer_id `brand_id`,
			b.code `brand_code`,
			b.name `brand_title`,
			b.code `brand_slug`,
			/* b.logo `brand_logo`, */
			b.pos `brand_sortorder`,
			m.id `model_id`,
			m.title `model_title`,
			m.slug `model_slug`,
			m.sortorder `model_sortorder`,
			JSON_OBJECT(
			    'material', JSON_OBJECT(
                    'id', mt.id,
                    'title', mt.title,
                    'slug', mt.slug,
                    'sortorder', CONCAT(mt.sortorder, ' ', mt.title)
                )
			) as model_params,
			JSON_OBJECT(
				'diameter', CAST(g.diameter AS DECIMAL(5,2)),
				'width', CAST(g.width AS DECIMAL(5,2)),
				'pn', CAST(g.pn AS UNSIGNED),
				'pcd', CAST(g.pcd AS DECIMAL(5,2)),
				'et', CAST(g.et AS DECIMAL(5,2)),
				'cb', CAST(g.dia AS DECIMAL(5,2)),
				'description', g.descr,
				'brand_group', g.brand_group
			) as good_params,
			JSON_OBJECT(
				'id', mv.id,
				'title', mv.title,
				'slug', mv.slug,
				'sortorder', CONCAT(mv.sortorder, ' ', mv.title),
				'color', IF(mc.id IS NULL, NULL, JSON_OBJECT(
					'id', mc.id,
					'title', mc.title,
					'slug', mc.slug,
					'sortorder', CONCAT(mc.sortorder, ' ', mc.title)
				))
			) as variation_params,
			'{}' as offer,
			ots.order_type_groups AS order_type_groups,           
            oubh.users AS hidden_for_user_id,
            rbh.regions AS hidden_for_region_id,
            ad.autos AS auto_modification            
		FROM disks g
		INNER JOIN d_producer b ON b.d_producer_id = g.brand_id AND b.is_published = 1
		INNER JOIN d_model m ON m.id = g.model_id AND m.status = 1
		INNER JOIN d_model_type mt ON mt.id = m.type_id AND mt.status = 1
		INNER JOIN d_model_variation mv ON mv.id = g.variation_id AND mv.status = 1
		LEFT JOIN d_model_color mc ON mc.id = mv.color_id AND mc.status = 1
        INNER JOIN (
        
			SELECT
			  z.item_idx,
			  JSON_OBJECTAGG(
			    z.order_type_group_id,
			    JSON_OBJECT(
					'zone_id', zp.zone_id,
					'price', zp.price,
					
					'amount_max', z.amount,
					'order_types', z.order_types,
					
					'shops', sg.shops,
					'shops_id', sg.shops_id,
					
					'preorder', IF(zp.preorder IS NULL, 0, CAST(zp.preorder AS UNSIGNED)),
					'special', IF(zp.special IS NULL, 0, CAST(zp.special AS UNSIGNED)),
					'sale', IF(zp.sale IS NULL OR zp.sale = 0, 0, 1),
					'offer', IF(zp.offer IS NULL, 0, CAST(zp.offer AS UNSIGNED))
			    )
			  ) order_type_groups
			  
			FROM tmp_order_type_stock_disk z
			INNER JOIN tmp_order_type_group_disk otgg ON otgg.group_id = z.order_type_group_id
			LEFT JOIN tmp_shops_group_disk sg ON sg.region_id = otgg.shops_from_region_id AND sg.item_idx = z.item_idx
			INNER JOIN zone_price zp ON zp.zone_id = otgg.zone_id AND zp.item_idx = z.item_idx
			
			GROUP BY
			  z.item_idx
			  
        ) ots ON ots.item_idx = g.disk_id
        LEFT JOIN (
        	SELECT
        		d_producer_id,
        		GROUP_CONCAT(DISTINCT opt_user_id) users
        	FROM ou_2_dprod_restrict
            GROUP BY
            	d_producer_id
        ) oubh ON oubh.d_producer_id = b.d_producer_id
        LEFT JOIN (
        	SELECT
        		d_producer_id,
        		GROUP_CONCAT(region_id) regions
        	FROM regions_dprod_restrict
            GROUP BY
            	d_producer_id
        ) rbh ON rbh.d_producer_id = b.d_producer_id
        LEFT JOIN (
            SELECT
                disk_id,
                GROUP_CONCAT(crc32(automodel_code_1c)) autos
            FROM auto_disks
            GROUP BY
                disk_id
        ) ad ON ad.disk_id = g.disk_id
        
		)