<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Shareaccount */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="shareaccount-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'accountnumber')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fk_memid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'NoOfShares')->textInput() ?>

    <?= $form->field($model, 'totalSubscription')->textInput() ?>

    <?= $form->field($model, 'balance')->textInput() ?>

    <?= $form->field($model, 'dateCreated')->textInput() ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
