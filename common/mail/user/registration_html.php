<?php

use common\components\Html;

/**
 * @var \b2b\modules\api\models\RegistrationForm $registration
 * @var string $sayings
 */

?>

<p>Здравствуйте!</p>

<p>Поступила новая заявка на регистрацию в сервисе B2B.</p>

<ul>
	<li>Организация: <?php echo Html::encode($registration->organization); ?>;</li>
	<li>ИНН: <?php echo Html::encode($registration->inn); ?>;</li>
	<li>КПП: <?php echo Html::encode($registration->kpp); ?>;</li>
	<li>БИК: <?php echo Html::encode($registration->bik); ?>;</li>
	<li>Р/С: <?php echo Html::encode($registration->rs); ?>;</li>
	<li>Юр. адрес: <?php echo Html::encode($registration->uraddress); ?>;</li>
	<li>Факт. адрес: <?php echo Html::encode($registration->address); ?>;</li>
	<li>E-mail: <?php echo Html::encode($registration->email); ?>;</li>
	<li>Телефон: <?php echo Html::encode($registration->getPhoneFormatted()); ?>;</li>
	<li>Сайт: <?php echo Html::encode($registration->website); ?>.</li>
</ul>

<p><?php echo $sayings; ?></p>