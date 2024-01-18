<?php

namespace common\components;


use common\interfaces\RegionComponentInterface;
use common\interfaces\RegionEntityInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\User;

/**
 * Class Region
 * @package common\components
 *
 * @property RegionEntityInterface $current
 * @property RegionEntityInterface $default
 */
class Region extends Component implements RegionComponentInterface
{

	/**
	 * @var string
	 */
	public $userComponentName = 'user';

	/**
	 * @var User
	 */
	protected $_userComponent;

	/**
	 * @var string
	 */
	public $domainRegionVar = 'domain';

	/**
	 * @var string Урл региона поумолчанию
	 */
	public $defaultRegionName;

	/**
	 * @var bool Использовать регион поумолчанию, если текущий не найден
	 */
	public $useDefaultOnNotFound = false;

	/**
	 * Это свойство должно быть установлено при конфигурации приложения
	 * @var integer id московского региона
	 */
	public $moscowRegionId;

	/**
	 * Группа московского региона
	 * @return array
	 */
	public $regionMoscowGroup = [1, 19]; // www, b2b

	/**
	 * @var RegionEntityInterface
	 */
	public $regionModel;

	protected $_region = false;
	protected $_regionDefault = false;

	/**
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		parent::init();

		if ($this->regionModel === null) {
			throw new InvalidConfigException(get_class($this) . '::$regionModel must be set.');
		}

		if ($this->userComponentName === null) {
			throw new InvalidConfigException(get_class($this) . '::$userComponentName must be set.');
		}

		if (empty($this->domainRegionVar)) {
			throw new InvalidConfigException(get_class($this) . '::$domainRegionVar must be set.');
		}

		if ($this->defaultRegionName === null) {
			throw new InvalidConfigException(get_class($this) . '::$defaultRegionName must be set.');
		}

		if ((int)$this->moscowRegionId === 0) {
			throw new InvalidConfigException(get_class($this) . '::$moscowRegionId must be set.');
		}

		$this->_userComponent = Yii::$app->get($this->userComponentName);
	}

	/**
	 * @return RegionEntityInterface
	 * @throws InvalidConfigException
	 */
	public function getDefault(): RegionEntityInterface
	{

		if (false === $this->_regionDefault) {

			/**
			 * @var \common\models\Region $class
			 */
			$class = $this->regionModel;

			$this->_regionDefault = $class::find()
				->cache(3600)
				->active()
				->bySlug($this->defaultRegionName)
				->one();

			if (null === $this->_regionDefault)
				throw new InvalidConfigException('Default region not found');

		}

		return $this->_regionDefault;
	}

	/**
	 * @return RegionEntityInterface
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 * @throws Throwable
	 */
	public function getCurrent(): RegionEntityInterface
	{
		if (false === $this->_region) {
			$this->_region = null;
			if (!$this->_userComponent->isGuest) {
				$this->_region = $this->_userComponent->getIdentity()->getRegion()->cache(3600 * 24)->one();
			}
			if ($this->_region === null) {
				$parsedUrl = parse_url(mb_strtolower(Yii::$app->request->hostInfo));
				$hostPart = explode('.', $parsedUrl['host']);
				$domain = implode('.', array_slice($hostPart, 0, -2));
				if (empty($domain)) {
					$this->_region = $this->getDefault();
					if (null === $this->_region) {
						$this->regionNotFound();
					}
				} else {
					/**
					 * @var \common\models\Region $class
					 */
					$class = $this->regionModel;
					$this->_region = $class::find()->active()->bySlug($this->defaultRegionName)->cache(3600 * 24)->one();
					if (null === $this->_region) {
						if (!$this->useDefaultOnNotFound) {
							$this->regionNotFound();
						} else {
							$this->_region = $this->getDefault();
						}
					}
				}
			}
			Yii::info($this->_region->attributes, 'application.region.current');
		}
		return $this->_region;
	}

	/**
	 * @return int
	 */
	public function getMoscowRegionId(): int
	{
		return $this->moscowRegionId;
	}

	public function getRegionMoscowGroup(): array
	{
		return $this->regionMoscowGroup;
	}

	public function isRegionInMoscowGroup(RegionEntityInterface $region): bool
	{
		return in_array($region->getId(), $this->getRegionMoscowGroup());
	}

	public function isRegionZoneTypeWWW(RegionEntityInterface $region): bool
	{
		return static::ZONE_TYPE_WWW == $region->getZoneType();
	}

	public function isRegionZoneTypeB2B(RegionEntityInterface $region): bool
	{
		return static::ZONE_TYPE_B2B == $region->getZoneType();
	}

	public function isRegionZoneTypeCC(RegionEntityInterface $region): bool
	{
		return static::ZONE_TYPE_CC == $region->getZoneType();
	}

	/**
	 * @throws NotFoundHttpException
	 */
	protected function regionNotFound()
	{
		throw new NotFoundHttpException('Регион не существует');
	}

}
