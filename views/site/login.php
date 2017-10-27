<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
?>
<div class = "site-login">

    <div class = "row">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">

            <h1><?= Html::encode($this->title) ?></h1>

            <p>Please fill out the following fields to login</p>

            <?php 
                $form = ActiveForm::begin(
                [
                    'id'                => 'login-form',
                ]); 
            ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="form-group">
                    <div class="col-lg-12">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>
                </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
