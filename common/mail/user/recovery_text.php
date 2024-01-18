<?php

use b2b\modules\api\models\RecoveryForm;

/**
 * @var RecoveryForm $form
 * @var string $sayings
 */

?>
Здравствуйте!

Поступила новая заявка на восстановление пароля в сервисе B2B.

E-mail: <?php echo $form->email, "\n"; ?>

Комментарий:
<?php echo $form->comment, "\n"; ?>

---
<?php echo $sayings; ?>
