<?php

namespace domain\repositories\ar;

use domain\entities\shop\ShopGroupMove;
use domain\entities\shop\ShopGroupMoveCollection;
use domain\entities\shop\ShopGroupMoveEntityCollectionInterface;
use domain\interfaces\ShopGroupMoveRepositoryInterface;
use domain\repositories\Hydrator;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

class ShopGroupMoveRepository extends RepositoryBase implements ShopGroupMoveRepositoryInterface
{

	private $hydrator;

	protected $shopGroupMoveModelClass;

	function __construct(ActiveRecordInterface $shopGroupMoveModelClass, Hydrator $hydrator)
	{

		$this->shopGroupMoveModelClass = $shopGroupMoveModelClass;
		$this->hydrator = $hydrator;

	}

	protected function _populateEntity(array $data): ShopGroupMove
	{
		return $this->hydrator->hydrate(ShopGroupMove::class, [
			'id' => (int)$data['move_id'],
			'group_id_from' => (int)$data['shop_group_from'],
			'group_id_to' => (int)$data['shop_group_to'],
			'days' => (int)$data['move_days'],
			'priority' => (int)$data['move_mins'],
		]);
	}

	public function findAll(): ShopGroupMoveEntityCollectionInterface
	{
		/* @var $class ActiveRecord */
		$class = $this->shopGroupMoveModelClass;

		$reader = $class::find()
			->orderBy(['move_id' => SORT_ASC])
			->asArray();

		$data = new ShopGroupMoveCollection();

		foreach ($reader->each() as $row) {
			$data[] = $this->_populateEntity($row);
		}

		return $data;
	}

}
