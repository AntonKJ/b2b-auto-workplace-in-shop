<?php

use common\components\deliveries\DeliveryRussiaTc;

/**
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \common\models\forms\Order $order
 */

$delivery = $orderForm->getDeliveryModel();
$deliveryTypeComponent = $orderForm->getDeliveryTypeComponent();
?>

Способ покупки: <?php echo $deliveryTypeComponent->getTitle(); ?>


Предполагаемая дата: <?php echo $order->deliveryDt; ?>


Адрес доставки: <?php echo $order->deliveryAddress ?>


<?php if ($deliveryTypeComponent instanceof DeliveryRussiaTc): ?>
Транспортная компания: <?php echo $delivery->tcText; ?>
<?php endif; ?>


