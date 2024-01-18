<?php

use common\models\DiskGood;
use common\models\Region;
use common\models\TyreGood;
use domain\entities\service1c\OrderInvoice;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \domain\entities\service1c\OrderReserve $reserve
 * @var \common\models\forms\Order $order
 * @var \common\models\Shop $shop
 * @var Region $region
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

<p>Здравствуйте!</p>

<p>Благодарим вас за заказ!</p>

<p>
	Проверить <?php echo Html::a('статус заказа', Url::to(['/site/index', '#' => "!/profile/order/details/{$reserve->getId()}"], true)); ?>
	вы можете на сайтe <?php echo Url::to(['/site/index', '#' => "!/profile/order"], true); ?>
</p>

<?php if ($order->invoiceForm): ?>

	<p>Ваш заказ оформлен.</p>

	<?php if (($invoiceEntity = $reserve->getInvoiceEntity()) instanceof OrderInvoice): ?>
		<p>
			Во вложении к письму находится счет <?php echo $invoiceEntity->getAccountNumber(); ?>,
			который необходимо оплатить в течение 5 банковских дней.
		</p>
	<?php else: ?>
		<p>
			После подтверждения заказа поставщиком, вы получите счёт,
			который необходимо оплатить в течении 5 банковских дней.
		</p>
	<?php endif; ?>

	<p>Доставка осуществляется <strong>после поступления оплаты</strong> на наш счет.</p>

<?php else: ?>

	<p>Номер вашего заказа: <?php echo $reserve->getId(); ?></p>

	<p>Ваша заявка принята на обработку и в ближайшее время с вами свяжется менеджер нашей компании.</p>

	<?php if ($order->isMovingRequired()): ?>
		<p class="highlight">
			Обращаем ваше внимание, что в связи с тем,
			что выбранный вами товар расположен на удаленных складах срок доставки может быть увеличен.
		</p>
	<?php endif; ?>

<?php endif; ?>

<?php if (!$orderForm->getDeliveryModel()->getOrderType()->getIsCategoryRussiaTc()): ?>
	<p>Доставка осуществляется только <strong>до подъезда</strong> дома и производится с 11:00 до 18:00.</p>
<?php endif; ?>

<p>Оплата производится в рублях по ценам, указанным на сайте на день заказа.</p>

<h3>Состав заказа</h3>

<?php echo $this->render('_order_goods_html', ['goods' => $goods]); ?>
<br/>

<?php echo $this->render('_shipping_details_html', [
	'orderForm' => $orderForm,
	'order' => $order,
]); ?>
<br/>

<p>Если у вас возникли вопросы - обращайтесь к менеджерам по телефонам:</p>

<p>Телефон единой справочной: <?php echo $region->phone; ?>.</p>

<p>
	Если у вас есть замечания и предложения относительно качества товара и предоставляемых услуг,
	вы можете их отправить нам через форму на сайте по адресу <a href="http://www.myexample.ru/abuse">http://www.myexample.ru/abuse</a>.
</p>

<p>Спасибо, что воспользовались услугами нашего магазина!</p>