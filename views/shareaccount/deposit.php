<?php
/* @var $this yii\web\View */
$this->title = 'Share Transaction';

$savingsTransaction = yii\helpers\Html::encode(json_encode($savingsTransaction, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<share-deposit
	:data-transaction='<?= $savingsTransaction ?>'
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'>
</share-deposit>
