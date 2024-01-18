<?php

use b2b\modules\api\models\RecoveryForm;
use common\components\Html;

/**
 * @var RecoveryForm $form
 * @var string $sayings
 */

?>

<p>Здравствуйте!</p>

<p>Поступила новая заявка на восстановление пароля в сервисе B2B.</p>

<ul>
	<li>E-mail: <?php echo Html::encode($form->email); ?>;</li>
	<li>
		Комментарий:
		<blockquote><?php echo Html::encode($form->comment); ?></blockquote>
	</li>
</ul>
<div>---</div>
<p><?php echo $sayings; ?></p>
