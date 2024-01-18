<?php

namespace domain\repositories\ar;

use domain\entities\shop\ShopStock;
use domain\entities\shop\ShopStockCollection;
use domain\entities\shop\ShopStockEntityCollectionInterface;
use domain\interfaces\ShopStockRepositoryInterface;
use domain\repositories\Hydrator;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

class ShopStockRepository extends RepositoryBase implements ShopStockRepositoryInterface
{

	private $hydrator;

	protected $shopStockModelClass;

	function __construct(ActiveRecordInterface $shopStockModelClass, Hydrator $hydrator)
	{

		$this->shopStockModelClass = $shopStockModelClass;
		$this->hydrator = $hydrator;

	}

	protected function _populateEntity(array $data): ShopStock
	{
		return $this->hydrator->hydrate(ShopStock::class, [
			'id' => (int)$data['shop_stock_id'],
			'shop_id' => (int)$data['shop_id'],
			'good_id' => (int)$data['item_idx'],
			'amount' => (int)$data['amount'],
		]);
	}

	/**
	 * @param $id
	 * @return array|ShopStock[]
	 */
	public function findAllByGoodId($id): ShopStockEntityCollectionInterface
	{
		/* @var $class ActiveRecord */
		$class = $this->shopStockModelClass;

		$reader = $class::find()
			->findByGoodId($id)
			->orderBy(['amount' => SORT_ASC])
			->asArray();

		$data = new ShopStockCollection();

		foreach ($reader->each() as $row)
			$data[] = $this->_populateEntity($row);

		return $data;
	}

}