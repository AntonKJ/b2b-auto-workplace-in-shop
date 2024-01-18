<?php

namespace domain\interfaces;

interface EntityCollectionInterface extends \IteratorAggregate, \ArrayAccess, \Countable
{
	public function getAll();
}