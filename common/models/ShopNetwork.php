<?php

namespace common\models;

use common\components\file\storageStrategy\ShopNetworkStorageStrategyDefault;
use common\components\file\ThumbnailBehavior;
use HTMLPurifier_Config;

/**
 * This is the model class for table "{{%shop_networks}}".
 *
 * @property integer $network_id
 * @property string $name
 * @property string $descr
 * @property string $font_color
 * @property string $css_class
 */
class ShopNetwork extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shop_networks}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'image' => [
				'class' => ThumbnailBehavior::class,
				'defaultThumbnail' => 'default',
				'thumbnails' => [
					'default' => [
						'target' => ShopNetworkStorageStrategyDefault::class,
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['network_id', 'css_class'], 'required'],
			[['network_id'], 'integer'],
			[['descr'], 'string'],
			[['name'], 'string', 'max' => 63],
			[['font_color'], 'string', 'max' => 100],
			[['css_class'], 'string', 'max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'network_id' => 'Network ID',
			'name' => 'Name',
			'descr' => 'Descr',
			'font_color' => 'Font Color',
			'css_class' => 'Css Class',
		];
	}

	/**
	 * @inheritdoc
	 * @return \common\models\query\ShopNetworkQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \common\models\query\ShopNetworkQuery(get_called_class());
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getShops()
	{
		return $this->hasMany(Shop::class, ['network_id' => 'network_id'])
			->inverseOf('network');
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->network_id;
	}

	/**
	 * @param int $id
	 * @return ShopNetwork
	 */
	public function setId(int $id): ShopNetwork
	{
		$this->network_id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->name;
	}

	/**
	 * @param string $title
	 * @return ShopNetwork
	 */
	public function setTitle(string $title): ShopNetwork
	{
		$this->name = $title;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->descr;
	}

	/**
	 * @param string $description
	 * @return ShopNetwork
	 */
	public function setDescription(string $description): ShopNetwork
	{
		$this->descr = $description;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getColor(): string
	{
		return $this->font_color;
	}

	/**
	 * @param string $color
	 * @return ShopNetwork
	 */
	public function setColor(string $color): ShopNetwork
	{
		$this->font_color = $color;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCssClass(): string
	{
		return $this->css_class;
	}

	/**
	 * @param string $class
	 * @return ShopNetwork
	 */
	public function setCssClass(string $class): ShopNetwork
	{
		$this->css_class = $class;
		return $this;
	}

	public function getLogoUrl()
	{

		if (empty($this->logo_url))
			return null;

		return $this->getThumbnail()->getUrl();
	}

	public function fields()
	{
		$fields = [
			'id',
			'title',
			'description' => function ($model) {
				return \yii\helpers\HtmlPurifier::process($model->description, function (HTMLPurifier_Config $config) {

					$attr = $config->get('HTML.ForbiddenAttributes');
					$elements = $config->get('HTML.ForbiddenElements');

					$attr[] = 'style';

					$elements[] = 'script';
					$elements[] = 'style';
					$elements[] = 'img';
					$elements[] = 'br';

					$config->set('HTML.ForbiddenAttributes', $attr);
					$config->set('HTML.ForbiddenElements', $elements);
				});
			},
			'color',
			'class' => function ($model) {
				return $model->getCssClass();
			},
			'logoUrl',
		];

		return $fields;
	}


}
