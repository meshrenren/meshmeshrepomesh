<?php
/* @var $this yii\web\View */
$this->title = 'Savings Account';
$savingsAccount = yii\helpers\Html::encode(json_encode($savingsAccount, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$savingsProduct = yii\helpers\Html::encode(json_encode($savingsProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<savings-account-create
	:data-savings-account='<?= $savingsAccount ?>'
	:data-savings-product='<?= $savingsProduct ?>'
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'
	>
</savings-account-create>