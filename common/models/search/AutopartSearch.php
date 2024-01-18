<?php

namespace common\models\search;

use common\components\data\AccessoriesDataProvider;
use common\components\SearchInterface;
use common\models\Autopart;
use common\models\query\AutopartQuery;
use Yii;
use yii\base\Model;

class AutopartSearch extends Autopart implements SearchInterface
{

	protected $_searchParams;

	/**
	 * TyreSearch constructor.
	 * @param SearchParams|null $params
	 * @param array $config
	 */
	public function __construct($params = null, array $config = [])
	{
		parent::__construct($config);
		$this->_searchParams = $params;
	}

	/**
	 * @return SearchParams|null
	 */
	public function getSearchParams(): ?SearchParams
	{
		return $this->_searchParams;
	}

	/**
	 * @param SearchParams|null $searchParams
	 * @return AutopartSearch
	 */
	public function setSearchParams(?SearchParams $searchParams)
	{
		$this->_searchParams = $searchParams;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		return Model::scenarios();
	}

	public function formName()
	{
		return '';
	}

	/**
	 * @param $params
	 * @return AutopartQuery
	 */
	public function getSearchQuery($params = [])
	{
		return Autopart::find();
	}

	public function getSearchAttributes()
	{
		return $this->getAttributes($this->safeAttributes());
	}

	/**
	 * @param $params
	 * @return AccessoriesDataProvider
	 */
	public function search($params)
	{

		$this->load($params) && $this->validate();

		$params = $this->getSearchAttributes();

		// формируем запрос к базе
		$query = $this->getSearchQuery($params);

		// ленивая загрузка
		$query->with(['apCategory']);

		$region = Yii::$app->region->current;

		// это ленивая загрузка для прайсов и наличия
		$query
			->withPricesByRegion($region);

		// и все это в датапровайдер
		$dataProvider = new AccessoriesDataProvider(Yii::$app->ecommerce, $region, [
			'query' => $query,
			'pagination' => false,
			'sort' => [
				'defaultOrder' => [
					'default' => SORT_ASC,
				],
				'attributes' => [
					'default' => [
						'asc' => [
							'description' => SORT_ASC,
						],
						'desc' => [
							'description' => SORT_DESC,
						],
						'default' => SORT_ASC,
					],
				],
			],
		]);

		return $dataProvider;
	}

	protected function isEmpty($value)
	{
		return $value === '' || $value === [] || $value === null || (is_string($value) && trim($value) === '');
	}
}
