<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Permission Access';

    $levels = yii\helpers\Html::encode(json_encode($levels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $accessList = yii\helpers\Html::encode(json_encode($accessList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<permission-settings
    :data-levels='<?= $levels ?>'
    :data-access-list='<?= $accessList ?>'
    :can-edit='<?= json_encode(Yii::$app->user->identity->checkUserAccess("_settings_", "_view")) ?>'>
</permission-settings>

