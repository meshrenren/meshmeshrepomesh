<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Permission Access';

    $levels = yii\helpers\Html::encode(json_encode($levels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $superAdminAccess = yii\helpers\Html::encode(json_encode($superAdminAccess, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<permission-settings
    :data-levels='<?= $levels ?>'
    :data-super-admin='<?= $superAdminAccess ?>'
    :base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'
    :can-edit='<?= json_encode(Yii::$app->user->identity->checkUserAccess("_settings_", "_view")) ?>'>
</permission-settings>

