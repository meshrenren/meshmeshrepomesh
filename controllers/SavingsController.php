<?php

namespace app\controllers;

class SavingsController extends \yii\web\Controller
{
    public function actionIndex()
    {
    	$this->layout = 'main-vue';
        return $this->render('index');
    }

}
