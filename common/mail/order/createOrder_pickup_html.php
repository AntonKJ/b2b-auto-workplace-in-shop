<?php

use common\components\payments\PaymentInvoice;
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

<p><strong>Внимательно прочтите эту информацию до конца!</strong></p>

<p>Для Вас зарезервированы <?php echo $wws; ?> в магазине:</p>

<p><strong><?php echo $shop->getTitleShort(); ?></strong></p>

<h2>Номер Вашего резерва: <?php echo $reserve->getId(); ?></h2>

<?php // Если московская группа ?>
<?php if ($regionComponent->isRegionInMoscowGroup($region) && !$order->invoiceForm): ?>
	<?php // Если оплата по безналу ?>
	<?php if ($orderForm->getDeliveryModel()->getPaymentModel() instanceof PaymentInvoice): ?>

		<?php // Если есть счёт ?>
		<?php if (($invoiceEntity = $reserve->getInvoiceEntity()) instanceof OrderInvoice): ?>
			<p>Во вложении к письму находится счет, который необходимо оплатить в течение 5 банковских дней.</p>
		<?php else: ?>
			<p>Если вы не получили счет, пожалуйста, обратитесь к нам по телефону.</p>
		<?php endif; ?>

		<p><strong>
				При получении заказа при себе необходимо иметь
				доверенность от организации с синей печатью.
			</strong></p>

	<?php else: ?>

		<p>
			Заказанный Вами товар
			<span class="highlight">необходимо выкупить до <?php echo $order->reserveEndDt; ?></span>.
			После закрытия магазина резерв <span class="highlight">аннулируется</span>.
		</p>

	<?php endif; ?>
<?php endif; ?>

<?php // Если НЕ московская группа ?>
<?php if (!$regionComponent->isRegionInMoscowGroup($region)): ?>
	<?php if ($order->invoiceForm): ?>

		<?php if (($invoiceEntity = $reserve->getInvoiceEntity()) instanceof OrderInvoice): ?>
			<p>Во вложении к письму находится счет, который необходимо оплатить в течение 5 банковских дней.</p>
		<?php else: ?>
			<p>
				Если вы не получили счет, пожалуйста, обратитесь к нам по
				телефону <strong><?php echo $region->phone; ?></strong>.
			</p>
		<?php endif; ?>

		<?php if ($orderForm->getDeliveryModel()->getPaymentModel() instanceof PaymentInvoice): ?>
			<p><strong>
					При получении заказа при себе необходимо иметь
					доверенность от организации с синей печатью.
				</strong></p>
		<?php endif; ?>

	<?php else: ?>

		<?php if ($region->isMovementToRegion()): ?>
			<p>
				Заказанный Вами товар <span class="highlight">перемещается с удаленного склада.</span>
				Мы проинформируем Вас обе всех этапах формирования отгрузки и
				<span class="highlight">поступлении товара в магазин.</span>
			</p>
		<?php else: ?>

			<p>
				Пожалуйста, дождитесь подтверждения <b>готовности заказа по SMS</b>.
				Вы также можете <b>уточнить статус заказа по телефону</b>.
			</p>

			<p>
				Заказанный Вами товар
				<span class="highlight">необходимо выкупить до <?php echo $order->reserveEndDt; ?>.</span>
				После закрытия магазина резерв <span class="highlight">аннулируется.</span>
			</p>

		<?php endif; ?>

	<?php endif; ?>
<?php endif; ?>

<p>Сообщите нам если, по каким-либо причинам, Вы не сможете забрать заказанный товар.</p>

<h2>Адрес магазина</h2>

<p><?php echo $shop->location, ', ', $shop->address; ?></p>

<p><?php echo Html::a('Часы работы и схема проезда', Url::to(['/site/index', '#' => "!/shops/{$shop->url}"], true)); ?></p>

<p>Если у вас возникли вопросы - обращайтесь к менеджерам по телефонам:</p>

<p>Телефон единой справочной: <?php echo $region->phone; ?>.</p>

<h3>Состав заказа</h3>

<?php echo $this->render('_order_goods_html', ['goods' => $goods]); ?>
<br/>

<p>
	Если у Вас есть замечания и предложения относительно качества товара и предоставляемых услуг,
	Вы можете их отправить нам через форму на сайте по адресу <a href="http://www.myexample.ru/abuse">http://www.myexample.ru/abuse</a>.
</p>

<p>
	Вы так же можете <a href="http://market.yandex.ru/shop/596/reviews/add">оценить качество</a> нашего магазина на
	Яндекс.Маркете.
</p>

<p>Спасибо, что воспользовались услугами нашего магазина!</p>