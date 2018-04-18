<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ShareaccountSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="shareaccount-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'accountnumber') ?>

    <?= $form->field($model, 'fk_memid') ?>

    <?= $form->field($model, 'NoOfShares') ?>

    <?= $form->field($model, 'totalSubscription') ?>

    <?= $form->field($model, 'balance') ?>

    <?php // echo $form->field($model, 'dateCreated') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
