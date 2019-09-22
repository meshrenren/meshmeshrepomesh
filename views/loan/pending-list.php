<?php
/* @var $this yii\web\View */
$this->title = 'Loan Evaluation';


$ForApprovalLoans= yii\helpers\Html::encode(json_encode($ForApprovalLoans, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>

<loan-for-approval
:for-approval-loans='<?= $ForApprovalLoans?>'
>

</loan-for-approval>