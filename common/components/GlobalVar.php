<?php

namespace common\components;

use common\models\CacheVariables;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class GlobalVar
 * @package common\components
 */
class GlobalVar extends Component
{

	protected $_data;

	protected function fetchData()
	{
		$out = null;
		if ($model = CacheVariables::find()->byId(CacheVariables::ID_GLOBAL)->one()) {
			$out = $model->getDumpAsArray();
		}
		return $out;
	}

	public function getData()
	{
		if ($this->_data === null) {
			$this->_data = Yii::$app->cache->getOrSet([__CLASS__, __METHOD__], function () {
				return $this->fetchData();
			});
		}
		return $this->_data;
	}

	/**
	 * Активный сезон (s|w)
	 * @return string
	 */
	public function getSeason()
	{
		static $season;
		if ($season === null) {
			$season = mb_strtolower(ArrayHelper::getValue($this->getData(), 'season', SeasonEnum::SUMMER));
		}
		return $season;
	}

	/**
	 * Год начиная с которого модель считается новинкой
	 * @return int
	 */
	public function getYearOfModelNewFlag()
	{
		static $year;
		if ($year === null) {
			$year = (int)ArrayHelper::getValue($this->getData(), 'year', date('Y'));
		}
		return $year;
	}

}
