<?php

use common\components\Html;
use common\models\DiskGood;
use common\models\TyreGood;
use yii\helpers\Url;

/**
 * @var \common\models\forms\B2BOrderForm $orderForm
 * @var \domain\entities\service1c\OrderReserve $reserve
 * @var \common\models\Shop $shop
 * @var \common\models\Region $region
 * @var \common\components\ShoppingCartItem $good
 */

?>

<table class="content-table">

	<thead>
	<tr>
		<td>Модель</td>
		<td>Размер</td>
		<td>Цена</td>
		<td>Количество, шт.</td>
		<td>Сумма</td>
	</tr>
	</thead>

	<tbody>

	<?php
	//todo отрефакторить
	$total = [
		'price' => 0.0,
		'amount' => 0,
	];
	?>

	<?php if (isset($goods[TyreGood::GOOD_ENTITY_TYPE])): ?>
		<tr>
			<td colspan="5">Шины</td>
		</tr>
		<?php foreach ($goods[TyreGood::GOOD_ENTITY_TYPE] as $good): ?>

			<?php
			$total['price'] += (float)$good->getPriceTotal();
			$total['amount'] += (int)$good->getAmountReal();
			?>

			<tr>
				<td>
					<div>
						<?php echo Html::a(implode(' ', [$good->getGood()->brand_title, $good->getGood()->model_title]), Url::to(['/site/index', '#' => implode('/', [
							'!',
							$good->getGood()->getTypeCode(),
							$good->getGood()->brand_slug,
							$good->getGood()->model_slug,
							$good->getGood()->getGoodId(),
						])], true)); ?>
					</div>
					<div><?php echo $good->getGood()->getSize()->format(); ?></div>
					<div>
						(<?php echo $good->getGood()->sku_brand; ?>),
						код товара: <?php echo $good->getGood()->getGoodId(); ?>
					</div>
				</td>
				<td><?php echo $good->getGood()->getSize()->format(); ?></td>
				<td><?php echo number_format((float)$good->getGood()->getPrice(), 0, '.', '&nbsp;'); ?></td>
				<td><?php echo $good->getAmountReal(); ?></td>
				<td><?php echo number_format((float)$good->getPriceTotal(), 0, '.', '&nbsp;'); ?>&nbsp;руб.</td>
			</tr>

		<?php endforeach; ?>
	<?php endif; ?>

	<?php if (isset($goods[DiskGood::GOOD_ENTITY_TYPE])): ?>
		<tr>
			<td colspan="5">Диски</td>
		</tr>
		<?php foreach ($goods[DiskGood::GOOD_ENTITY_TYPE] as $good): ?>

			<?php
			$total['price'] += (float)$good->getPriceTotal();
			$total['amount'] += (int)$good->getAmountReal();
			?>

			<tr>
				<td>
					<div>
						<?php echo Html::a(implode(' ', [$good->getGood()->brand->title, $good->getGood()->model->title]), Url::to(['/site/index', '#' => implode('/', [
							'!',
							$good->getGood()->getTypeCode(),
							$good->getGood()->brand_slug,
							$good->getGood()->model_slug,
							$good->getGood()->getVariationId(),
							$good->getGood()->getGoodId(),
						])], true)); ?>
					</div>
					<div><?php echo $good->getGood()->getSize()->format(); ?></div>
					<div>
						(<?php echo $good->getGood()->sku_brand; ?>),
						код товара: <?php echo $good->getGood()->getGoodId(); ?>
					</div>
				</td>
				<td><?php echo $good->getGood()->getSize()->format(); ?></td>
				<td><?php echo number_format((float)$good->getGood()->getPrice(), 0, '.', '&nbsp;'); ?></td>
				<td><?php echo $good->getAmountReal(); ?></td>
				<td><?php echo number_format((float)$good->getPriceTotal(), 0, '.', '&nbsp;'); ?>&nbsp;руб.</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>

	</tbody>

	<tfoot>
	<tr>
		<td colspan="3"></td>
		<td><?php echo $total['amount']; ?></td>
		<td><?php echo number_format($total['price'], 0, '.', '&nbsp;'); ?></td>
	</tr>
	</tfoot>

</table>