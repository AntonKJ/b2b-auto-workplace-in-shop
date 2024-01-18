<?php

namespace domain\interfaces;

interface GoodRepositoryInterface extends RepositoryInterface
{

	public function findById($id);

}