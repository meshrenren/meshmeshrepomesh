<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\SavingsAccount;

class SavingsController extends \yii\web\Controller
{
    public function actionIndex()
    {
    	$this->layout = 'main-vue';

    	$transaction = new \app\models\SavingsTransaction;
    	$savingsTransaction = $transaction->attributes();
    	
        return $this->render('index', [ 'savingsTransaction' => $savingsTransaction]);
    }

    public function actionCreate()
    {
    	$this->layout = 'main-vue';

    	$savings = new \app\models\SavingsAccount;
    	$savingsAccount = $savings->attributes();

    	$savingsProduct  = \app\models\SavingsProduct::find()
    		->where(['is_active' => 1])
    		->select(['id as value', 'description as label'])
    		->asArray()->all();
    	
        return $this->render('create', [
        	'savingsProduct'	=> $savingsProduct,
        	'savingsAccount'	=> $savingsAccount
        ]);
    }

    public function actionCreateAccount()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$accountDetails = json_decode($_POST['account']);
        	$accountDetails = (array)$accountDetails;

    		$hasAccount = \app\models\SavingsAccount::find()->where(['member_id' => $accountDetails['member_id'], 'saving_product_id' => $accountDetails['saving_product_id']])->one();

    		if($hasAccount == null){
    			$member = \app\models\Member::find()->where(['id' => $accountDetails['member_id']])->one();
    			$product = \app\models\SavingsProduct::find()->where(['id' => $accountDetails['saving_product_id']])->one();
	        	$account = new \app\models\SavingsAccount;
	        	$account->account_no = $product->id . "-" . $member->id;
	        	$account->member_id = $member->id;
	        	$account->saving_product_id = $accountDetails['saving_product_id'];
	        	$account->balance = 0;
	        	$account->date_created = date('Y-m-d H:i:s');
	        	$account->transacted_date = date('Y-m-d H:i:s');
	        	$account->is_active = 1;
	        	if($account->save()){
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

        	$getTransactionType = \app\models\TransactionType::find()->where(['transaction_code' => $transaction['transaction_type']])->one();
        	$getSavingsAccount = \app\models\SavingsAccount::findOne($transaction['fk_savings_id']);
        	$running_balance = $getSavingsAccount->balance;
        	if($getTransactionType->credit_entry == 1){
        		$running_balance = $running_balance + $transaction['amount'];
        	}
        	else if($getTransactionType->debit_entry == 1){        		
        		$running_balance = $running_balance - $transaction['amount'];
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
	        	var_dump($model->getErrors());
	        	return [
	        		'success'	=> false
	        	];
	        }

        	
        }
    }

}
