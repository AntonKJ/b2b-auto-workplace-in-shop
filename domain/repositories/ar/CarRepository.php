<?php

namespace domain\repositories\ar;

use domain\entities\car\Brand;
use domain\entities\car\BrandCollection;
use domain\entities\car\BrandEntityCollectionInterface;
use domain\entities\car\Car;
use domain\entities\car\Model;
use domain\entities\car\ModelCollection;
use domain\entities\car\ModelEntityCollectionInterface;
use domain\entities\car\Modification;
use domain\entities\car\ModificationCollection;
use domain\entities\car\ModificationEntityCollectionInterface;
use domain\entities\car\ModificationRange;
use domain\interfaces\CarRepositoryInterface;
use domain\repositories\Hydrator;
use domain\repositories\NotFoundException;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

class CarRepository extends RepositoryBase implements CarRepositoryInterface
{

	private $hydrator;

	protected $brandModelClass;
	protected $modelModelClass;
	protected $modificationModelClass;

	function __construct(ActiveRecordInterface $brandModelClass,
	                     ActiveRecordInterface $modelModelClass,
	                     ActiveRecordInterface $modificationModelClass,
	                     Hydrator $hydrator)
	{

		$this->brandModelClass = $brandModelClass;
		$this->modelModelClass = $modelModelClass;
		$this->modificationModelClass = $modificationModelClass;

		$this->hydrator = $hydrator;

	}

	protected function _populateBrand(array $data): Brand
	{
		return $this->hydrator->hydrate(Brand::class, [
			'id' => $data['brand_slug'],
			'title' => $data['prod'],
			'slug' => $data['brand_slug'],
		]);
	}

	protected function _populateModel(array $data): Model
	{
		return $this->hydrator->hydrate(Model::class, [
			'id' => $data['model_slug'],
			'title' => $data['model'],
			'slug' => $data['model_slug'],
		]);
	}

	protected function _populateModification(array $data): Modification
	{

		$range = $this->hydrator->hydrate(ModificationRange::class, [
			'start' => null,
			'end' => null,
		]);

		return $this->hydrator->hydrate(Modification::class, [
			'id' => $data['modification_slug'],
			'title' => implode(' ', [$data['prod'], $data['model']]),
			'slug' => $data['modification_slug'],
			'years' => $range,
		]);
	}

	protected function _populateCar(array $data): Car
	{
		return $this->hydrator->hydrate(Car::class, $data);
	}

	/**
	 * Возвращает список авто-брендов
	 * @return array|Brand[]
	 */
	public function findBrandAll(): BrandEntityCollectionInterface
	{
		/* @var $class ActiveRecord */
		$class = $this->brandModelClass;

		$data = new BrandCollection();

		$query = $class::find()->brands()->asArray();
		foreach ($query->each() as $row)
			$data[] = $this->_populateBrand($row);

		return $data;
	}

	/**
	 * @param string $slug
	 * @return Brand
	 */
	public function findBrandBySlug(string $slug): Brand
	{

		/* @var $class ActiveRecord */
		$class = $this->brandModelClass;

		if (null === ($brand = $class::find()->findById($slug)->asArray()->one()))
			throw new NotFoundException();

		return $this->_populateBrand($brand);
	}

	public function findModelAllByBrandId($id): ModelEntityCollectionInterface
	{

		/* @var $class ActiveRecord */
		$class = $this->modelModelClass;

		$data = new ModelCollection();

		$query = $class::find()->models()->findByBrandId($id)->asArray();
		foreach ($query->each() as $row)
			$data[] = $this->_populateModel($row);

		return $data;
	}

	public function findModelByBrandIdAndSlug($brandId, string $slug): Model
	{

		/* @var $class ActiveRecord */
		$class = $this->modelModelClass;

		if (null === ($model = $class::find()->findByBrandId($brandId)->findById($slug)->asArray()->one()))
			throw new NotFoundException();

		return $this->_populateModel($model);
	}

	public function findModificationAllByBrandIdAndModelId($brandId, $modelId): ModificationEntityCollectionInterface
	{

		/* @var $class ActiveRecord */
		$class = $this->modificationModelClass;

		$data = new ModificationCollection();

		$query = $class::find()->modifications()->findByBrandId($brandId)->findByModelId($modelId)->asArray();
		foreach ($query->each() as $row)
			$data[] = $this->_populateModification($row);

		return $data;
	}
}