<?php
/* @var $this yii\web\View */
$this->title = 'View General Voucher';

$voucherList = yii\helpers\Html::encode(json_encode($voucherList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

?>
<voucher-view
	:data-voucher-list='<?= $voucherList ?>'
	>
</voucher-view>