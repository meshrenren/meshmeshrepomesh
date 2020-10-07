<?php
/* @var $this yii\web\View */
$this->title = 'Payment View Particular';

$pageData = yii\helpers\Html::encode(json_encode($pageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

?>
<payment-view-particular
	:page-data='<?= $pageData ?>'
	>
</payment-view-particular>