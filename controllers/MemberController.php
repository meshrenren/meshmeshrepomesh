<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Member;

use app\helpers\accounts\LoanHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;
use app\helpers\accounts\TimeDepositHelper;

class MemberController extends \yii\web\Controller
{

     /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_member_profile_","_view") ){
                                    return true;
                            }
                        }
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_member_profile_", "_view") || $_GET['member_id'] == Yii::$app->user->identity->member->id){
                                    return true;
                            }
                        }
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }


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
    
    public function actionGetMember()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$model = new Member();
    	
    	$post = \Yii::$app->getRequest()->getBodyParams();
        if(count($post['joinWith']) > 0)
            return $model->getMemberList($post['nameInput'], $post['joinWith']);
        else
            return $model->getMemberList($post['nameInput']);
    	
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


    public function actionGetAllAccounts(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $id = $post['id'];
            $name = $post['name'];

            $getSavings = [];
            $getShare = [];
            $getTimedeposits = [];
            $getLoan = [];

            $filter = ['member_id' => $id];

            $getSavings = SavingsHelper::getAccountSavingsInfo($filter);
            $getShare = SavingsHelper::getAccountShareInfo($filter);
            $getTimedeposits = TimeDepositHelper::getAccountTDInfo($filter);
            $getLoan = LoanHelper::getAccountLoanInfo($id);
        
            return [
                'savingsAccounts'        => $getSavings,
                'shareAccounts'          => $getShare,
                'timedepositAccounts'    => $getTimedeposits,
                'loanAccounts'           => $getLoan
            ];
            
        }

    }


    public function actionGetAccounts(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $member_id = $post['member_id'];
            $getSavings = \app\models\SavingsAccount::find()->innerJoinWith(['product'])->joinWith(['member'])->where(['member_id' => $member_id])->asArray()->all();

            $getShare = \app\models\Shareaccount::find()->innerJoinWith(['product'])->joinWith(['member'])->where(['fk_memid' => $member_id])->asArray()->all();

            $tdAcc = new \app\models\TimeDepositAccount;
            $getTimedeposits = $tdAcc->getAccountListByMemberID($member_id);

            $loanAcc = new \app\models\LoanAccount;
            $getLoan = $loanAcc->getAccountListByMemberID($member_id);
        
            return [
                'savingsAccounts'        => $getSavings,
                'shareAccounts'          => $getShare,
                'timedepositAccounts'    => $getTimedeposits,
                'loanAccounts'           => $getLoan
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
        		$model->member_type_id = $emp->detail->member_type_id;
        	if($model->station_id != null)
        		$model->station_id = $emp->detail->station_id;
        	if($model->division_id != null)
        		$model->division_id = $emp->detail->division_id;
        	if($model->civil_status != null)
        		$model->civil_status = $emp->detail->civil_status;
        	if($model->gender != null)
        		$model->gender = $emp->detail->gender;
        	$model->validate();

        	//User
        	$user = new \app\models\User;
       		$user->setScenario('create');
        	$user->attributes = (array)$emp->user;
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
        		$error['address'] = $address->getErrors();
                $error['family'] = $family->getErrors();

        		return [
	        		'success' 	=> false,
	        		'status'	=> 'has-error', 
	        		'error'		=> $error
	            ];
        	}
        	else{
                $model->image_path = "/images/user.png";
        		if($model->save()){
        			$user->password = sha1($user->password);
                    $user->password_text = $user->password;
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
        			$member = \app\models\Member::find()->innerJoinWith(['user'])
                        ->where(['member.id' => $_POST['member_id']])
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

    //Family Member
    function actionAddMemberFamily(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
            $model = new \app\models\MemberFamily; 
            if(isset($model) && $model != null){
                $familyMember = json_decode($_POST['familyMember']);
                $model->attributes = (array)$familyMember;
                $model->member_id = $_POST['member_id'];
                if($model->save()){
                    $getFamily = \app\models\MemberFamily::find()
                        ->where(['id' => $model->id])
                        ->asArray()->one();

                    return [
                        'success'   => true,
                        'status'    => 'okay',
                        'data'      => $getFamily
                    ];
                }
                else{
                    $getErrors = array();
                    if($model->hasErrors())
                        $getErrors = $model->getErrors();
                    return [
                        'success'   => false,
                        'status'    => 'has-error',
                        'data'      => $getErrors
                    ];
                }
            }

            return [
                'success'   => false,
                'status'    => 'save-failed'
            ];
        }
    }

    function actionUpdateMemberFamily(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$model = \app\models\MemberFamily::find()->where(['id' => $_POST['family_id']])->one(); 
        	if(isset($model) && $model != null){
        		$label = $_POST['label'];
        		$value = $_POST['value'];
        		$model->$label = $value;
        		if($model->save()){
        			$getFamily = \app\models\MemberFamily::find()
		        		->where(['id' => $model->id])
		        		->asArray()->one();

		        	return [
		        		'success' 	=> true,
		        		'status'	=> 'okay',
	        			'data'		=> $getFamily
		            ];
        		}
        	}

        	return [
		        'success' 	=> false,
		        'status'	=> 'save-failed'
		    ];
        }
    }

    function actionDeleteMemberFamily(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
            $model = \app\models\MemberFamily::find()->where(['id' => $_POST['family_id']])->one(); 
            $id = $model->id;
            if($model->delete()){
                return [
                    'success'   => true,
                    'status'    => 'okay',
                    'data'      => $id
                ];
            }
            return [
                'success'   => false,
                'status'    => 'delete-failed'
            ];
        }
    }

    //Address Member
    function actionAddMemberAddress(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
            $model = new \app\models\MemberAddress; 
            if(isset($model) && $model != null){
                $addressMember = json_decode($_POST['addressMember']);
                $model->attributes = (array)$addressMember;
                $model->member_id = $_POST['member_id'];
                if($model->save()){
                    $getAddress = \app\models\MemberAddress::find()
                        ->where(['id' => $model->id])
                        ->asArray()->one();

                    return [
                        'success'   => true,
                        'status'    => 'okay',
                        'data'      => $getAddress
                    ];
                }
                else{
                    $getErrors = array();
                    if($model->hasErrors())
                        $getErrors = $model->getErrors();
                    return [
                        'success'   => false,
                        'status'    => 'has-error',
                        'data'      => $getErrors
                    ];
                }
            }

            return [
                'success'   => false,
                'status'    => 'save-failed'
            ];
        }
    }

    function actionUpdateMemberAddress(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
            $model = \app\models\MemberAddress::find()->where(['id' => $_POST['address_id']])->one(); 
            if(isset($model) && $model != null){
                $label = $_POST['label'];
                $value = $_POST['value'];
                $model->$label = $value;

                $model->validate([$label]);
                if($model->hasErrors()){
                    $getErrors = $model->getErrors();

                    return [
                        'success'   => false,
                        'status'    => 'has-error',
                        'data'      => $getErrors
                    ];

                }
                else{
                   $model->save();
                   $getAddress = \app\models\MemberAddress::find()
                        ->where(['id' => $model->id])
                        ->asArray()->one();

                    return [
                        'success'   => true,
                        'status'    => 'okay',
                        'data'      => $getAddress
                    ];
                }
            }
            return [
                'success'   => false,
                'status'    => 'save-failed'
            ];
        }
    }

    function actionDeleteMemberAddress(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
            $model = \app\models\MemberAddress::find()->where(['id' => $_POST['address_id']])->one(); 
            $id = $model->id;
            if($model->delete()){
                return [
                    'success'   => true,
                    'status'    => 'okay',
                    'data'      => $id
                ];
            }
            return [
                'success'   => false,
                'status'    => 'delete-failed'
            ];
        }
    }


    public function actionProfileImageUpdate(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (isset($_POST)) {
            $model = \app\models\Member::findOne($_POST['member_id']); 
            $image = \yii\web\UploadedFile::getInstanceByName('imagefile');
            
            if (isset($image)){
                $model->image_path = 'http://dilgempc.local/images/members/' . $model->id . '.jpg';
                $image->saveAs('images/members/'. $model->id . '.jpg');
                $imagepath = $model->image_path;
                $model->save(false);
            }            

            return[
                'success'   => true,
            ];

        }
    }

}
