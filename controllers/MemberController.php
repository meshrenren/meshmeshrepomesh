<?php

namespace app\controllers;

use Yii;

class MemberController extends \yii\web\Controller
{

    public function actionIndex($state)
    {    	
        $this->layout = 'main-vue';
  //       $emp  = new \app\models\Member();
		// $att = $emp->attributes();

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

        $members = \app\models\Member::find()->innerJoinWith(['user'])
        	->joinWith(['memberType', 'division', 'station'])
        	->asArray()->all();

        $view = "member-" . $state;

        return $this->render('create', [
        		'stationList'	=> $stationList,
        		'divisionList'	=> $divisionList,
        		'typeList'		=> $typeList,
        		'memberList'	=> $members,
        		'view'			=> $view
        	]);
    }

    public function actionView($member_id){
        $this->layout = 'main-vue';

        $stationList  = \app\models\Station::find()
            ->select([
            	"id as value",
            	"name as label",
            	"CONCAT('station_id','') as column_name"
            ])
        	->asArray()->all();
        $divisionList  = \app\models\Division::find()
            ->select([
            	"id as value",
            	"name as label",
            	"CONCAT('division_id','') as column_name"
            ])
        	->asArray()->all();
        $typeList  = \app\models\MembershipType::find()
            ->select([
            	"id as value",
            	"description as label",
            	"CONCAT('member_type_id','') as column_name"
            ])
        	->asArray()->all();

        $member = \app\models\Member::find()->innerJoinWith(['user'])
        	->where(['member.id' => $member_id])
        	->joinWith(['memberType', 'division', 'station'])
        	->select([
            	"member.*",
            	"users.username as username",
            	"users.email as email"
            ])
        	->asArray()->one();

        $memberFamily = \app\models\MemberFamily::find()
        		->where(['member_id' => $member_id])
        		->asArray()->all();

        $memberAddress = \app\models\MemberAddress::find()
        		->where(['member_id' => $member_id])
        		->asArray()->all();

    	return $this->render('view', [
        		'stationList'	=> $stationList,
        		'divisionList'	=> $divisionList,
        		'typeList'		=> $typeList,
        		'member'		=> $member,
        		'memberFamily'	=> $memberFamily,
        		'memberAddress'	=> $memberAddress,
        ]);
    }

    public function actionGetMembers(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){

        	$members = \app\models\Member::find()->innerJoinWith(['user'])
        	->joinWith(['memberType', 'division', 'station'])
        	->asArray()->all();


        	return [
		        'success' 	=> false,
	        	'data'	=> $members
		    ];
        }

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
        	if($model->civil_status != null)
        		$model->civil_status = $emp->detail->civil_status->value;
        	if($model->gender != null)
        		$model->gender = $emp->detail->gender->value;
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
        			$user->password = sha1($user->password);
        			$user->save();

        			//Save user id to member table
        			$model->user_id = $user->id;
        			$model->update();

        			$family->member_id = $model->id;
        			$family->save();

        			$address->member_id = $model->id;
        			$address->save();

        			$members = \app\models\Member::find()->innerJoinWith(['user'])
        				->joinWith(['memberType', 'division', 'station'])
        				->asArray()->all();

        			return [
		        		'success' 	=> true,
		        		'status'	=> 'okay',
	        			'data'		=> $members
		            ];

        		}



        		return [
		        	'success' 	=> false,
	        		'status'	=> 'save-failed'
		        ];

        	}

        	
        }
    }

    public function actionUpdateMember(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	if($_POST['table'] == 'member'){
        		$model = \app\models\Member::find()->where(['id' => $_POST['member_id']])->one();
        	}
        	else if($_POST['table'] == 'user'){
        		$model = \app\models\User::find()->where(['id' => $_POST['user_id']])->one();        		
        	}

        	if(isset($model) && $model != null){
        		$label = $_POST['label'];
        		$value = $_POST['value'];
        		$model->$label = $value;
        		if($model->save()){
        			if($label == 'fist_name' || $label == 'last_name'){
        				$user = \app\models\User::find()->where(['id' => $model->user_id])->one(); 
        				$user->$label = $value;
        			}
        			$member = \app\models\Member::find()->innerJoinWith(['user'])
        				->joinWith(['memberType', 'division', 'station'])
			        	->select([
			            	"member.*",
			            	"users.username as username",
			            	"users.email as email"
			            ])
        				->asArray()->one();

        			return [
		        		'success' 	=> true,
		        		'status'	=> 'okay',
	        			'data'		=> $member
		            ];
        		}
        		else{
        			$error = $model->getErrors();
        			return [
		        		'success' 	=> false,
		        		'status'	=> 'has-error',
	        			'error'		=> $error
		            ];
        		}
        	}
        	return [
		        'success' 	=> false,
		        'status'	=> 'save-failed'
		    ];
        }
    }

    function actionUpdateFamilyMember(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$model = \app\models\MemberFamily::find()->where(['id' => $_POST['family_id']])->one(); 
        	if(isset($model) && $model != null){
        		$label = $_POST['label'];
        		$value = $_POST['value'];
        		$model->$label = $value;
        		if($model->save()){
        			$memberFamily = \app\models\MemberFamily::find()
		        		->where(['member_id' => $model->member_id])
		        		->asArray()->all();

		        	return [
		        		'success' 	=> true,
		        		'status'	=> 'okay',
	        			'data'		=> $memberFamily
		            ];
        		}
        	}

        	return [
		        'success' 	=> false,
		        'status'	=> 'save-failed'
		    ];
        }
    }

}
