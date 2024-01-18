<?php

namespace common\models;

use common\models\query\AutopartCategoryQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%autopart_categories}}".
 *
 * @property int $sortorder
 * @property int $id
 * @property null|string $title
 * @property null|string $categoryId
 * @property null|int $images_version
 * @property null|int $add2cart_qty
 */
class AutopartCategory extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%autopart_categories}}';
	}

	public static function find()
	{
		return new AutopartCategoryQuery(static::class);
	}

	public function getId(): int
	{
		return (int)$this->autopart_category_id;
	}

	public function getCategoryId(): ?string
	{
		return $this->apcategory_id;
	}

	public function getTitle(): ?string
	{
		return $this->name;
	}

	public function getSortorder(): int
	{
		return (int)$this->ord_num;
	}

	public function getAddToCartQuantity(): int
	{
		return ($qty = (int)$this->add2cart_qty) > 0 ? $qty : 1;
	}

	public function fields()
	{
		return [
			'id',
			'categoryId',
			'title',
			'sortorder',
		];
	}

}
