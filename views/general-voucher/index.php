<?php
/* @var $this yii\web\View */
$this->title = 'General Voucher';

$voucherModel = yii\helpers\Html::encode(json_encode($voucherModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$detailsModel = yii\helpers\Html::encode(json_encode($detailsModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$particularList = yii\helpers\Html::encode(json_encode($particularList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$voucherList = yii\helpers\Html::encode(json_encode($voucherList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<voucher-create
	:data-model='<?= $voucherModel ?>'
	:data-voucher-list='<?= $voucherList ?>'
	:data-details-model='<?= $detailsModel ?>'
	:data-particular-list='<?= $particularList ?>'
	>
</voucher-create>