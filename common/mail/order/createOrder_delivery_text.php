<?php

use common\models\DiskGood;
use common\models\TyreGood;
use domain\entities\service1c\OrderInvoice;

/**
 * @var \yii\web\View $this
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \domain\entities\service1c\OrderReserve $reserve
 * @var \common\models\forms\Order $order
 * @var \common\models\Shop $shop
 * @var \common\models\Region $region
 * @var \common\components\Region $regionComponent
 */

$order = $orderForm->getOrder();
$shop = $orderForm->getDeliveryModel()->getShop();
$region = $orderForm->getRegion();

$regionComponent = Yii::$app->region;

$goods = [];

/* @var \common\components\ShoppingCartItem $good */
foreach ($orderForm->getGoods() as $good)
	$goods[$good->getItem()->getEntityType()][] = $good;

$wws = [];

if (isset($goods[TyreGood::GOOD_ENTITY_TYPE]))
	$wws[] = 'шины';

if (isset($goods[DiskGood::GOOD_ENTITY_TYPE]))
	$wws[] = 'диски';

$wws = implode(' и ', $wws);

?>

Здравствуйте!

Благодарим вас за заказ!

Проверить статус заказа вы можете на сайтe http://www.myexample.ru/order_status/

<?php if ($order->invoiceForm): ?>

	Ваш заказ оформлен.

	<?php if (($invoiceEntity = $reserve->getInvoiceEntity()) instanceof OrderInvoice): ?>
		Во вложении к письму находится счет <?php echo $invoiceEntity->getAccountNumber(); ?>,
		который необходимо оплатить в течение 5 банковских дней.
	<?php else: ?>
		После подтверждения заказа поставщиком, вы получите счёт,
		который необходимо оплатить в течении 5 банковских дней.
	<?php endif; ?>

	Доставка осуществляется *после поступления оплаты* на наш счет.

<?php else: ?>

	Номер вашего заказа: <?php echo $reserve->getId(); ?>

	Ваша заявка принята на обработку и в ближайшее время с вами свяжется менеджер нашей компании.
	<?php if ($order->isMovingRequired()): ?>
		Обращаем ваше внимание, что в связи с тем,
		что выбранный вами товар расположен на удаленных складах срок доставки может быть увеличен.
	<?php endif; ?>

<?php endif; ?>


<?php if (!$orderForm->getDeliveryModel()->getOrderType()->getIsCategoryRussiaTc()): ?>
	Доставка осуществляется только *до подъезда* дома и производится с 11:00 до 18:00.
<?php endif; ?>

Оплата производится в рублях по ценам, указанным на сайте на день заказа.

Состав заказа
--------------------------

<?php echo $this->render('_order_goods_text', ['goods' => $goods]); ?>

<?php echo $this->render('_shipping_details_text', [
	'orderForm' => $orderForm,
	'order' => $order,
]); ?>

Если у вас возникли вопросы - обращайтесь к менеджерам по телефонам:
Телефон единой справочной: <?php echo $region->phone; ?>


Если у вас есть замечания и предложения относительно качества товара и предоставляемых услуг,
вы можете их отправить нам через форму на сайте по адресу http://www.myexample.ru/abuse


Спасибо, что воспользовались услугами нашего магазина!