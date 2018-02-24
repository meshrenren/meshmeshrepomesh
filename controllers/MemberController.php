<?php

namespace app\controllers;

use Yii;

class MemberController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate()
    {    	
        $this->layout = 'main-vue';

        return $this->render('create');
    }

}
