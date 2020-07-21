<?php
/* @var $this yii\web\View */
$this->title = 'Import Payment';

$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<payment-import
	:page-data='<?= $pageData ?>'
	>
</payment-import>