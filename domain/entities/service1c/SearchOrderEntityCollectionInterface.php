<?php

namespace domain\entities\service1c;

use domain\interfaces\EntityCollectionInterface;

interface SearchOrderEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(SearchOrder $data, $key = null);
}