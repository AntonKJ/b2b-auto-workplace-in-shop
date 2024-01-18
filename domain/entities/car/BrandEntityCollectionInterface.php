<?php

namespace domain\entities\car;

use domain\interfaces\EntityCollectionInterface;

interface BrandEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(Brand $data, $key = null);
}