<?php

namespace domain\entities\region;

use domain\interfaces\EntityCollectionInterface;

interface RegionEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(RegionEntityInterface $data, $key = null);
}