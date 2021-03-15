<?php
/* @var $this yii\web\View */
$this->title = 'Time Deposit Account';

$tdAccounts = yii\helpers\Html::encode(json_encode($tdAccounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<time-deposit-list
	:data-time-deposit-accounts='<?= $tdAccounts ?>'
	:page-data='<?= $pageData ?>'
	:type-list='<?= json_encode($typeList) ?>'
	:header='<?= json_encode($header) ?>'
	>
</time-deposit-list>