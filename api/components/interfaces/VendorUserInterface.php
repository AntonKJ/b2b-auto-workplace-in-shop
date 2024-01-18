<?php

namespace api\components\interfaces;

use common\interfaces\B2BUserInterface;

interface VendorUserInterface
{

	/**
	 * @return mixed
	 */
	public function getVendor();

	/**
	 * @return int|null
	 */
	public function getOptUserId(): ?int;

	/**
	 * @return B2BUserInterface|null
	 */
	public function getOptUser(): ?B2BUserInterface;

}