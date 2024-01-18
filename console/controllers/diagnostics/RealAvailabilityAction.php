<?php

namespace console\controllers\diagnostics;

use common\models\Shop;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\services\GoodAvailabilityService;
use yii\base\Action;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\ArrayHelper;

class RealAvailabilityAction extends Action
{

	/**
	 * @var GoodAvailabilityService
	 */
	protected $availabilityService;

	public function __construct($id, $controller, GoodAvailabilityServiceInterface $availabilityService, $config = [])
	{
		$this->availabilityService = $availabilityService;
		parent::__construct($id, $controller, $config);
	}

	/**
	 * @param int $zoneId
	 * @param array $goods
	 * @return int
	 * @throws \Exception
	 */
	public function run(int $zoneId, array $goods)
	{

		$this->controller->stdout(sprintf("ZoneId: %d\n", $zoneId));
		$this->controller->stdout(sprintf("Goods: %s\n", implode(', ', $goods)));

		$this->controller->stdout("Real Availability\n");

		/** @var Shop[] $shops */
		$shops = Shop::find()->indexBy('shop_id')->all();
		foreach ($goods as $goodId) {
			$data = $this->availabilityService->getRealAvailability($goodId, $zoneId);

			foreach ($data as $otId => $otData) {
				$this->controller->stdout(sprintf("#%d, days: %d, %s\n", $otId, $otData['days'], $otData['name']));
				$output = [];
				foreach ($otData['availability'] as $rowKey => $rowData) {
					$output[] = [
						(string)$rowData['shop_id'],
						isset($shops[$rowData['shop_id']]) ? (string)$shops[$rowData['shop_id']]->getTitle() : null,
						(string)$rowData['qty'],
						(string)$rowData['days'],
						(string)implode(', ', $rowData['from_shop_id'] ?? []),
					];
				}
				echo Table::widget([
					'headers' => ['shopId', 'shopTitle', 'qty', 'days', 'from_shop_id'],
					'rows' => $output,
				]);
			}

		}


		return ExitCode::OK;
	}

}
