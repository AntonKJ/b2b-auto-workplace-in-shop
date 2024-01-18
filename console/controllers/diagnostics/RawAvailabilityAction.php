<?php

namespace console\controllers\diagnostics;

use common\models\Shop;
use domain\entities\shop\ShopStock;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\repositories\ar\ShopStockRepository;
use domain\services\GoodAvailabilityService;
use Exception;
use Yii;
use yii\base\Action;
use yii\console\ExitCode;
use yii\console\widgets\Table;

class RawAvailabilityAction extends Action
{

	/**
	 * @var GoodAvailabilityService
	 */
	protected $availabilityService;
	protected $shopStockRepository;

	public function __construct($id,
	                            $controller,
	                            GoodAvailabilityServiceInterface $availabilityService,
	                            ShopStockRepository $shopStockRepository,
	                            $config = [])
	{
		$this->availabilityService = $availabilityService;
		$this->shopStockRepository = $shopStockRepository;
		parent::__construct($id, $controller, $config);
	}

	/**
	 * @param int $zoneId
	 * @param array $goods
	 * @return int
	 * @throws Exception
	 */
	public function run(array $goods)
	{

		$this->controller->stdout(sprintf("Goods: %s\n", implode(', ', $goods)));

		/** @var Shop[] $shops */
		$shops = Shop::find()->indexBy('shop_id')->all();

		$this->controller->stdout("Raw Availability\n\n");
		foreach ($goods as $goodId) {
			$data = $this->availabilityService->getAvailableByGoodId($goodId);
			$output = [];
			foreach ($data as $rowKey => $rowData) {
				$output[] = [
					(string)$rowData['shop_id'],
					isset($shops[$rowData['shop_id']]) ? (string)$shops[$rowData['shop_id']]->getTitle() : null,
					isset($shops[$rowData['shop_id']]) ? (string)$shops[$rowData['shop_id']]->location : null,
					(string)$rowData['amount'],
					(string)$rowData['days'],
					(string)$rowData['from_shop_id'],
				];
			}
			echo Table::widget([
				'headers' => ['shopId', 'shopTitle', 'shopLocation', 'amount', 'days', 'from_shop_id'],
				'rows' => $output,
			]);
			echo "-----------\n";
			$this->controller->stdout(sprintf("AV_RAW_%s: %s\n", $goodId, Yii::$app->cacheAvailability->get('AV_RAW_' . $goodId)));
			$this->controller->stdout(sprintf("AV_RAW_%s_PRE: %s\n", $goodId, Yii::$app->cacheAvailability->get('AV_RAW_' . $goodId . '_PRE')));
			echo "-----------\n";
		}

		$stocks = $this->shopStockRepository->findAllByGoodId($goods)->getAll();
		usort($stocks, static function (ShopStock $a, ShopStock $b) {
			return $a->getShopId() <=> $b->getShopId();
		});
		$this->controller->stdout("Shop stocks\n\n");

		$output = [];
		foreach ($stocks as $stock) {
			$output[] = [
				(string)$stock->getShopId(),
				isset($shops[$stock->getShopId()]) ? (string)$shops[$stock->getShopId()]->getTitle() : null,
				isset($shops[$stock->getShopId()]) ? (string)$shops[$stock->getShopId()]->location : null,
				(string)$stock->getGoodId(),
				(string)$stock->getAmount(),
			];
		}

		echo Table::widget([
			'headers' => ['shopId', 'shopTitle', 'shopLocation', 'goodId', 'amount'],
			'rows' => $output,
		]);

		return ExitCode::OK;
	}

}
