<?php
/* @var $this yii\web\View */
$this->title = 'Cut Off Savings';

$savingsAccount = yii\helpers\Html::encode(json_encode($savingsAccount, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<savings-cut-off
	:data-transaction='<?= $savingsAccount ?>'>
</savings-cut-off>
