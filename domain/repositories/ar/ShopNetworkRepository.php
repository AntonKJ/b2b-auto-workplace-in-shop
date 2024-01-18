<?php

namespace domain\repositories\ar;

use domain\entities\shop\ShopNetwork;
use domain\entities\shop\ShopNetworkCollection;
use domain\entities\shop\ShopNetworkEntityCollectionInterface;
use domain\interfaces\ShopNetworkRepositoryInterface;
use domain\repositories\Hydrator;
use yii\db\ActiveRecordInterface;

class ShopNetworkRepository extends RepositoryBase implements ShopNetworkRepositoryInterface
{

	private $hydrator;

	protected $modelClass;

	function __construct(ActiveRecordInterface $modelClass, Hydrator $hydrator)
	{

		$this->modelClass = $modelClass;
		$this->hydrator = $hydrator;

	}

	protected function _populateShopNetwork(array $data): ShopNetwork
	{

		return $this->hydrator->hydrate(ShopNetwork::class, [
			'id' => (int)$data['network_id'],
			'title' => $data['name'],
			'description' => $data['descr'],
			'color' => $data['font_color'],
			'class' => $data['css_class'],
		]);
	}

	public function findAll(): ShopNetworkEntityCollectionInterface
	{

		$data = new ShopNetworkCollection();

		$class = $this->modelClass;
		$query = $class::find()->asArray();

		foreach ($query->each() as $row)
			$data[] = $this->_populateShopNetwork($row);

		return $data;
	}
}