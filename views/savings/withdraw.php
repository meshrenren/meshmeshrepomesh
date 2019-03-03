<?php
/* @var $this yii\web\View */
$this->title = 'Savings Transaction';

$savingsTransaction = yii\helpers\Html::encode(json_encode($savingsTransaction, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$voucherModel = yii\helpers\Html::encode(json_encode($voucherModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$detailsModel = yii\helpers\Html::encode(json_encode($detailsModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$particularList = yii\helpers\Html::encode(json_encode($particularList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<savings-withdraw-form
	:data-transaction='<?= $savingsTransaction ?>'
	:data-model='<?= $voucherModel ?>'
	:data-details-model='<?= $detailsModel ?>'
	:data-particular-list='<?= $particularList ?>'
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'>
</savings-withdraw-form>
