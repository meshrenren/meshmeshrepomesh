<?php
/* @var $this yii\web\View */
$this->title = 'Loan Evaluation';

$loandProduct = yii\helpers\Html::encode(json_encode($loandProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<loan-evaluation
	:data-loan-product='<?= $loandProduct ?>'
	>
</loan-evaluation>