<?php
/* @var $this yii\web\View */
$this->title = 'Payment Record';

$model = yii\helpers\Html::encode(json_encode($model, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$particularList = yii\helpers\Html::encode(json_encode($particularList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<payment-record
	:data-model='<?= $model ?>'
	:data-particular-list='<?= $particularList ?>'
	>
</payment-record>