<?php

namespace domain\entities\good;

use domain\entities\EntityBase;

class BrandTyre extends EntityBase
{

	private $id;

	protected $code;

	protected $title;
	protected $slug;

	protected $logo;

	/**
	 * BrandTyre constructor.
	 * @param $id int
	 * @param $title string
	 * @param $slug string
	 * @param $logo string
	 */
	public function __construct($id, $code, $title, $slug, $logo)
	{
		$this->id = $id;
		$this->title = $title;
		$this->slug = $slug;
		$this->logo = $logo;

	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string
	{
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getLogo(): string
	{
		return $this->logo;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	public function getLogoUrl()
	{

		if (empty($this->logo))
			return null;

		return \Yii::$app->media->getStorageUrl(implode('/', [
			'images',
			$this->logo,
		]));
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [

			'id' => $this->getId(),
			'code' => $this->getCode(),
			'title' => $this->getTitle(),
			'slug' => $this->getSlug(),
			'logo' => $this->getLogo(),
		];

		return $fields;
	}

}