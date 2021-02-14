<?php
/* @var $this yii\web\View */
$this->title = 'Savings List';

$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<report-savings-list
	:page-data='<?= $pageData ?>'>
</report-savings-list>