<?php

namespace common\models;

use common\interfaces\BrandModelInterface;
use common\models\query\TyreModelQuery;
use Yii;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%model}}".
 *
 * @property integer $id
 * @property string $prod_code
 * @property string $season
 * @property string $code
 * @property string $name
 * @property string $pin
 * @property integer $type
 * @property string $kind
 * @property integer $new
 * @property string $logo
 * @property string $descr
 * @property string $zag
 * @property string $text
 * @property string $test
 * @property string $video
 * @property integer $Position
 * @property string $photo
 * @property integer $ext_warranty
 * @property string $category
 * @property integer $is_published
 * @property string $url
 */
class TyreModel extends ActiveRecord implements BrandModelInterface
{

	public const IS_PUBLISHED = 1;

	public const TYPE_BASE = 1;
	public const TYPE_SUV = 2;
	public const TYPE_LIGHT_TRUCK = 4;
	public const TYPE_TRUCK = 8;

	public const SEASON_SUMMER = 'S';
	public const SEASON_WINTER = 'W';

	public const PINS_YES = 'Y';
	public const PINS_NO = 'N';

	public const NEW_YES = 1;
	public const EXT_WARRANTY = 1;

	public $goodCount;
	public $sizeCount;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%model}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['type', 'new', 'Position', 'ext_warranty', 'is_published'], 'integer'],
			[['kind', 'new', 'photo'], 'required'],
			[['zag', 'text', 'test', 'video', 'photo'], 'string'],
			[['prod_code'], 'string', 'max' => 30],
			[['season', 'pin', 'category'], 'string', 'max' => 1],
			[['code', 'name'], 'string', 'max' => 63],
			[['kind'], 'string', 'max' => 6],
			[['logo', 'descr'], 'string', 'max' => 120],
			[['url'], 'string', 'max' => 40],
			//
			[['sizeCount'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'prod_code' => 'Prod Code',
			'season' => 'Season',
			'code' => 'Code',
			'name' => 'Name',
			'pin' => 'Pin',
			'type' => 'Type',
			'kind' => 'Kind',
			'new' => 'New',
			'logo' => 'Logo',
			'descr' => 'Descr',
			'zag' => 'Zag',
			'text' => 'Text',
			'test' => 'Test',
			'video' => 'Video',
			'Position' => 'Position',
			'photo' => 'Photo',
			'ext_warranty' => 'Ext Warranty',
			'category' => 'Category',
			'is_published' => 'Is Published',
			'url' => 'Url',
		];
	}

	/**
	 * @inheritdoc
	 * @return TyreModelQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new TyreModelQuery(static::class);
	}

	/**
	 * @return array типы моделей
	 */
	public static function getTypeOptions(): array
	{
		return [
			static::TYPE_BASE => 'Легковые шины',
			static::TYPE_SUV => 'Шины для внедорожников',
			static::TYPE_LIGHT_TRUCK => 'Легкогрузовые шины',
			static::TYPE_TRUCK => 'Грузовые шины',
		];
	}

	public function getTypeMask(): int
	{
		return (int)$this->type;
	}

	public function isTypeFlagSet(int $typeFlag): bool
	{
		return (($this->getTypeMask() & $typeFlag) === $typeFlag);
	}

	/**
	 * @return array
	 */
	public function getTypes(): array
	{
		$daysOptions = static::getTypeOptions();
		$types = [];
		foreach (array_keys($daysOptions) as $m) {
			if ($this->isTypeFlagSet($m)) {
				$types[$m] = $daysOptions[$m];
			}
		}
		return $types;
	}

	/**
	 * @return mixed|string
	 * @deprecated use getTypes instead
	 */
	public function getTypeText()
	{
		throw new InvalidCallException();
	}

	/**
	 * @return ActiveQuery
	 */
	public function getGoods()
	{
		return $this->hasMany(TyreGood::className(), ['prod_code' => 'prod_code', 'p_t' => 'code']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getBrand()
	{
		return $this->hasOne(TyreBrand::className(), ['code' => 'prod_code']);
	}

	public function getLogoUrl()
	{
		if (empty($this->logo)) {
			return null;
		}
		return Yii::$app->media->getStorageUrl(implode('/', [
			'catalog',
			$this->logo . '.jpg',
		]));
	}

	public function getSeasonText()
	{
		return ArrayHelper::getValue(self::getSeasonOptions(), $this->season);
	}

	public static function getSeasonOptions()
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

	/**
	 * @return array
	 */
	public static function getSeasonCodeOptions(): array
	{
		return [
			self::SEASON_SUMMER => 'summer',
			self::SEASON_WINTER => 'winter',
		];
	}

	public function getPinText()
	{
		return ArrayHelper::getValue(self::getPinOptions(), $this->pin);
	}

	/**
	 * @return array
	 */
	public static function getPinOptions(): array
	{
		return [
			self::PINS_YES => 'Шипы',
			self::PINS_NO => '',
		];
	}

	public function getHasPin()
	{
		return mb_strtoupper($this->pin) === static::PINS_YES;
	}

	public function getTitle()
	{
		return $this->name;
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function fields()
	{

		$fields = [


			'id',

			'code',
			'name',
			'title',

			'brand_code' => 'prod_code',

			'logo',
			'logoUrl',

			'url',
			'slug',

			'params' => static function (self $model) {
				return [

					'type' => $model->getTypes(),

					'season' => [
						'id' => mb_strtolower($model->season),
						'text' => $model->seasonText,
						'code' => $model->seasonCode,
					],

					'pins' => $model->hasPin,

					'isNew' => $model->isNew,
					'extWarranty' => $model->isExtWarranty,

				];
			},

		];

		if (null !== $this->sizeCount) {
			$fields[] = 'sizeCount';
		}

		return $fields;
	}

	public function extraFields()
	{

		$fields = parent::extraFields();

		$fields['content'] = static function (self $model) {

			$description = trim($model->text);
			$test = trim($model->test);
			$video = trim($model->video);
			$photo = trim($model->photo);

			return [
				'description' => empty($description) ? null : $description,
				'test' => empty($test) ? null : $test,
				'video' => empty($video) ? null : $video,
				'photo' => empty($photo) ? null : $photo,
			];
		};

		return $fields;
	}

	public function getIsNew()
	{
		$yearOfNovelty = Yii::$app->global->getYearOfModelNewFlag();
		return $yearOfNovelty > 0 && (int)$this->new >= $yearOfNovelty;
	}

	/**
	 * @return bool
	 */
	public function getIsExtWarranty()
	{
		return (int)$this->ext_warranty === static::EXT_WARRANTY;
	}

	/**
	 * alias url
	 * @return string
	 */
	public function getSlug()
	{
		return $this->url;
	}

	public function toArrayShort()
	{
		return $this->toArray([

			'id',

			'brand_code',

			'title',

			'url',
			'slug',

		]);
	}

	public function getPrepareSearchIndex(bool $refresh = false): array
	{

		// Кешируем сгенерированный индекс
		static $index = [];

		if (!isset($index[(int)$this->id]) || $refresh) {
			$words = [];

			$fields = ['prod_code', 'code', 'name', 'seasonText', 'pinText'];
			foreach ($fields as $field) {
				$words[] = $this->{$field};
			}

			if (preg_match('/(run\s*flat|flat\s*run|\bssr\b)/ui', $this->name)) {
				$words[] = 'rnflt';
				$words[] = 'flt';
				$words[] = 'fltrn';
				$words[] = 'runflat';
				$words[] = 'flatrun';
				$words[] = 'run flat';
				$words[] = 'flat run';
				$words[] = 'флетран';
				$words[] = 'флэтран';
				$words[] = 'ранфлет';
				$words[] = 'ранфлэт';
			}

			$words = preg_replace('/\s+/u', ' ', mb_strtolower(trim(implode(' ', $words))));
			$words = preg_split('/[\s,]+/u', $words);

			$index[(int)$this->id] = [];
			foreach ($words as $word) {
				$word = trim($word, '.');
				if (!in_array($word, $index[(int)$this->id]) && mb_strlen($word) >= 2) {
					$index[(int)$this->id][] = $word;
				}
			}
		}

		return $index[(int)$this->id];
	}
}
