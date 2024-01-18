<?php

namespace domain\entities\good;

use domain\entities\EntityBase;

class GoodDiskParams extends EntityBase
{
	/**
	 * @var string|null
	 */
	protected $description;
	/**
	 * @var
	 */
	protected $brandGroup;
	/**
	 * GoodDiskParams constructor.
	 * @param string|null $description
	 * @param string|null $brandGroup
	 */
	public function __construct(?string $description, ?string $brandGroup)
	{
		$description = trim($description);
		$this->description = empty($description) ? null : $description;

		$brandGroup = trim($brandGroup);
		$this->brandGroup = empty($brandGroup) ? null : $brandGroup;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return string|null
	 */
	public function getBrandGroup(): ?string
	{
		return $this->brandGroup;
	}

	public function fields()
	{
		return [
			'description' => $this->getDescription(),
			'brandGroup' => $this->getBrandGroup(),
		];
	}

}
