<?php


namespace common\bootstrap;

use common\models\AutoBrand;
use common\models\AutoModel;
use common\models\AutoModification;
use common\models\OrderType;
use common\models\RegionCrosses;
use common\models\Shop;
use common\models\ShopGroupMoves;
use common\models\ShopNetwork;
use common\models\ShopStock;
use domain\components\FileStorage;
use domain\components\MediaManager;
use domain\interfaces\CarServiceInterface;
use domain\interfaces\GoodAvailabilityServiceInterface;
use domain\repositories\ar\CarRepository;
use domain\repositories\ar\OrderTypeRepository;
use domain\repositories\ar\ShopGroupMoveRepository;
use domain\repositories\ar\ShopNetworkRepository;
use domain\repositories\ar\ShopRepository;
use domain\repositories\ar\ShopStockRepository;
use domain\repositories\Hydrator;
use domain\services\CarService;
use domain\services\GoodAvailabilityService;
use Yii;
use yii\base\BootstrapInterface;
use yii\di\Instance;

class ContainerBootstrap implements BootstrapInterface
{
	public function bootstrap($app)
	{

		$container = Yii::$container;

		$container->setSingletons([
			'mediaManager' => function () {
				$fs = new FileStorage('/', '//b2b-spa.myexample.loc:8039');
				return new MediaManager($fs);
			},
		]);

		// CarService --------------------------------------------------------------------------------

		$container->setSingleton(CarRepository::class, CarRepository::class, [
			Instance::of(AutoBrand::class),
			Instance::of(AutoModel::class),
			Instance::of(AutoModification::class),
			Instance::of(Hydrator::class),
		]);

		$container->setSingleton(CarService::class, CarService::class, [
			Instance::of(CarRepository::class),
		]);

		$container->setSingleton(CarServiceInterface::class, CarService::class);

		// GoodAvailableService --------------------------------------------------------------------------------

		$container->setSingleton(ShopRepository::class, ShopRepository::class, [
			Instance::of(Shop::class),
			Instance::of(RegionCrosses::class),
			Instance::of(Hydrator::class),
		]);

		$container->setSingleton(ShopStockRepository::class, ShopStockRepository::class, [
			Instance::of(ShopStock::class),
			Instance::of(Hydrator::class),
		]);

		$container->setSingleton(ShopNetworkRepository::class, ShopNetworkRepository::class, [
			Instance::of(ShopNetwork::class),
			Instance::of(Hydrator::class),
		]);

		$container->setSingleton(ShopGroupMoveRepository::class, ShopGroupMoveRepository::class, [
			Instance::of(ShopGroupMoves::class),
			Instance::of(Hydrator::class),
		]);

		// OrderType --------------------------------------------------------------------------------

		$container->setSingleton(OrderTypeRepository::class, OrderTypeRepository::class, [
			Instance::of(OrderType::class),
			Instance::of(Hydrator::class),
		]);

		// Services --------------------------------------------------------------------------------

		$container->setSingleton(GoodAvailabilityService::class, GoodAvailabilityService::class, [
			Instance::of(ShopGroupMoveRepository::class),
			Instance::of(ShopNetworkRepository::class),
			Instance::of(ShopStockRepository::class),
			Instance::of(ShopRepository::class),
			Instance::of(OrderTypeRepository::class),
		]);

		$container->setSingleton(GoodAvailabilityServiceInterface::class, GoodAvailabilityService::class);

	}
}