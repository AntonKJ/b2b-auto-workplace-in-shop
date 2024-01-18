SET SESSION query_cache_type=OFF;
SET SESSION group_concat_max_len = 60000;

			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_order_type_group_tyre (
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

			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_shops_group_tyre (
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

			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_order_type_stock_tyre (
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
SET @cnt := 0;
(
        SELECT
            (@cnt := @cnt + 1) id,
            10 `type`,
            CONCAT_WS(' ', 'шина', 'шины', b.`name-rus`) good_words,
            g.idx `good_id`,
            g.idx `sku`,
            g.manuf_code `sku_brand`,
            g.code_1c `sku_1c`,
            g.origin_country `country`,
            b.id `brand_id`,
            b.code `brand_code`,
            b.name `brand_title`,
            b.url `brand_slug`,
            b.Position `brand_sortorder`,
            m.id `model_id`,
            m.name `model_title`,
            m.url `model_slug`,
            m.sortorder `model_sortorder`,
            JSON_OBJECT(
				'type', m.type,
				'season', LOWER(m.season),
				'pin', IF(LOWER(m.pin) = 'y', 1, 0),
				'runflat', CAST(CAST(g.runflat AS CHAR) AS UNSIGNED)
            ) AS model_params,
            JSON_OBJECT(
				'radius', CAST(REPLACE(REPLACE(UPPER(g.rad), 'R', ''), 'C', '') AS DECIMAL(5, 2)),
				'width', CAST(g.width AS DECIMAL(5, 2)),
				'profile', CAST(g.pr AS DECIMAL(5, 2)),
				'commerce', IF(UPPER(g.rad) LIKE '%C', 1, 0),
				'speed_rating', UPPER(g.cc),
				'load_index', g.in_type,
				'tLong', CAST(g.tlong AS DECIMAL(8, 4)),
				'size', g.sz,
				'homologation', g.Homologation,
				'prod_year', g.prod_year
            ) AS good_params,
            '{}' AS variation_params,
            JSON_OBJECT(
				'discount', IF(g.discount IS NULL, 0, CAST(CAST(g.discount AS CHAR) AS UNSIGNED)),
				'new', IF(m.new IS NULL, 0, IF(CAST(m.new AS UNSIGNED) >= 2014, 1, 0))
            ) AS offer,
			ots.order_type_groups AS order_type_groups,
            oubh.users AS hidden_for_user_id,
            rbh.regions AS hidden_for_region_id,
            null AS auto_modification
        FROM assort g
        INNER JOIN producer b ON b.code = g.prod_code
        INNER JOIN (
                   SELECT
                     MAX(id)        id,
                     MAX(code)      code,
                     MAX(prod_code) prod_code,
                     MAX(name)      name,
                     MAX(url)       url,
                     MAX(type)      type,
                     MAX(season)    season,
                     MAX(pin)       pin,
                     MAX(new)       new,
                     MAX(position)  sortorder
                   FROM
                     model
                   GROUP BY
                     prod_code, code
                 ) m ON m.code = g.p_t AND m.prod_code = b.code
                 
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
			  
			FROM tmp_order_type_stock_tyre z
			INNER JOIN tmp_order_type_group_tyre otgg ON otgg.group_id = z.order_type_group_id
			LEFT JOIN tmp_shops_group_tyre sg ON sg.region_id = otgg.shops_from_region_id AND sg.item_idx = z.item_idx
			INNER JOIN zone_price zp ON zp.zone_id = otgg.zone_id AND zp.item_idx = z.item_idx
			
			GROUP BY
			  z.item_idx
			  
        ) ots ON ots.item_idx = g.idx
        LEFT JOIN (
        	SELECT
        		producer_id,
        		GROUP_CONCAT(DISTINCT opt_user_id) users
        	FROM ou_2_prod_restrict
            GROUP BY
            	producer_id
        ) oubh ON oubh.producer_id = b.id
        LEFT JOIN (
        	SELECT
        		producer_id,
        		GROUP_CONCAT(region_id) regions
        	FROM regions_prod_restrict
            GROUP BY
            	producer_id
        ) rbh ON rbh.producer_id = b.id
        
		)