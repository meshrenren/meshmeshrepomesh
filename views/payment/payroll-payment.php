<?php
/* @var $this yii\web\View */
$this->title = 'Payment Record';

$paymentModel = yii\helpers\Html::encode(json_encode($paymentModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$pytPayrollModel = yii\helpers\Html::encode(json_encode($pytPayrollModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$pytPayrollListModel = yii\helpers\Html::encode(json_encode($pytPayrollListModel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$memberList = yii\helpers\Html::encode(json_encode($memberList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>

<payment-payroll
	:data-payment-model='<?= $paymentModel ?>'
	:data-payroll-model='<?= $pytPayrollModel ?>'
	:data-payroll-list-model='<?= $pytPayrollListModel ?>'
	:data-mamber-list='<?= $memberList ?>'
	>
</payment-payroll>