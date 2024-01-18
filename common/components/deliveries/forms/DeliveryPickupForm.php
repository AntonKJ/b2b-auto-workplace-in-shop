<?php

namespace common\components\deliveries\forms;

use common\components\deliveries\DeliveryPickup;
use common\models\OptUserAddress;
use common\models\OrderType;
use DateTime;
use Exception;
use Throwable;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class DeliveryPickupForm extends Model implements DeliveryFormInterface
{

	use PaymentFormTrait;
	use DeliveryScheduleFormTrait;
	use DeliveryFormTrait;

	public $shop;
	public $date;
	public $payment;
	public $schedule;

	public $comment;

	protected $_shops;
	protected $_payments;
	protected $_schedules;

	protected $_commentNotEmpty;

	/**
	 * DeliveryPickupForm constructor.
	 * @param DeliveryPickup $delivery
	 * @param array $config
	 * @throws Throwable
	 */
	public function __construct(DeliveryPickup $delivery, array $config = [])
	{
		$data = $delivery->isActive() ? $delivery->getData() : [];

		$this->_shops = $data['items'] ?? [];
		$this->_payments = ArrayHelper::index($data['payments'] ?? [], 'id');
		$this->_schedules = $data['schedules'] ?? [];

		parent::__construct($config);
	}

	public function attributeLabels()
	{
		return [
			'shop' => 'Ближайший магазин для самовывоза',
			'date' => 'Дата самовывоза',
			'payment' => 'Вариант оплаты',
			'schedule' => 'Время самовывоза',
			'comment' => 'Комментарий к заказу',
		];
	}

	public function getShopIdsOptions()
	{
		static $options = null;
		if (null === $options) {
			$options = [];
			foreach ($this->_shops as $itm) {
				$options[$itm['shop']['id']] = $itm;
			}
		}
		return $options;
	}

	/**
	 * @param mixed $commentNotEmpty
	 * @return DeliveryPickupForm
	 */
	public function setCommentNotEmpty($commentNotEmpty): self
	{
		$this->_commentNotEmpty = $commentNotEmpty;
		return $this;
	}

	/**
	 * @return array
	 */
	public function rules()
	{
		return [

			[['shop'], 'required', 'message' => 'Выберите ближайший магазин'],
			[['shop'], 'in', 'range' => array_keys($this->getShopIdsOptions())],

			[['date'], 'required', 'message' => 'Укажите дату самовывоза'],
			[['date'], 'datetime', 'format' => static::JS_DATE_FORMAT],
			[['date'], 'validateDeliveryDate', 'skipOnError' => true],

			[['payment'], 'required', 'message' => 'Выберите способ оплаты'],
			[['payment'], 'in', 'range' => array_keys($this->_payments)],

			[['schedule'], 'in', 'range' => array_keys($this->_schedules)],

			[['comment'], 'trim'],
			[['comment'], 'required', 'when' => function () {
				return (bool)$this->_commentNotEmpty;
			}],
			[['comment'], 'string', 'max' => 1000],

		];
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 * @throws Exception
	 */
	public function validateDeliveryDate($attribute, $params, $validator)
	{

		if ($validator->skipOnError && $this->hasErrors()) {
			return;
		}

		$value = $this->{$attribute};

		if (empty($value) && $params['skipOnEmpty']) {
			return;
		}

		$dt = $this->getDateAsDateTime();

		$shop = $this->getShopIdsOptions()[$this->shop];

		$dtMin = new DateTime($shop['deliveryDate']['min']['dayDatetime']);
		$dtMax = new DateTime($shop['deliveryDate']['max']['dayDatetime']);

		$dtTimeStamp = $dt->getTimestamp();

		if ($dtTimeStamp < $dtMin->getTimestamp() || $dtMax->getTimestamp() < $dtTimeStamp)
			$this->addError($attribute, "Выберите правильную дату самовывоза между {$dtMin->format('d.m.Y')} и {$dtMax->format('d.m.Y')}, Вы выбрали {$dt->format('d.m.Y')}");
	}

	/**
	 * @return mixed
	 * @throws Exception
	 * @throws Throwable
	 */
	public function getOrderType()
	{
		return OrderType::getDb()->cache(function ($db) {
			return OrderType::find()->byCategory(DeliveryPickup::getCategory())->orderByPriority()->one();
		});
	}

	public function getShopId()
	{
		return $this->shop;
	}

	public function isAllowedAddressStore(): bool
	{
		return false;
	}

	public function loadAddressAttributes(OptUserAddress $addressModel): void
	{
		throw new InvalidCallException('Method not implemented.');
	}

}
