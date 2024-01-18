<?php

/**
 * @var \b2b\modules\api\models\RegistrationForm $registration
 * @var string $sayings
 */
?>
Здравствуйте!

Поступила новая заявка на регистрацию в сервисе B2B.

Организация: <?php echo $registration->organization; ?>
ИНН: <?php echo $registration->inn; ?>
КПП: <?php echo $registration->kpp; ?>
БИК: <?php echo $registration->bik; ?>
Р/С: <?php echo $registration->rs; ?>
Юр. адрес: <?php echo $registration->uraddress; ?>
Факт. адрес: <?php echo $registration->address; ?>
E-mail: <?php echo $registration->email; ?>
Телефон: <?php echo $registration->getPhoneFormatted(); ?>
Сайт: <?php echo $registration->website; ?>

---
<?php echo $sayings; ?>
