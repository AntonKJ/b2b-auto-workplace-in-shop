<?php

namespace domain\interfaces;

interface GoodEntityInterface extends EntityInterface
{

	public function getEntityType();

	public function getId();

	public function getBrand();

	public function getModel();

}