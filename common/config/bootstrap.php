<?php

Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@b2b', dirname(__DIR__, 2) . '/b2b');
Yii::setAlias('@cp', dirname(__DIR__, 2) . '/cp');
Yii::setAlias('@api', dirname(__DIR__, 2) . '/api');
Yii::setAlias('@gate', dirname(__DIR__, 2) . '/gate');
Yii::setAlias('@console', dirname(__DIR__, 2) . '/console');

Yii::setAlias('@domain', dirname(__DIR__, 2) . '/domain');

// папка домена для медиа файлов каталога
Yii::setAlias('@media', dirname(__DIR__, 2) . '/media');

Yii::setAlias('@storage', dirname(__DIR__, 2) . '/storage');

// переопределяем, чтобы была возможность использовать функцию getAlias()
Yii::$classMap['yii\db\ActiveQuery'] = Yii::getAlias('@common/components/ActiveQuery.php');
