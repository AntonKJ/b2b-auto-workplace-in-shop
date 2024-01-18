<?php

use common\models\DiskGood;
use common\models\TyreGood;

/**
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \domain\entities\service1c\OrderReserve $reserve
 * @var \common\models\Shop $shop
 * @var \common\models\Region $region
 * @var \common\components\ShoppingCartItem $good
 */

$total = [
	'price' => 0.0,
	'amount' => 0,
];
?>
<?php if (isset($goods[TyreGood::GOOD_ENTITY_TYPE])): ?>

	Шины
	=====

	<?php foreach ($goods[TyreGood::GOOD_ENTITY_TYPE] as $good): ?>
		<?php
		$total['price'] += (float)$good->getPriceTotal();
		$total['amount'] += (int)$good->getAmountReal();
		?>

		Наименование: <?php echo implode(' ', [$good->getGood()->brand_title, $good->getGood()->model_title]); ?> <?php echo $good->getGood()->getSize()->format(); ?>

		Код товара: (<?php echo $good->getGood()->sku_brand; ?>), код товара: <?php echo $good->getGood()->getGoodId(); ?>

		Цена: <?php echo number_format((float)$good->getGood()->getPrice(), 0, '.', ''); ?>

		Кол-во: <?php echo $good->getAmountReal(); ?>

		Сумма: <?php echo number_format((float)$good->getPriceTotal(), 0, '.', ''); ?> руб.


	<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($goods[DiskGood::GOOD_ENTITY_TYPE])): ?>

	Диски
	=====

	<?php foreach ($goods[DiskGood::GOOD_ENTITY_TYPE] as $good): ?>
		<?php
		$total['price'] += (float)$good->getPriceTotal();
		$total['amount'] += (int)$good->getAmountReal();
		?>

		Наименование: <?php echo implode(' ', [$good->getGood()->brand->title, $good->getGood()->model->title]); ?> <?php echo $good->getGood()->getSize()->format(); ?>

		Код товара: (<?php echo $good->getGood()->sku_brand; ?>), код товара: <?php echo $good->getGood()->getGoodId(); ?>

		Цена: <?php echo number_format((float)$good->getGood()->getPrice(), 0, '.', ''); ?>

		Кол-во: <?php echo $good->getAmountReal(); ?>

		Сумма: <?php echo number_format((float)$good->getPriceTotal(), 0, '.', ''); ?> руб.


	<?php endforeach; ?>
<?php endif; ?>

Кол-во: <?php echo $total['amount']; ?>

ИТОГО: <?php echo number_format($total['price'], 0, '.', ''); ?>

