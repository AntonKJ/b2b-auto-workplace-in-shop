<?php

namespace console\controllers\diagnostics;

use common\components\OrderTypeGroupGenerated;
use common\models\OptUser;
use common\models\OptUserCategory;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\repositories\ar\ShopStockRepository;
use domain\services\GoodAvailabilityService;
use Exception;
use yii\base\Action;
use yii\console\ExitCode;
use yii\console\widgets\Table;

class UserOrderTypeGroupAction extends Action
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
	 * @param string $userLogin
	 * @return int
	 * @throws Exception
	 */
	public function run(string $userLogin)
	{

		$users = OptUser::find()->byEmail($userLogin);
		$this->controller->stdout(sprintf("Finded users by email `%s`\n\n", $userLogin));

		$output = [];
		/** @var OptUser $user */
		foreach ($users->each() as $user) {

			$region = $user->region;
			/** @var OptUserCategory $category */
			$category = $user->category;

			$intersectedOrderTypeGroup = null;
			if ($region) {
				$intersectedOrderTypeGroup = new OrderTypeGroupGenerated($region, $region, $user);
			}

			$output[] = [
				(string)$user->id,
				(string)$user->email,
				(string)$user->pass,
				(string)$user->fullname,
				(string)$user->ou_category_id,
				$category ? $category->getTitle() : null,
				(string)$user->region_id,
				$region ? $region->getTitle() : null,
				$intersectedOrderTypeGroup->getOrderTypeGroupId(),
			];
		}
		echo Table::widget([
			'headers' => ['id', 'email', 'pass', 'name', 'category_id', 'category_title', 'region_id', 'region_title', 'order_type_group_id'],
			'rows' => $output,
		]);

		return ExitCode::OK;
	}

}
