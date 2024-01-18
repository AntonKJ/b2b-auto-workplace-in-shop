<?php

namespace common\models\forms;

use common\interfaces\GoodInterface;
use common\models\Autopart;
use common\models\DiskGood;
use common\models\TyreGood;
use yii\base\Model;

class ShoppingCartRemoveItem extends Model
{

	public $id;
	public $type;

	static public function getTypeOptions()
	{
		return [
			TyreGood::GOOD_ENTITY_TYPE => 'Шина',
			DiskGood::GOOD_ENTITY_TYPE => 'Диск',
			Autopart::GOOD_ENTITY_TYPE => 'Аксессуары',
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => 'ID товара',
			'type' => 'Тип товара',
		];
	}

	public function rules()
	{
		return [

			[['id'], 'required'],
			[['id'], 'match', 'pattern' => '/^[\da-zA-Z\_\-]+$/ui'],

			[['type'], 'required'],
			[['type'], 'in', 'range' => array_keys(static::getTypeOptions())],

		];
	}

	/**
	 * @return GoodInterface
	 * @deprecated
	 */
	public function getGood(): ?GoodInterface
	{

		$good = null;
		switch (true) {

			case $this->type == TyreGood::GOOD_ENTITY_TYPE:

				$good = TyreGood::find()->byId($this->id)->one();
				break;

			case $this->type == DiskGood::GOOD_ENTITY_TYPE:

				$good = DiskGood::find()->byId($this->id)->one();
				break;

			case $this->type == Autopart::GOOD_ENTITY_TYPE:

				$good = Autopart::find()->byId($this->id)->one();
				break;

		}

		/*		if ($good === null) {
					throw new NotFoundHttpException('Товар не найден!');
				}*/

		return $good;
	}

	public function fields()
	{
		return [
			'id',
			'type',
		];
	}

}
