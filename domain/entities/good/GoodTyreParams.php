<?php

namespace domain\entities\good;

use common\models\Homologation;
use domain\entities\EntityBase;
use domain\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class GoodTyreParams extends EntityBase
{

	const SEASON_SUMMER = 's';
	const SEASON_WINTER = 'w';

	/**
	 * @var string
	 */
	protected $season;

	/**
	 * @var bool
	 */
	protected $runflat;

	/**
	 * @var bool
	 */
	protected $pins;

	/**
	 * @var int
	 */
	protected $loadIndex;

	/**
	 * @var string
	 */
	protected $speedRating;

	/**
	 * @var float
	 */
	protected $tLong;

	/**
	 * @var string|null
	 */
	protected $homologation;
	/**
	 * @var int|null
	 */
	protected $prodYear;

	/**
	 * GoodTyreParams constructor.
	 * @param string $season
	 * @param bool $runflat
	 * @param bool $pins
	 * @param string $loadIndex
	 * @param string $speedRating
	 * @param float $tLong
	 * @param $homologation
	 * @param $prodYear
	 */
	public function __construct($season, $runflat, $pins, $loadIndex, $speedRating, $tLong, $homologation, $prodYear)
	{
		$this->season = mb_strtolower($season);
		$this->runflat = $runflat;
		$this->pins = $pins;
		$this->loadIndex = $loadIndex;
		$this->speedRating = $speedRating;
		$this->tLong = $tLong;
		$this->homologation = !empty($homologation) ? $homologation : null;
		$this->prodYear = ($y = (int)$prodYear) > 0 ? $y : null;
	}

	public function getSeasonText()
	{
		return ArrayHelper::getValue(static::getSeasonOptions(), $this->season);
	}

	static public function getSeasonOptions()
	{
		return [
			self::SEASON_SUMMER => 'Лето',
			self::SEASON_WINTER => 'Зима',
		];
	}

	public function getSeasonCode()
	{
		return ArrayHelper::getValue(static::getSeasonCodeOptions(), $this->season);
	}

	static public function getSeasonCodeOptions()
	{
		return [
			self::SEASON_SUMMER => 'summer',
			self::SEASON_WINTER => 'winter',
		];
	}

	/**
	 * @return string
	 */
	public function getSeason()
	{
		return $this->season;
	}

	/**
	 * @return bool
	 */
	public function isRunflat(): bool
	{
		return $this->runflat;
	}

	/**
	 * @return bool
	 */
	public function isPins(): bool
	{
		return $this->pins;
	}

	/**
	 * @return float
	 */
	public function getTLong(): float
	{
		return $this->tLong;
	}

	/**
	 * @return bool
	 */
	public function isSeasonSummer(): bool
	{
		return $this->season === static::SEASON_SUMMER;
	}

	/**
	 * @return bool
	 */
	public function isSeasonWinter(): bool
	{
		return $this->season === static::SEASON_WINTER;
	}

	/**
	 * @return mixed
	 */
	public function getLoadIndexText()
	{
		return static::getLoadIndexTextByIndex($this->loadIndex);
	}

	/**
	 * Рейтинг скорости
	 * @return mixed
	 */
	public function getSpeedRatingText()
	{
		return static::getSpeedRatingTextByIndex($this->speedRating);
	}

	/**
	 * Индекс нагрузки
	 * @return string
	 */
	public function getLoadIndex(): string
	{
		return $this->loadIndex;
	}

	/**
	 * @return string
	 */
	public function getSpeedRating(): string
	{
		return $this->speedRating;
	}

	/**
	 * @return array
	 */
	static function getLoadIndexOptions()
	{
		return [

			69 => 325,
			70 => 335,
			71 => 345,
			72 => 355,
			73 => 365,
			74 => 376,
			75 => 387,
			76 => 400,
			77 => 412,
			78 => 425,
			79 => 437,
			80 => 450,
			81 => 462,
			82 => 475,
			83 => 487,
			84 => 500,
			85 => 515,
			86 => 530,
			87 => 545,
			88 => 560,
			89 => 580,
			90 => 600,
			91 => 615,
			92 => 630,
			93 => 650,
			94 => 670,
			95 => 690,
			96 => 710,
			97 => 730,
			98 => 750,
			99 => 775,
			100 => 800,
			101 => 825,
			102 => 850,
			103 => 875,
			104 => 900,
			105 => 925,
			106 => 950,
			107 => 975,
			108 => 1000,
			109 => 1030,
			110 => 1060,
			111 => 1090,
			112 => 1120,
			113 => 1150,
			114 => 1180,
			115 => 1215,
			116 => 1250,
			117 => 1285,
			118 => 1320,
			119 => 1360,
			120 => 1400,
			121 => 1450,
			122 => 1500,
			123 => 1550,
			124 => 1600,
			125 => 1650,
			126 => 1700,
			127 => 1750,
			128 => 1800,
			129 => 1850,
			130 => 1900,
			131 => 1950,
			132 => 2000,
			133 => 2060,
			134 => 2120,
			135 => 2180,
			136 => 2240,
			137 => 2300,
			138 => 2360,
			139 => 2430,
			140 => 2500,
			141 => 2570,
			142 => 2650,
			143 => 2720,
			144 => 2800,
			145 => 2900,
			146 => 3000,
			147 => 3070,
			148 => 3150,
			149 => 3250,
			150 => 3350,
			151 => 3450,
			152 => 3550,
			153 => 3650,
			154 => 3750,
			156 => 4000,
			158 => 4260,
			159 => 4375,
			160 => 4500,
			161 => 4620,
			162 => 4750,
			163 => 4875,
			164 => 5000,
			165 => 5150,

		];
	}

	/**
	 * @return array
	 */
	static function getSpeedRatingOptions()
	{
		return [

			'J' => 100,
			'K' => 110,
			'L' => 120,
			'M' => 130,
			'N' => 140,
			'P' => 150,
			'Q' => 160,
			'R' => 170,
			'S' => 180,
			'T' => 190,
			'U' => 200,
			'H' => 210,
			'Z' => '>= 240',
			'ZR' => '>= 240',
			'V' => 240,
			'W' => 270,
			'Y' => 300,

		];
	}

	/**
	 * @param $index
	 * @return mixed
	 */
	public static function getSpeedRatingTextByIndex($index)
	{
		return ArrayHelper::getValue(static::getSpeedRatingOptions(), $index);
	}

	/**
	 * @param $index
	 * @return mixed
	 */
	public static function getLoadIndexTextByIndex($index)
	{
		return ArrayHelper::getValue(static::getLoadIndexOptions(), $index);
	}

	/**
	 * @return array
	 */
	public function getHomologation(): array
	{
		if (!$this->homologation)
			return [];

		static $data = [];
		if (!isset($data[$this->homologation])) {

			$data[$this->homologation] = [];

			$parts = StringHelper::explode($this->homologation, ',', true, true);

			$homoOptions = Homologation::getHomologationOptions();
			foreach (array_keys($homoOptions) as $k)
				$homoOptions[$k] = "{$homoOptions[$k]} — {$k}";

			foreach (array_values($parts) as $part) {

				if (!isset($homoOptions[$part])) {

					$data[$this->homologation][] = $part;
					continue;
				}

				$data[$this->homologation][] = $homoOptions[$part];
			}
		}

		return $data[$this->homologation];
	}

	/**
	 * @return int|null
	 */
	public function getProdYear(): ?int
	{
		return $this->prodYear;
	}

	public function fields()
	{
		return [

			'season' => [
				'id' => mb_strtolower($this->getSeason()),
				'text' => $this->getSeasonText(),
				'code' => $this->getSeasonCode(),
			],

			'runflat' => $this->isRunflat(),

			'pins' => $this->isPins(),

			'loadIndex' => [
				'index' => $this->getLoadIndex(),
				'maxWeight' => $this->getLoadIndexText(),
			],

			'speedRating' => [
				'index' => $this->getSpeedRating(),
				'maxSpeed' => $this->getSpeedRatingText(),
			],

			'tLong' => $this->tLong,
			'homologation' => $this->getHomologation(),
			'prodYear' => $this->getProdYear(),

		];
	}

}
