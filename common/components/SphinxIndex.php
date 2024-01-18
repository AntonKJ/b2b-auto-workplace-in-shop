<?php

namespace common\components;


use common\models\OrderTypeStock;
use common\models\ShopStock;
use common\models\ShopStockTotal;
use common\models\Zone;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\Expression;

/**
 *
 * @property \yii\sphinx\Connection $sphinx
 * @property Connection $db
 */
class SphinxIndex extends Component
{

	protected $_db;
	protected $_sphinx;

	/**
	 * @var GoodAvailabilityService
	 */
	protected $_availability;

	public function __construct(GoodAvailabilityServiceInterface $availabilityService, array $config = [])
	{
		parent::__construct($config);
		$this->_availability = $availabilityService;
	}


	/**
	 * @return \yii\sphinx\Connection
	 */
	protected function getSphinx()
	{

		if ($this->_sphinx === null)
			$this->_sphinx = Yii::$app->sphinx;

		return $this->_sphinx;
	}

	/**
	 * @return Connection
	 */
	protected function getDb()
	{

		if ($this->_db === null)
			$this->_db = Yii::$app->db;

		return $this->_db;
	}

	public function getQueryCreateTemporaryTableOrderTypeGroups($tblPrefix = null)
	{

		$sql = "
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_order_type_group_{$tblPrefix} (
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
				{{%regions}} r
			INNER JOIN order_type_group otg ON otg.id = r.order_type_group_id
			INNER JOIN (
				SELECT DISTINCT
					ou.region_id,
					ouc.order_type_group_id
				FROM {{%opt_user_category}} ouc
				INNER JOIN {{%opt_users}} ou ON ou.ou_category_id = ouc.ou_category_id
			) uc ON uc.region_id = r.region_id
			INNER JOIN {{%order_type_group_rel}} otrRegion ON otrRegion.group_id = otg.id
			INNER JOIN {{%order_type_group_rel}} otrUser ON otrUser.group_id = uc.order_type_group_id AND otrUser.order_type_id = otrRegion.order_type_id
		";

		return $sql;
	}

	public function getQueryCreateTemporaryTableShopGroups($tblPrefix = null)
	{

		$sql = "
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_shops_group_{$tblPrefix} (
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
		";

		return $sql;
	}

	public function getQueryCreateTemporaryTableOrderTypeStock($tblPrefix = null)
	{

		$sql = "
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_order_type_stock_{$tblPrefix} (
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
		";

		return $sql;
	}

	public function getPickupPopulateOrderTypeStockQueryString(array $goodIds = [], $tblTable = null)
	{

		$goodFilter = null;
		if ($goodIds !== []) {

			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, $goodIds);

			$goodFilter = ' AND sst.item_idx IN(\'' . implode('\',\'', $goodIds) . '\')';
		}

		return "
{$this->getQueryCreateTemporaryTableOrderTypeGroups($tblTable)};
INSERT INTO {{%order_type_stock}}
(
	order_type_group_id,
	order_type_id,
	item_idx,
	amount
)
SELECT
	otgg.group_id		order_type_group_id,
	ot.ot_id			order_type_id,
	sst.item_idx		item_idx,
	MAX(sst.total_10k)	amount
FROM tmp_order_type_group_{$tblTable} otgg
JOIN {{%order_types}} ot ON ot.ot_id = otgg.order_type_id AND (ot.from_shop_id IS NULL OR ot.from_shop_id = 0)
JOIN {{%shops}} s ON s.region_id = otgg.shops_from_region_id
JOIN {{%shop_stock_total}} sst ON sst.total_10k > 0 AND sst.shop_id = s.shop_id{$goodFilter}
GROUP BY
	otgg.group_id,
	ot.ot_id,
	sst.item_idx
";
	}

	public function getDeliveryPopulateOrderTypeStockQueryString(array $goodIds = [], $tblTable = null)
	{

		$goodFilter = null;
		if ($goodIds !== []) {

			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, $goodIds);

			$goodFilter = ' AND sst.item_idx IN(\'' . implode('\',\'', $goodIds) . '\')';
		}

		return "
{$this->getQueryCreateTemporaryTableOrderTypeGroups($tblTable)};
INSERT INTO {{%order_type_stock}}
(
	order_type_group_id,
	order_type_id,
	item_idx,
	amount
)
SELECT
	
	otgg.group_id		order_type_group_id,
	ot.ot_id			order_type_id,
	sst.item_idx		item_idx,
	MAX(sst.total_10k)	amount
	
FROM tmp_order_type_group_{$tblTable} otgg
JOIN {{%order_types}} ot ON ot.ot_id = otgg.order_type_id AND (ot.from_shop_id IS NOT NULL AND ot.from_shop_id > 0)
JOIN {{%shop_stock_total}} sst ON sst.total_10k > 0 AND sst.shop_id = ot.from_shop_id{$goodFilter}
GROUP BY
	otgg.group_id,
	ot.ot_id,
	sst.item_idx
";
	}

	/**
	 * @param array $goodIds
	 * @throws Exception
	 */
	public function populateOrderTypeStock(array $goodIds): void
	{

		if ([] !== $goodIds)
			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, $goodIds);

		$this->getDb()
			->createCommand()
			->delete(OrderTypeStock::tableName(), [] !== $goodIds ? ['item_idx' => $goodIds] : [])
			->execute();

		// Вычисляем наличие по типам заказа для магазинов
		$this->getDb()->createCommand($this->getPickupPopulateOrderTypeStockQueryString($goodIds))->execute();

		// Вычисляем наличие по типам заказа для доставки
		$this->getDb()->createCommand($this->getDeliveryPopulateOrderTypeStockQueryString($goodIds))->execute();

	}

	/**
	 * @param array $goodIds
	 * @param bool $generateSphinxRowId
	 * @return string
	 */
	public function getUpdateSphinxIndexTyreQueryString(array $goodIds, bool $generateSphinxRowId = false, $tblTable = null): string
	{

		$goodFilter = null;
		if ($goodIds !== []) {

			$goodIds = array_map(static function ($v) {
				return (string)$v;
			}, $goodIds);

			$goodFilter = 'WHERE g.idx IN(\'' . implode('\',\'', $goodIds) . '\')';
		}

		$generateSphinxRowId = $generateSphinxRowId !== false ? '(@cnt := @cnt + 1) id,' : null;

		$newLogic = 0;
		$yearOfNovelty = Yii::$app->global->getYearOfModelNewFlag();

		if ($yearOfNovelty > 0)
			$newLogic = "IF(CAST(m.new AS UNSIGNED) >= {$yearOfNovelty}, 1, 0)";

		return "
        SELECT
            {$generateSphinxRowId}
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
				'new', IF(m.new IS NULL, 0, {$newLogic})
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
			  
			FROM tmp_order_type_stock_{$tblTable} z
			INNER JOIN tmp_order_type_group_{$tblTable} otgg ON otgg.group_id = z.order_type_group_id
			LEFT JOIN tmp_shops_group_{$tblTable} sg ON sg.region_id = otgg.shops_from_region_id AND sg.item_idx = z.item_idx
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
        {$goodFilter}
		";
	}

	/**
	 * @param array $goodIds
	 * @param bool $generateSphinxRowId
	 * @return string
	 */
	public function getUpdateSphinxIndexDiskQueryString(array $goodIds, bool $generateSphinxRowId = true, $tblTable = null): string
	{

		$goodFilter = null;
		if ($goodIds !== []) {

			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, $goodIds);

			$goodFilter = 'WHERE g.disk_id IN(\'' . implode('\',\'', $goodIds) . '\')';
		}

		$generateSphinxRowId = $generateSphinxRowId !== false ? '(@cnt := @cnt + 1) id,' : null;

		return "
		SELECT
		    {$generateSphinxRowId}
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
			  
			FROM tmp_order_type_stock_{$tblTable} z
			INNER JOIN tmp_order_type_group_{$tblTable} otgg ON otgg.group_id = z.order_type_group_id
			LEFT JOIN tmp_shops_group_{$tblTable} sg ON sg.region_id = otgg.shops_from_region_id AND sg.item_idx = z.item_idx
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
        {$goodFilter}
		";
	}

	/**
	 * @param array $goodIds
	 * @throws Exception
	 */
	protected function updateSphinxIndexTyre(array $goodIds): void
	{

		if ($goodIds !== []) {

			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, $goodIds);
		}

		$goodSql = $this->getUpdateSphinxIndexTyreQueryString($goodIds);

		$reader = $this->getDb()
			->createCommand($goodSql)
			->query();

		$transaction = $this->getSphinx()->beginTransaction();

		$this->getSphinx()
			->createCommand()
			->delete('myexample_rt', [] !== $goodIds ? ['good_id' => $goodIds] : [])
			->execute();

		$batchSize = 500;
		while (true) {

			$i = 0;
			$data = [];
			while ($i < $batchSize && ($row = $reader->read())) {

				$data[] = $row;
				$i++;
			}

			if ($data === [])
				break;

			$columns = array_keys($data[0]);
			$data = $this->prepareIndexRow($data);

			$this->getSphinx()
				->createCommand()
				->batchReplace('myexample_rt', $columns, $data)
				->execute();

		}

		$transaction->commit();
	}

	/**
	 * @param array $goodIds
	 * @throws Exception
	 */
	protected function updateSphinxIndexDisk(array $goodIds): void
	{

		$goodFilter = null;
		if ($goodIds !== []) {

			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, $goodIds);

			$goodFilter = 'WHERE g.disk_id IN(\'' . implode('\',\'', $goodIds) . '\')';
		}

		$goodSql = $this->getUpdateSphinxIndexDiskQueryString($goodIds);

		$reader = $this->getDb()
			->createCommand($goodSql)
			->query();

		$transaction = $this->getSphinx()->beginTransaction();

		$this->getSphinx()
			->createCommand()
			->delete('myexample_rt', [] !== $goodIds ? ['good_id' => $goodIds] : [])
			->execute();

		$batchSize = 500;
		while (true) {

			$i = 0;
			$data = [];
			while ($i < $batchSize && ($row = $reader->read())) {

				$data[] = $row;
				$i++;
			}

			if ($data === [])
				break;

			$columns = array_keys($data[0]);
			$data = $this->prepareIndexRow($data);

			$this->getSphinx()
				->createCommand()
				->batchReplace('myexample_rt', $columns, $data)
				->execute();

		}

		$transaction->commit();
	}

	/**
	 * @param $data
	 * @return array
	 */
	protected function prepareIndexRow($data): array
	{

		return array_map(function ($v) {

			$v['available_shop_id'] = new Expression("({$v['available_shop_id']})");
			$v['hidden_for_user_id'] = new Expression("({$v['hidden_for_user_id']})");
			$v['hidden_for_region_id'] = new Expression("({$v['hidden_for_region_id']})");
			$v['auto_modification'] = new Expression("({$v['auto_modification']})");

			return array_values($v);
		}, $data);
	}

	/**
	 * @param array $data
	 * @param bool $withoutSphinx
	 * @throws Exception
	 */
	public function updateGoodsAvailable(array $data, $withoutSphinx = false)
	{

		if ($data === [])
			return;

		$transaction = $this->getDb()->beginTransaction();

		try {

			$goodIds = array_map(function ($v) {
				return (string)$v;
			}, array_keys($data));

			$this->getDb()
				->createCommand()
				->delete(ShopStock::tableName(), [
					'item_idx' => $goodIds,
				])
				->execute();

			$idsByType = [];

			$updateData = [];
			foreach ($data as $goodId => $shops) {

				$idsByType[$shops['type']][] = $goodId;

				foreach ($shops['shops'] as $shopId => $amount) {
					$updateData[] = [
						'amount' => $amount,
						'item_idx' => (string)$goodId,
						'shop_id' => $shopId,
					];
				}
			}

			if ($updateData !== [])
				$this->getDb()
					->createCommand()
					->batchInsert(ShopStock::tableName(), array_keys($updateData[0]), $updateData)
					->execute();

			$transaction->commit();

			if ($withoutSphinx == false) {

				if ($goodIds !== [])
					$this->updateShopStockTotal($goodIds);

				$this->populateOrderTypeStock($goodIds);

				/*
								if (isset($idsByType['tyre']))
									$this->updateSphinxIndexTyre($idsByType['tyre']);

								if (isset($idsByType['disk']))
									$this->updateSphinxIndexDisk($idsByType['disk']);
				*/
			}

		} catch (Exception $e) {

			$transaction->rollBack();
			throw $e;
		}

	}

	/**
	 * @param array|string[] $goodIds
	 * @throws Exception
	 * @throws \Exception
	 */
	protected function updateShopStockTotal(array $goodIds = []): void
	{

		$shopStockTotals = [];

		$zoneIds = Zone::getZoneIds();
		foreach ($goodIds as $goodId) {
			foreach ($zoneIds as $zoneId) {

				$goodsAvailability = $this->_availability->getAvailableByGoodId($goodId, false, false);
				$goodsAvailabilityWithout10k = $this->_availability->getAvailableByGoodId($goodId, false, true);

				$goodsAvailability = $this->_availability->getExpandedAvailability($goodsAvailability, $goodId, $zoneId, false);
				$goodsAvailabilityWithout10k = $this->_availability->getExpandedAvailability($goodsAvailabilityWithout10k, $goodId, $zoneId, false);

				$shopStockTotals[$goodId][$zoneId]['total'] = $this->_availability->getShopStockTotal($goodsAvailability, $zoneId);
				$shopStockTotals[$goodId][$zoneId]['total_10k'] = $this->_availability->getShopStockTotal($goodsAvailabilityWithout10k, $zoneId);
			}
		}

		$updateData = [];
		foreach ($shopStockTotals as $goodId => $zones) {
			foreach ($zones as $zone) {
				foreach ($zone as $shopScope => $orderTypes) {
					foreach ($orderTypes as $orderTypeId => $shops) {
						foreach ($shops as $shopId => $amount) {

							$key = "{$goodId}_{$shopId}";

							if (!isset($updateData[$key]))
								$updateData[$key] = [
									'item_idx' => $goodId,
									'shop_id' => $shopId,
									'total' => 0,
									'total_10k' => 0,
								];

							$updateData[$key][$shopScope] = $amount;
						}
					}
				}
			}
		}

		// Удаляем обновляемые товары из таблицы
		$this->getDb()
			->createCommand()
			->delete(ShopStockTotal::tableName(), [] !== $goodIds ? ['item_idx' => $goodIds] : [])
			->execute();

		if ($updateData !== []) {

			$columns = reset($updateData);
			$columns = array_keys($columns);

			$updateData = array_map('array_values', $updateData);

			Yii::$app->db->createCommand()
				->batchInsert(ShopStockTotal::tableName(), $columns, $updateData)
				->execute();
		}
	}

}
