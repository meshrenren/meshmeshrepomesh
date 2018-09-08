<?php
/* @var $this yii\web\View */
$this->title = 'Savings Transaction';

$savingsTransaction = yii\helpers\Html::encode(json_encode($savingsTransaction, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<savings-withdraw-form
	:data-transaction='<?= $savingsTransaction ?>'
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'>
</savings-withdraw-form>
