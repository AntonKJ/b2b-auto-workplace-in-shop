<?php

namespace common\models;

use common\components\file\storageStrategy\AutoImageStorageStrategyDefault;
use common\components\file\ThumbnailBehavior;
use common\models\query\AutoImageQuery;

/**
 * This is the model class for table "{{%car_images}}".
 *
 * @property int $id
 * @property int $color_id
 * @property string $photo
 */
class AutoImage extends \yii\db\ActiveRecord
{

	/**
	 * @var float[]
	 */
	private $_frontPosition;

	/**
	 * @var float[]
	 */
	private $_rearPosition;

	/**
	 * @var float
	 */
	private $_frontRadius;

	/**
	 * @var float
	 */
	private $_rearRadius;

	private $_autoImageSize;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%car_images}}';
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
						'target' => AutoImageStorageStrategyDefault::class,
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 * @return query\AutoImageQuery
	 */
	public static function find()
	{
		return new AutoImageQuery(static::class);
	}

	public function getPhotoUrl()
	{

		if (empty($this->photo))
			return null;

		return $this->getThumbnail()->getUrl();
	}

	public function fields()
	{
		return [
			'id',
			'color_id',
			'photo',
			'photoUrl',
			'photoSize' => 'autoImageSize',
			'diameter' => 'diam',
			'front' => function (self $model) {
				return [
					'radius' => $model->getFrontRadius(),
					'position' => $model->getFrontPosition(),
				];
			},
			'rear' => function (self $model) {
				return [
					'radius' => $model->getRearRadius() ?? $model->getFrontRadius(),
					'position' => $model->getRearPosition(),
				];
			},
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getColor()
	{
		return $this->hasOne(AutoColor::class, ['id' => 'color_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getModification()
	{
		return $this->hasOne(AutoModification::class, ['automodel_code_1c' => 'automodel_code_1c'])
			->inverseOf('images');
	}

	public function extraFields()
	{
		return [
			'color',
		];
	}

	/**
	 * @return float|null
	 */
	public function getFrontRadius($rearOnNull = true): ?float
	{

		if ($this->_frontRadius === null) {

			if (empty($this->coord_fx2) || empty($this->coord_fx1))
				return null;

			$this->setFrontRadius(
				abs($this->coord_fx2 - $this->coord_fx1) / 2
			);
		}

		return $this->_frontRadius ?? ($rearOnNull ? $this->getRearRadius(false) : null);
	}

	/**
	 * @param float $radius
	 * @return AutoImage
	 */
	public function setFrontRadius(?float $radius): AutoImage
	{
		$this->_frontRadius = $radius;
		return $this;
	}

	/**
	 * @return float|null
	 */
	public function getRearRadius($rearOnNull = true): ?float
	{

		if ($this->_rearRadius === null) {

			if (empty($this->coord_rx2) || empty($this->coord_rx1))
				return null;

			$this->setRearRadius(
				abs($this->coord_rx2 - $this->coord_rx1) / 2
			);
		}

		return $this->_rearRadius ?? ($rearOnNull ? $this->getFrontRadius(false) : null);
	}

	/**
	 * @param float $radius
	 * @return AutoImage
	 */
	public function setRearRadius(?float $radius): AutoImage
	{
		$this->_rearRadius = $radius;
		return $this;
	}

	/**
	 * @param float[] $frontPosition
	 * @return AutoImage
	 */
	public function setFrontPosition(array $frontPosition): AutoImage
	{
		$this->_frontPosition = $frontPosition;
		return $this;
	}

	/**
	 * @return float[]
	 */
	public function getFrontPosition(): array
	{

		if ($this->_frontPosition === null) {

			if (empty($this->coord_fx2) || empty($this->coord_fx1) || empty($this->coord_fy2) || empty($this->coord_fy1))
				return null;

			$this->setFrontPosition([
				'x' => $this->coord_fx1 + $this->getFrontRadius(),
				'y' => $this->coord_fy1 + $this->getFrontRadius(),
			]);
		}

		return $this->_frontPosition;
	}

	/**
	 * @param float[] $rearPosition
	 * @return AutoImage
	 */
	public function setRearPosition(array $rearPosition): AutoImage
	{
		$this->_rearPosition = $rearPosition;
		return $this;
	}

	/**
	 * @return float[]
	 */
	public function getRearPosition(): array
	{

		if ($this->_rearPosition === null) {

			if (empty($this->coord_rx2) || empty($this->coord_rx1) || empty($this->coord_ry2) || empty($this->coord_ry1))
				return null;

			$this->setRearPosition([
				'x' => $this->coord_rx1 + $this->getRearRadius(),
				'y' => $this->coord_ry1 + $this->getRearRadius(),
			]);
		}

		return $this->_rearPosition;
	}

	/**
	 * @param mixed $autoImageSize
	 * @return AutoImage
	 */
	public function setAutoImageSize($width, $height)
	{

		$this->_autoImageSize = [
			'width' => $width,
			'height' => $height
		];

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAutoImageSize()
	{
		return $this->_autoImageSize;
	}

}
