<?php

namespace common\components;

use common\models\search\SearchParams;

interface SearchInterface
{

	public function getSearchParams(): ?SearchParams;

	public function setSearchParams(?SearchParams $searchParams);

	public function getSearchAttributes();

	public function getSearchQuery($params = []);

}