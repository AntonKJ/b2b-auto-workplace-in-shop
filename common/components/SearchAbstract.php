<?php

namespace common\components;

use common\models\search\SearchParams;
use yii\base\Model;

/**
 *
 * @property null|SearchParams $searchParams
 */
abstract class SearchAbstract extends Model implements SearchInterface
{

	protected $_searchParams;

	/**
	 * SearchAbstract constructor.
	 * @param SearchParams|null $params
	 * @param array $config
	 */
	public function __construct(?SearchParams $params = null, array $config = [])
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
	 * @return SearchAbstract
	 */
	public function setSearchParams(?SearchParams $searchParams)
	{
		$this->_searchParams = $searchParams;
		return $this;
	}


}
