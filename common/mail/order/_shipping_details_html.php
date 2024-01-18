<?php

use common\components\deliveries\DeliveryRussiaTc;

/**
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \common\models\forms\Order $order
 */

$delivery = $orderForm->getDeliveryModel();
$deliveryTypeComponent = $orderForm->getDeliveryTypeComponent();
?>

	<table class="content-table">

		<tr>
			<td>Способ покупки</td>
			<td><?php echo $deliveryTypeComponent->getTitle(); ?></td>
		</tr>

		<tr>
			<td>Предполагаемая дата</td>
			<td><?php echo $order->deliveryDt; ?></td>
		</tr>

		<tr>
			<td>Адрес доставки</td>
			<td><?php echo $order->deliveryAddress ?></td>
		</tr>

		<?php if ($deliveryTypeComponent instanceof DeliveryRussiaTc): ?>
			<tr>
				<td>Транспортная компания</td>
				<td><?php echo $delivery->tcText; ?></td>
			</tr>
		<?php endif; ?>

	</table>
