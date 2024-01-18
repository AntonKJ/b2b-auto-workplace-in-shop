<?php

namespace domain\interfaces;

interface GoodServiceInterface extends ServiceInterface
{

	public function getById($id): GoodEntityInterface;

	public function loadBrandInto(GoodEntityInterface &$good): GoodEntityInterface;

	public function loadModelInto(GoodEntityInterface &$good): GoodEntityInterface;

}