<?php

namespace domain\entities\car;

use domain\interfaces\EntityCollectionInterface;

interface ModelEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(Model $data, $key = null);
}