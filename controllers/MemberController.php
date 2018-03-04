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

        $stationList  = \app\models\Station::find()
            ->select([
            	'id as value',
            	'name as label'
            ])
        	->asArray()->all();
        $divisionList  = \app\models\Division::find()
            ->select([
            	'id as value',
            	'name as label'
            ])
        	->asArray()->all();
        $typeList  = \app\models\MembershipType::find()
            ->select([
            	'id as value',
            	'description as label'
            ])
        	->asArray()->all();
        $branchList  = \app\models\Branch::find()
            ->select([
            	'id as value',
            	'branch_desc as label'
            ])
        	->asArray()->all();

        return $this->render('create', [
        		'stationList'	=> $stationList,
        		'divisionList'	=> $divisionList,
        		'typeList'		=> $typeList,
        		'branchList'	=> $branchList
        	]);
    }

    public function actionSaveMember(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$model = new \app\models\Member;

        	$emp =json_decode($_POST['employee']);
        	$model->attributes = $emp;
        	$model->validate();
        	$model_errors = $model->getErrors();

        	return [
        		'success' 	=> true,
        		'error'		=> $model_errors
            ];
        }
    }

}
