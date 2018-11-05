<?php
/* @var $this yii\web\View */
$this->title = 'Time Deposit Account';

$tdProduct = yii\helpers\Html::encode(json_encode($tdProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$tdAccount = yii\helpers\Html::encode(json_encode($tdAccount, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$tdTransaction = yii\helpers\Html::encode(json_encode($tdTransaction, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$tdlist = yii\helpers\Html::encode(json_encode($tdlist, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<time-deposit-create
	:data-time-deposit-account='<?= $tdAccount ?>'
	:data-time-deposit-transaction='<?= $tdTransaction ?>'
	:data-time-deposit-product='<?= $tdProduct ?>'
	:data-time-deposit-list='<?= $tdlist ?>'
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'
	>
</time-deposit-create>