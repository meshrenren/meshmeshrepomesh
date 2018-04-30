<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\User;

$this->title = 'Login';
?>

<div class="login-box">

    <div class="login-box-body">
        <div class="login-logo">        
            <img src = "/images/coop_logo.png" />
            <b>DILG XI</b> - EMPC
        </div>

        <?php 
                $form = ActiveForm::begin(
                [
                    'id' => 'login-form',
                ]); 
            ?>
                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <div class="row">
                    <div class = "col-md-8">
                        <a href="#">I forgot my password</a><br>
                    </div>
                    <div class="col-lg-4">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>
                </div>

        <?php ActiveForm::end(); ?>
        
        

      </div>
      <!-- /.login-box-body -->
</div>
