<?php
/* @var $this yii\web\View */
$this->title = 'Dividend and Refund';

$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<report-dividend-refund
	:page-data='<?= $pageData ?>'>
</report-dividend-refund>