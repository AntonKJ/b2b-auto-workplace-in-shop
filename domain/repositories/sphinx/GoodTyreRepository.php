<?php

namespace domain\repositories\sphinx;

use domain\entities\good\BrandTyre;
use domain\entities\good\dto\BrandTyreDto;
use domain\entities\good\dto\ModelTyreDto;
use domain\entities\good\GoodTyre;
use domain\entities\good\GoodTyreParams;
use domain\entities\good\ModelTyre;
use domain\entities\PriceRange;
use domain\entities\region\RegionCollection;
use domain\entities\region\RegionEntityCollectionInterface;
use domain\entities\SizeTyre;
use domain\interfaces\GoodEntityInterface;
use domain\interfaces\GoodTyreRepositoryInterface;
use domain\interfaces\GoodTyreSpecificationInterface;
use domain\repositories\ar\RepositoryBase;
use domain\repositories\Hydrator;
use domain\repositories\NotFoundException;
use domain\specifications\GoodTyreSpecification;
use yii\db\Expression;
use yii\sphinx\Query;

class GoodTyreRepository extends RepositoryBase implements GoodTyreRepositoryInterface
{

	const PINS_YES = 'Y';
	const PINS_NO = 'N';

	const RUNFLAT = 1;
	const SALE = 1;

	private $hydrator;

	function __construct(Hydrator $hydrator)
	{
		$this->hydrator = $hydrator;
	}

	protected function _populateBrand(BrandTyreDto $dto): BrandTyre
	{
		return $this->hydrator->hydrate(BrandTyre::class, (array)$dto);
	}

	protected function _populateModel(ModelTyreDto $dto): ModelTyre
	{
		return $this->hydrator->hydrate(ModelTyre::class, (array)$dto);
	}

	protected function _populateGood(array $data): GoodTyre
	{

		$data['good_params'] = json_decode($data['good_params'], true);
		$data['model_params'] = json_decode($data['model_params'], true);

		$brandDto = new BrandTyreDto();

		$brandDto->id = (int)$data['brand_id'];
		$brandDto->code = $data['brand_code'];
		$brandDto->title = $data['brand_title'];
		$brandDto->slug = $data['brand_slug'];
		$brandDto->logo = $data['brand_logo'];

		$modelDto = new ModelTyreDto();

		$modelDto->id = (int)$data['model_id'];
		$modelDto->type = (int)$data['model_params']['type'];
		$modelDto->brandId = (int)$data['brand_id'];
		$modelDto->brandCode = $data['brand_code'];
		$modelDto->title = $data['model_title'];
		$modelDto->slug = $data['model_slug'];
		$modelDto->logo = $data['model_params']['logo'];

		/**
		 * @var SizeTyre $size
		 */
		$size = $this->hydrator->hydrate(SizeTyre::class, [
			'width' => (float)$data['good_params']['width'] ?? null,
			'profile' => (float)$data['good_params']['profile'] ?? null,
			'radius' => (float)$data['good_params']['radius'] ?? null,
			'commerce' => (bool)$data['good_params']['commerce'] ?? null,
		]);

		/**
		 * @var GoodTyreParams $params
		 */
		$params = $this->hydrator->hydrate(GoodTyreParams::class, [
			'season' => mb_strtolower($data['model_params']['season']) ?? null,
			'runflat' => (int)$data['model_params']['runflat'] == static::RUNFLAT,
			'pins' => $data['model_params']['pin'] == static::PINS_YES,
			'loadIndex' => $data['good_params']['load_index'] ?? null,
			'speedRating' => $data['good_params']['speed_rating'] ?? null,
			'tLong' => $data['good_params']['tLong'] ?? null,
		]);

		return $this->hydrator->hydrate(GoodTyre::class, [
			'id' => $data['good_id'],
			'sku' => $data['sku'],
			'sku_1c' => $data['sku_1c'],
			'brandSku' => $data['sku_brand'],
			'title' => $size->format(),
			'brandCode' => mb_strtolower($data['brand_slug']),
			'modelCode' => mb_strtolower($data['model_slug']),
			'country' => null,
			'size' => $size,
			'params' => $params,
			'brand' => $this->_populateBrand($brandDto),
			'model' => $this->_populateModel($modelDto),
		]);
	}

	public function getBrandByCode($code): BrandTyre
	{

		$query = new Query();

		$data = $query
			->from('myexample')
			->andWhere([
				'type' => 10,
				'brand_code' => $code,
			])
			->groupBy([
				'brand_code',
			])
			->one();

		if (false === $data)
			throw new NotFoundException();

		$brandDto = new BrandTyreDto();

		$brandDto->id = (int)$data['brand_id'];
		$brandDto->code = (int)$data['brand_code'];
		$brandDto->title = $data['brand_title'];
		$brandDto->slug = $data['brand_slug'];
		$brandDto->logo = $data['brand_logo'];

		return $this->_populateBrand($brandDto);
	}

	public function getModelByBrandAndCode(BrandTyre $brand, $code): ModelTyre
	{

		$query = new Query();

		$data = $query
			->from('myexample')
			->andWhere([
				'type' => 10,
				'brand_code' => $brand->getCode(),
				'model_code' => $code,
			])
			->groupBy([
				'brand_code',
				'model_code',
			])
			->one();

		$model = null;
		if (false !== $data) {

			$modelDto = new ModelTyreDto();

			$modelDto->id = (int)$data['model_id'];
			$modelDto->type = (int)$data['model_params']['type'];
			$modelDto->brandId = (int)$data['brand_id'];
			$modelDto->brandCode = $data['brand_code'];
			$modelDto->title = $data['model_title'];
			$modelDto->slug = $data['model_slug'];
			$modelDto->logo = $data['model_params']['logo'];

			$model = $this->_populateModel($modelDto);
		}

		return $model;
	}

	public function loadBrandInto(GoodEntityInterface &$good, BrandTyre $brand): GoodEntityInterface
	{
		$this->hydrator->hydrateInto($good, [
			'brand' => $brand,
		]);

		return $good;
	}

	public function loadModelInto(GoodEntityInterface &$good, ModelTyre $model): GoodEntityInterface
	{
		$this->hydrator->hydrateInto($good, [
			'model' => $model,
		]);

		return $good;
	}

	public function findById($id)
	{
		$specification = (new GoodTyreSpecification())->setId($id);

		$reader = $this->findOneBySpecification($specification);

		return $reader;
	}

	public function findAllBySpecification(GoodTyreSpecificationInterface $specification): RegionEntityCollectionInterface
	{

		$query = $this->_findBySpecification($specification);

		$data = new RegionCollection();

		foreach ($query->each() as $row)
			$data[] = $this->_populateGood($row);

		return $data;
	}

	public function findOneBySpecification(GoodTyreSpecificationInterface $specification): GoodTyre
	{

		$row = $this->_findBySpecification($specification)
			->groupBy(['good_id'])
			->one();

		if (null === $row)
			throw new NotFoundException();

		return $this->_populateGood($row);
	}

	protected function _findBySpecification(GoodTyreSpecificationInterface $specification): Query
	{

		$query = new Query();

		$query
			->from('myexample')
			->andWhere(['type' => 10]);

		if (null !== $specification->getId())
			$query->andWhere(['good_id' => $specification->getId()]);

		if (null !== $specification->getRunflat() && true === (bool)$specification->getRunflat())
			$query->andWhere(['model_params.runflat' => static::RUNFLAT]);

		if (null !== $specification->getPins() && true === (bool)$specification->getPins())
			$query->andWhere(new Expression('model_params.pin = :pin'), [':pin' => static::PINS_YES]);

		if (null !== $specification->getSale() && true === (bool)$specification->getSale())
			$query->andWhere(new Expression('offer.sale = :sale'), [':sale' => static::SALE]);

		if (null !== $specification->getSeason())
			$query->andWhere(new Expression('model_params.season = :season'), [':season' => $specification->getSeason()]);

		if (null !== $specification->getSpeedRating())
			$query->andWhere(new Expression('good_params.speed_rating = :speedRating'), [':speedRating' => $specification->getSpeedRating()]);

		if (null !== $specification->getLoadIndex())
			$query->andWhere(new Expression('good_params.load_index = :loadIndex'), [':loadIndex' => $specification->getLoadIndex()]);

		if (($priceRange = $specification->getPrice()) instanceof PriceRange) {

			if (null !== $priceRange->getFrom() && null !== $priceRange->getTo())
				$query->andWhere(['between', 'price', $priceRange->getFrom(), $priceRange->getTo()]);
			elseif (null !== $priceRange->getFrom())
				$query->andWhere(['>=', 'price', $priceRange->getFrom()]);
			elseif (null !== $priceRange->getTo())
				$query->andWhere(['<=', 'price', $priceRange->getTo()]);
		}

		if (null !== $specification->getBrandUrl())
			$query->andWhere(['brand_slug' => $specification->getBrandUrl()]);

		if (null !== $specification->getModelUrl())
			$query->andWhere(['model_slug' => $specification->getModelUrl()]);

		return $query;
	}

}