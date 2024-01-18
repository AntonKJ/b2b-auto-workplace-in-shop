<?php

namespace domain\entities\car;

use domain\interfaces\EntityCollectionInterface;

interface ModificationEntityCollectionInterface extends EntityCollectionInterface
{
	public function add(Modification $data, $key = null);
}