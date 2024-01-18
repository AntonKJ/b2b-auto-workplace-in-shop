<?php

namespace domain\entities\good;

use domain\entities\EntityBase;

class ModelTyre extends EntityBase
{

	private $id;
	protected $brandId;
	protected $brandCode;
	protected $type;
	protected $title;
	protected $slug;
	protected $logo;

	/**
	 * ModelTyre constructor.
	 * @param $id int
	 * @param $brandId int
	 * @param $brandCode string
	 * @param $type int
	 * @param $title string
	 * @param $slug string
	 * @param $logo string
	 */
	public function __construct($id, $brandId, $brandCode, $type, $title, $slug, $logo)
	{
		$this->id = $id;
		$this->brandId = $brandId;
		$this->brandCode = $brandCode;
		$this->type = $type;
		$this->title = $title;
		$this->slug = $slug;
		$this->logo = $logo;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getBrandId()
	{
		return $this->brandId;
	}

	/**
	 * @return string
	 */
	public function getBrandCode()
	{
		return $this->brandCode;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getLogo()
	{
		return $this->logo;
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [

			'id' => $this->getId(),
			'type' => $this->getType(),
			'brandId' => $this->getBrandId(),
			'brandCode' => $this->getBrandCode(),
			'title' => $this->getTitle(),
			'slug' => $this->getSlug(),
			'logo' => $this->getLogo(),
		];

		return $fields;
	}

}