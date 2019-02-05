<?php
/* @var $this yii\web\View */
$this->title = 'General Voucher';

$model = yii\helpers\Html::encode(json_encode($model, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$particularList = yii\helpers\Html::encode(json_encode($particularList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<voucher-create
	:data-model='<?= $model ?>'
	:data-particular-list='<?= $particularList ?>'
	>
</voucher-create>