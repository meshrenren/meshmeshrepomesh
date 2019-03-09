<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\SavingsAccount;
use kartik\mpdf\Pdf; 
use app\helpers\particulars\ParticularHelper;

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

        $accountList = $savings->getAccountList('');
        
        return $this->render('index', [
            'accountList'       => $accountList,
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

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();

        	$accountDetails = $post['account'];
        	$accountDetails = (array)$accountDetails;

        	$hasAccount = null;
        	$product = \app\models\SavingsProduct::find()->where(['id' => $accountDetails['saving_product_id']])->one();

        	if($product->is_multiple == 0 && $accountDetails['type'] == "Member")
        		$hasAccount = \app\models\SavingsAccount::find()->where(['member_id' => $accountDetails['member_id'], 'saving_product_id' => $accountDetails['saving_product_id']])->one();

    		if($hasAccount == null){
    			//$member = \app\models\Member::find()->where(['id' => $accountDetails['member_id']])->one();
	        	$account = new \app\models\SavingsAccount;
	        	$trans_serial = $product->trans_serial + 1;
	        	$trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);
	        	$account->account_no = $product->id . "-" . $trans_serial_pad;
	        	$account->member_id = $accountDetails['member_id'];
	        	$account->saving_product_id = $accountDetails['saving_product_id'];
	        	$account->balance = 0;
	        	$account->date_created = date('Y-m-d H:i:s');
	        	$account->transacted_date = date('Y-m-d H:i:s');
                $account->type = $accountDetails['type'];
                if($accountDetails['type'] == "Group"){
                    $account->account_name = $accountDetails['account_name'];
                    $account->member_id = 0;
                }
	        	$account->is_active = 1;
	        	if($account->save()){
	        		$product->trans_serial = $trans_serial;
	        		$product->save();
	        		$getAccount = \app\models\SavingsAccount::find()->where(['account_no' => $account->account_no])->one();

                    if($account->type == "Group" && isset($post['signatoryList'])){
                        $signatories = $post['signatoryList'];
                        foreach ($signatories as $sign) {
                            $newSign = new \app\models\SaGroupSignatory;
                            $newSign->savings_account = $account->account_no;
                            $newSign->member_id = $sign['id'];
                            $newSign->save();
                        }
                    }

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
    	$helper = ParticularHelper::processBeginning();
    	
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
                $running_balance = $getSavingsAccount->balance + $transaction['amount'];
            }

        	$model->transaction_date = date('Y-m-d H:i:s');
	        $model->transacted_by = \Yii::$app->user->identity->id;
	        $model->running_balance = $running_balance;
			
	        if($running_balance>=0)
	        {
	        	if($model->save()){
	        		$getSavingsAccount->balance = $running_balance;
	        		$getSavingsAccount->save();
	        		return [
	        				'success'	=> true,
	        				'data'      => $model->id
	        		];
	        	}
	        	else{
	        		//var_dump($model->getErrors());
	        		return [
	        				'success'	=> false
	        		];
	        	}
	        	
	        }
	        
	        else
	        {
	        	return [
	        			'success'	=> false,
	        			'data' => 'savings cannot be negative',
	        	];
	        }

        	
        }
    }

    public function actionPrintPdf($type, $id){

        $model = \app\models\SavingsTransaction::find()->where(['savings_transaction.id' => $id])->joinWith(['account.member'])->one();
        $account_no = $model->account->account_no;
        $account_name = $model->account->member->fullname;
        $trans_amount = number_format($model->amount, 2) ;
        $trans_date = date('M d, Y', strtotime($model->transaction_date)) ;
        $trans_remarks = $model->remarks;
        $trans_type = $model->transaction_type;
        //$account_balance = $model->account->account_balance;

        $template = Yii::$app->params['formTemplate']['savings_deposit'];

        $template = str_replace('[account_type]', "Savings Account", $template);
        $template = str_replace('[account_number]', $account_no, $template);
        $template = str_replace('[transaction_amount]', $trans_amount , $template);
        $template = str_replace('[account_name]', $account_name, $template);
        $template = str_replace('[transaction_date]', $trans_date, $template);
        $template = str_replace('[transaction_remarks]', $trans_remarks, $template);
        $template = str_replace('[transaction_type]', $trans_type, $template);

        $topdf = Yii::$app->view->topdf($template, 'Savings', 'deposit.pdf');

        //$topdf->getApi()->SetJS('this.print();');
        // return the pdf output as per the destination setting
        return $topdf->render();
    }

    public function actionPrintWithdraw($account_no){
        $model = \app\models\SavingsAccount::find()->where(['savingsaccount.account_no' => $account_no])->joinWith(['member'])->one();
        if($model){
            $transaction = \app\models\SavingsAccount::find()->where(['account_no' => $model->account_no])->one();
            $account_no = $model->account_no;
            $account_name = $model->member->fullname;
            $last_transaction = "";
            if($model->lastTransaction)
                $last_transaction = date('M d, Y', strtotime($model->lastTransaction->transaction_date));
            $balance = number_format($model->balance, 2, '.', ',');

            $settings  = new \app\models\DefaultSettings;
            $penalty = number_format($settings->getValue('savings_penalty'), 2, '.', ',');;
            //$account_balance = $model->account->account_balance;

            $template = Yii::$app->params['formTemplate']['savings_withdraw'];

            $template = str_replace('[account_name]', $account_name, $template);
            $template = str_replace('[account_number]', $account_no, $template);
            $template = str_replace('[last_transaction]', $last_transaction , $template);
            $template = str_replace('[balance]', $balance, $template);
            $template = str_replace('[penalty]', $penalty, $template);

            $pdf = new Pdf([
                // set to use core fonts only
                // 'mode' => Pdf::MODE_CORE, 
                'mode' => Pdf::MODE_BLANK, 
                // A4 paper format
                'format' => Pdf::FORMAT_A4, 
                // portrait orientation
                'orientation' => Pdf::ORIENT_PORTRAIT, 
                // stream to browser inline
                'destination' => Pdf::DEST_BROWSER, 
                // your html content input
                'content' => $template,  
                // format content from your own css file if needed or use the
                // enhanced bootstrap css built by Krajee for mPDF formatting 
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
                // any css to be embedded if required
                'cssInline' => '.kv-heading-1{font-size:10px}', 
                'defaultFontSize' => '10',
                 // set mPDF properties on the fly
                'options' => '',
                 // call mPDF methods on the fly
                'filename' => 'withdral.pdf'
            ]);

            //return $pdf;

            //$topdf = Yii::$app->view->topdf($template, 'Savings', 'withdral.pdf');

            //$topdf->getApi()->SetJS('this.print();');
            // return the pdf output as per the destination setting
            return $pdf->render();
        }
        
    }

    public function actionTestLayout(){
        return $this->render('testlayout');
    }

}
