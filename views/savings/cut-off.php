<?php
/* @var $this yii\web\View */
$this->title = 'Cut Off Savings';

if($cutoffDone){
?>
	<h1>Savings Cut Off Done!</h1>
<?php
}
else{

$savingsAccount = yii\helpers\Html::encode(json_encode($savingsAccount, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<savings-cut-off
	:data-transaction='<?= $savingsAccount ?>'
	:page-data='<?= $pageData ?>'>
</savings-cut-off>
<?php } ?>
