<?php

use common\components\payments\PaymentInvoice;
use common\models\DiskGood;
use common\models\TyreGood;
use domain\entities\service1c\OrderInvoice;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \domain\entities\service1c\OrderReserve $reserve
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

Внимательно прочтите эту информацию до конца!

Для Вас зарезервированы <?php echo $wws; ?> в магазине: <?php echo $shop->getTitleShort(); ?>


Номер Вашего резерва: <?php echo $reserve->getId(); ?>


<?php // Если московская группа ?>
<?php if ($regionComponent->isRegionInMoscowGroup($region) && !$order->invoiceForm): ?>
	<?php // Если оплата по безналу ?>
	<?php if ($orderForm->getDeliveryModel()->getPaymentModel() instanceof PaymentInvoice): ?>

		<?php // Если есть счёт ?>
		<?php if (($invoiceEntity = $reserve->getInvoiceEntity()) instanceof OrderInvoice): ?>
			Во вложении к письму находится счет, который необходимо оплатить в течение 5 банковских дней.
		<?php else: ?>
			Если вы не получили счет, пожалуйста, обратитесь к нам по телефону.
		<?php endif; ?>


		При получении заказа при себе необходимо иметь доверенность от организации с синей печатью.

	<?php else: ?>

		Заказанный Вами товар необходимо выкупить до <?php echo $order->reserveEndDt; ?>.
		После закрытия магазина резерв аннулируется.

	<?php endif; ?>
<?php endif; ?>

<?php // Если НЕ московская группа ?>
<?php if (!$regionComponent->isRegionInMoscowGroup($region)): ?>
	<?php if ($order->invoiceForm): ?>

		<?php if (($invoiceEntity = $reserve->getInvoiceEntity()) instanceof OrderInvoice): ?>
			Во вложении к письму находится счет, который необходимо оплатить в течение 5 банковских дней.
		<?php else: ?>
			Если вы не получили счет, пожалуйста, обратитесь к нам по
			телефону <?php echo $region->phone; ?>.
		<?php endif; ?>


		<?php if ($orderForm->getDeliveryModel()->getPaymentModel() instanceof PaymentInvoice): ?>
			При получении заказа при себе необходимо иметь
			доверенность от организации с синей печатью.
		<?php endif; ?>

	<?php else: ?>

		<?php if ($region->isMovementToRegion()): ?>

			Заказанный Вами товар перемещается с удаленного склада.
			Мы проинформируем Вас обе всех этапах формирования отгрузки и поступлении товара в магазин.

		<?php else: ?>

			Пожалуйста, дождитесь подтверждения готовности заказа по SMS.
			Вы также можете уточнить статус заказа по телефону.

			Заказанный Вами товар необходимо выкупить до <?php echo $order->reserveEndDt; ?>.
			После закрытия магазина резерв аннулируется.

		<?php endif; ?>

	<?php endif; ?>
<?php endif; ?>

Сообщите нам если, по каким-либо причинам, Вы не сможете забрать заказанный товар.

Адрес магазина: <?php echo $shop->location, ', ', $shop->address; ?>


Часы работы и схема проезда: <?php echo Url::to(['/site/index', '#' => "!/shops/{$shop->url}"], true); ?>

Если у вас возникли вопросы - обращайтесь к менеджерам по телефонам:
Телефон единой справочной: <?php echo $region->phone; ?>


Состав заказа
--------------------------

<?php echo $this->render('_order_goods_text', ['goods' => $goods]); ?>

Если у Вас есть замечания и предложения относительно качества товара и предоставляемых услуг,
Вы можете их отправить нам через форму на сайте по адресу http://www.myexample.ru/abuse

Вы так же можете оценить качество нашего магазина на Яндекс.Маркете.
http://market.yandex.ru/shop/596/reviews/add

Спасибо, что воспользовались услугами нашего магазина!