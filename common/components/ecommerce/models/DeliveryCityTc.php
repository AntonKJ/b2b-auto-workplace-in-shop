<?php

namespace common\components\ecommerce\models;

use myexample\ecommerce\DeliveryCityTcModelInterface;

class DeliveryCityTc implements DeliveryCityTcModelInterface
{
	/**
	 * @var int
	 */
	protected $_id;
	/**
	 * @var string
	 */
	protected $_title;

	/**
	 * DeliveryCityTc constructor.
	 * @param int $id
	 * @param string $title
	 */
	public function __construct(int $id, string $title)
	{
		$this->_id = $id;
		$this->_title = $title;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

}
