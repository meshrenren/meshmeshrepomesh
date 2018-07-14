<?php

namespace app\controllers;

class TimeDepositController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate()
    {
    	$this->layout = 'main-vue';

    	$account = new \app\models\TimeDepositAccount;
    	$tdAccount = $account->attributes();

    	$transaction = new \app\models\TimeDepositTransaction;
    	$tdTransaction = $transaction->attributes();

    	$tdProduct  = \app\models\TimeDepositProduct::find()
    		->joinWith(['ratetable'])
    		->asArray()->all();
    	
        return $this->render('create', [
        	'tdProduct'		=> $tdProduct,
        	'tdAccount'		=> $tdAccount,
        	'tdTransaction'	=> $tdTransaction
        ]);
    }

    public function actionSaveTdAccount(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$today = date("Y-m-d");
        	$tdaccount = json_decode($_POST['accountDetails']);
        	$tdaccount = (array)$tdaccount;
        	$model = new \app\models\TimeDepositAccount;
        	$model->attributes = $tdaccount;

        	$product = \app\models\TimeDepositProduct::find()->where(['id' => $tdaccount['fk_td_product']])->one();

        	$trans_serial = $product->trans_serial + 1;
        	$trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);
        	$model->accountnumber = $product->id . "-" . $trans_serial_pad;

        	$mature_days = date('Y-m-d', strtotime($today. ' + '. $tdaccount['term'] . ' days'));

        	$model->maturity_date = $mature_days;
        	$model->date_created = date('Y-m-d H:i:s');
	        $model->created_by = \Yii::$app->user->identity->id;
	        $model->balance = $tdaccount['amount'];
	        //var_dump($model->attributes);
	        if($model->save()){
                $product->trans_serial = $trans_serial;
                $product->save();
                //var_dampa($model is model duhhh!!)
        		$tdTransaction = new \app\models\TimeDepositTransaction;
        		$tdTransaction->fk_account_number = $model->accountnumber;
        		$tdTransaction->transaction_type = 'TDCASHDEP';
        		$tdTransaction->amount = $model->amount;
        		$tdTransaction->balance = $model->amount;
        		$tdTransaction->transaction_date = date('Y-m-d H:i:s');
        		$tdTransaction->transacted_by = \Yii::$app->user->identity->id;
        		$tdTransaction->save();

        		//var_dump($tdTransaction->getErrors());
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

    public function actionGetTdAccounts(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$member_id = $_POST['member_id'];
        	$model = new \app\models\TimeDepositAccount;
    	
	    	$accountList = $model->getAccountListByMemberID($member_id);
	    	return $accountList;
        	
        }
    }

    public function actionGetMaturedAccounts(){
        $date = date("Y-m-d");
        $model = \app\models\TimeDepositAccount::find()->where(['maturity_date' => $date])->all();
        foreach ($model as $timedeposit) {
            $totalInterest = $timedeposit->getMatureDays($timedeposit->maturity_date, $timedeposit->amount, $timedeposit->interest_rate, $timedeposit->term);
            $totalAmount = $timedeposit->amount + $totalInterest;
        }
    }

}
