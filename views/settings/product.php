<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Product Settings';

    $pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<product-settings
    :page-data='<?= $pageData ?>'
    :can-edit='<?= json_encode(Yii::$app->user->identity->checkUserAccess("_product_settings_", "_view")) ?>'>
</product-settings>