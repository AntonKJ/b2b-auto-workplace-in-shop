<?php

namespace api\modules\vendor\modules\toyo\controllers;

use api\modules\vendor\modules\toyo\components\Controller;
use api\modules\vendor\modules\toyo\models\Shop;
use api\modules\vendor\modules\toyo\Module;
use common\models\Service;
use common\models\ShopStock;
use common\models\TyreGood;
use common\models\ZonePrice;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use yii\db\ActiveQuery;
use yii\db\conditions\InCondition;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class ShopController extends Controller
{

	/**
	 * @var GoodAvailabilityService
	 */
	protected $availabilityService;


	public function __construct(string $id, Module $module, GoodAvailabilityServiceInterface $availabilityService, array $config = [])
	{
		$this->availabilityService = $availabilityService;
		parent::__construct($id, $module, $config);
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{

		$behaviors = parent::behaviors();

		$behaviors['verbs'] = [
			'class' => VerbFilter::class,
			'actions' => [
				'index' => ['GET'],
				'view' => ['GET'],
				'goods' => ['GET'],
			],
		];

		return $behaviors;
	}

	public function actionIndex()
	{

		$shops = Shop::find()
			->published()
			->defaultOrder()
			->with([
				'servicesRel' => function (ActiveQuery $q) {
					$q
						->alias('s2s')
						->indexBy('service_id')
						->andWhere([
							's2s.service_id' => [Service::SEASON_STORAGE_SERVICE, Service::TYRE_MOUNT_SERVICE],
						]);
				},
				'timeFrom',
				'timeTo',
				'timeWeekendFrom',
				'timeWeekendTo',
				'region',
			])
			->all();

		return ['response' => ['shops' => $shops]];
	}

	/**
	 * @param $shopId
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionView($shopId)
	{

		$shop = Shop::find()
			->published()
			->byId($shopId)
			->with([
				'servicesRel' => function (ActiveQuery $q) {
					$q
						->alias('s2s')
						->indexBy('service_id')
						->andWhere([
							's2s.service_id' => [Service::SEASON_STORAGE_SERVICE, Service::TYRE_MOUNT_SERVICE],
						]);
				},
				'timeFrom',
				'timeTo',
				'timeWeekendFrom',
				'timeWeekendTo',
				'region',
			])
			->one();

		if ($shop === null)
			throw new NotFoundHttpException('Магазин не найден.');

		return ['response' => ['shop' => $shop]];
	}

	/**
	 * @param $shopId
	 * @param null $sku
	 * @return \yii\console\Response|\yii\web\Response
	 * @throws NotFoundHttpException
	 */
	public function actionGoods($shopId, $sku = null)
	{

		if (($shop = Shop::find()->published()->byId($shopId)->one()) === null)
			throw new NotFoundHttpException('Магазин не найден.');

		$stockQuery = ShopStock::find()
			->alias('ss')
			->select([
				'a.idx id',
				'a.manuf_code sku',
				'IF(ss.amount > 20, 20, ss.amount) amount',
				'IFNULL(zp.price, 0) price',
			])
			->byShopId($shop->getId())
			->innerJoin(TyreGood::tableName() . ' a', [
				'AND',
				'a.idx = ss.item_idx',
				new InCondition('a.prod_code', 'IN', ['nitto']),
				'(IFNULL(a.manuf_code, "") != "")',
			])
			->innerJoin(Shop::tableName() . ' s', 's.shop_id = ss.shop_id')
			->innerJoin(ZonePrice::tableName() . ' zp', 'zp.item_idx = ss.item_idx AND zp.zone_id = s.zone_id')
			->asArray();

		if (!empty($sku))
			$stockQuery->andWhere(['a.manuf_code' => $sku]);

		$out = [];

		/**
		 * @var ShopStock $shopStock
		 */
		foreach ($stockQuery->each(1000) as $shopStock) {

			$available = (object)[
				0 => (int)$shopStock['amount'],
			];

			if (!empty($sku)) {

				$good = TyreGood::find()->byId($shopStock['id'])->one();
				$availability = $this->availabilityService->getRealAvailability($good->getId(), $shop->zone_id);

				if (
					isset($availability[1]['availability'])
					&& \is_array($availability[1]['availability'])
					&& [] !== $availability[1]['availability']
				) {

					$data = [];

					foreach ($availability[1]['availability'] as $shopData) {

						$days = $shopData['days'];
						$qty = $shopData['qty'];

						if (!isset($data[$shopData['shop_id']][(string)$days]))
							$data[$shopData['shop_id']][(string)$days] = 0;

						$data[$shopData['shop_id']][(string)$days] += $qty;
					}

					foreach (array_keys($data) as $shop_id) {

						ksort($data[$shop_id]);

						$prevIndex = null;
						foreach (array_keys($data[$shop_id]) as $nDay) {

							if (null !== $prevIndex)
								$data[$shop_id][$nDay] += $data[$shop_id][$prevIndex];

							$prevIndex = $nDay;
						}
					}

					$available = isset($data[$shop->getId()]) ? (object)$data[$shop->getId()] : new \stdClass();
				}
			}

			$out[] = [
				'sku' => $shopStock['sku'],
				'availability' => $available,
				'price' => (float)$shopStock['price'],
			];
		}

		$response = \Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$response->content = json_encode(['response' => ['stock' => $out]]);

		return $response;
	}

}