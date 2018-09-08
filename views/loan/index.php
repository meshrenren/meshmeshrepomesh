<?php
/* @var $this yii\web\View */
$this->title = 'Loan Evaluation';

$loandProduct = yii\helpers\Html::encode(json_encode($loandProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

$default_settings = yii\helpers\Html::encode(json_encode($default_setting, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<loan-evaluation
	:data-loan-product='<?= $loandProduct ?>'
	:data-default-settings='<?= $default_settings ?>'
	>
</loan-evaluation>