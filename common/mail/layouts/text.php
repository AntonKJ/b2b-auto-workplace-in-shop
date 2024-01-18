<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<?php $this->beginBody() ?>
<?= $content ?>


---
Все права защищены. 2002—<?php echo date('Y'); ?> © ООО «ПРОДАЖА ШИН»
Данное письмо отправлено автоматически и не требует ответа.
<?php $this->endBody() ?>
<?php $this->endPage() ?>
