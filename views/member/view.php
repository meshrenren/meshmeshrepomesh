<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Member View';

    $member = yii\helpers\Html::encode(json_encode($member, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $stationList = yii\helpers\Html::encode(json_encode($stationList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $divisionList = yii\helpers\Html::encode(json_encode($divisionList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $typeList = yii\helpers\Html::encode(json_encode($typeList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<member-view
    :data-member='<?= $member ?>'
    :data-station-list='<?= $stationList ?>'
    :data-division-list='<?= $divisionList ?>'
    :data-type-list='<?= $typeList ?>'
    :base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'>
</member-view>
