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
        	$emp =json_decode($_POST['employee']);

        	$model = new \app\models\Member;
        	$model->attributes = (array)$emp->detail;
        	$model->user_id = null;
        	if($model->member_type_id != null)
        		$model->member_type_id = $emp->detail->member_type_id->value;
        	if($model->station_id != null)
        		$model->station_id = $emp->detail->station_id->value;
        	if($model->division_id != null)
        		$model->division_id = $emp->detail->division_id->value;
        	$model->validate();

        	//User
        	$user = new \app\models\User;
       		$user->setScenario('create');
        	$user->attributes = (array)$emp->user;
        	$user->first_name = $model->first_name;
        	$user->last_name = $model->last_name;
        	$user->level_id = 5;
        	$user->is_active = 1;
        	$user->is_member = 1;
        	$user->validate();

        	//Family
        	$family = new \app\models\MemberFamily;
        	$family->attributes = (array)$emp->family;
        	$family->validate();

        	//Address
        	$address = new \app\models\MemberAddress;
        	$address->attributes = (array)$emp->address;
        	$address->is_permanent = 1;
        	$address->validate();

        	if( $model->hasErrors() || $user->hasErrors() || $family->hasErrors() || $address->hasErrors() ){

        		$error = array();
        		$error['detail'] = $model->getErrors();
        		$error['user'] = $user->getErrors();
        		$error['family'] = $family->getErrors();
        		$error['address'] = $address->getErrors();

        		return [
	        		'success' 	=> false,
	        		'status'	=> 'has-error', 
	        		'error'		=> $error
	            ];
        	}
        	else{

        		if($model->save()){
        			$user->save();

        			//Save user id to member table
        			$model->user_id = $user->id;
        			$model->update();

        			$family->member_id = $model->id;
        			$family->save();

        			$address->member_id = $model->id;
        			$address->save();

        			return [
		        		'success' 	=> true,
		        		'status'	=> 'okay'
		            ];

        		}

        		return [
		        	'success' 	=> false,
	        		'status'	=> 'save-failed'
		        ];

        	}

        	
        }
    }

}
