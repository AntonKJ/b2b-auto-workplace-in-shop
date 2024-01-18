<?php

namespace domain\entities;

use domain\interfaces\Arrayable;
use domain\interfaces\EntityInterface;
use domain\traits\ArrayableTrait;

class EntityBase implements EntityInterface, Arrayable
{

	use ArrayableTrait;

}