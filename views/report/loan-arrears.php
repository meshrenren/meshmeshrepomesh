<?php
/* @var $this yii\web\View */
$this->title = 'Loan Aging';

$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<report-loan-arrears
	:page-data='<?= $pageData ?>'>
</report-loan-arrears>