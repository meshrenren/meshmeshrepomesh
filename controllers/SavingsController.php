<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\SavingsAccount;

class SavingsController extends \yii\web\Controller
{


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'deposit', 'withdraw'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_savings_account_","_add") ){
                                    return true;
                            }
                        }
                    ],
                    [
                        'actions' => ['deposit', 'withdraw'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_savings_account_","_edit") ){
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

    public function actionIndex()
    {
        
        $this->layout = 'main-vue';

        $savings = new \app\models\SavingsAccount;
        $savingsAccount = $savings->attributes();

        $savingsProduct  = \app\models\SavingsProduct::find()
            ->where(['is_active' => 1])
            ->select(['id as value', 'description as label'])
            ->asArray()->all();
        
        return $this->render('index', [
            'savingsProduct'    => $savingsProduct,
            'savingsAccount'    => $savingsAccount
        ]);
    }

    public function actionDeposit()
    {
        $this->layout = 'main-vue';

        $transaction = new \app\models\SavingsTransaction;
        $savingsTransaction = $transaction->attributes();
        
        return $this->render('deposit', [ 'savingsTransaction' => $savingsTransaction]);
    }

    public function actionWithdraw()
    {
        $this->layout = 'main-vue';

        $transaction = new \app\models\SavingsTransaction;
        $savingsTransaction = $transaction->attributes();
        
        return $this->render('withdraw', [ 'savingsTransaction' => $savingsTransaction]);
    }

    public function actionCreateAccount()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$accountDetails = json_decode($_POST['account']);
        	$accountDetails = (array)$accountDetails;

        	$hasAccount = null;
        	$product = \app\models\SavingsProduct::find()->where(['id' => $accountDetails['saving_product_id']])->one();

        	if($product->is_multiple == 0)
        		$hasAccount = \app\models\SavingsAccount::find()->where(['member_id' => $accountDetails['member_id'], 'saving_product_id' => $accountDetails['saving_product_id']])->one();

    		if($hasAccount == null){
    			$member = \app\models\Member::find()->where(['id' => $accountDetails['member_id']])->one();
	        	$account = new \app\models\SavingsAccount;
	        	$trans_serial = $product->trans_serial + 1;
	        	$trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);
	        	$account->account_no = $product->id . "-" . $trans_serial_pad;
	        	$account->member_id = $member->id;
	        	$account->saving_product_id = $accountDetails['saving_product_id'];
	        	$account->balance = 0;
	        	$account->date_created = date('Y-m-d H:i:s');
	        	$account->transacted_date = date('Y-m-d H:i:s');
	        	$account->is_active = 1;
	        	if($account->save()){
	        		$product->trans_serial = $trans_serial;
	        		$product->save();
	        		$getAccount = \app\models\SavingsAccount::find()->where(['account_no' => $account->account_no])->one();

	        		return [
				        'success' 	=> true,
			        	'status'	=> 'okay',
				        'data' 		=> $getAccount
				    ];
	        	}
	        	else{
	        		$error = $account->getErrors();
	        		return [
				        'success' 	=> false,
			        	'status'	=> 'has-error',
				        "error"		=> $error
				    ];
	        	}
    		}
    		else{
    			return [
			        'success' 	=> false,
			        'status'	=> 'has-account'
			    ];
    		}
        	

        	
        }
    }
    
    
    public function actionBeginningofday()
    {
    	$model = new SavingsAccount();
    	
    	return $model->calculateSavingsInterest();
    }

    public function actionGetAccount(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$model = new \app\models\SavingsAccount;
    	
    	$accountList = $model->getAccountList($_POST['nameInput']);
    	return $accountList;
    }

    public function actionGetTransaction(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$model = \app\models\SavingsTransaction::find()->where(['fk_savings_id' => $_POST['account_no']])->asArray()->all();
    	
    	return $model;
    }

    public function actionSaveTransaction(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$transaction = json_decode($_POST['accountTransaction']);
        	$transaction = (array)$transaction;
        	$model = new \app\models\SavingsTransaction;
        	$model->attributes = $transaction;

        	$getSavingsAccount = \app\models\SavingsAccount::findOne($transaction['fk_savings_id']);
            if($transaction['transaction_type'] == 'WITHDRWL'){
                $running_balance = $getSavingsAccount->balance - $transaction['amount'];
            }
            else{
                $running_balance = $getSavingsAccount->balance - $transaction['amount'];
            }

        	$model->transaction_date = date('Y-m-d H:i:s');
	        $model->transacted_by = \Yii::$app->user->identity->id;
	        $model->running_balance = $running_balance;

	        if($model->save()){
	        	$getSavingsAccount->balance = $running_balance;
	        	$getSavingsAccount->save();
	        	return [
	        		'success'	=> true
	        	];
	        }
	        else{
	        	//var_dump($model->getErrors());
	        	return [
	        		'success'	=> false
	        	];
	        }

        	
        }
    }

}
