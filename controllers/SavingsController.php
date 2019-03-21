<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\SavingsAccount;
use \app\models\JournalHeader;
use \app\models\JournalDetails;
use kartik\mpdf\Pdf; 
use \Mpdf\Mpdf;

use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\journal\JournalHelper;


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
        $savingsAccount = $savings->getAttributes();

        $savingsProduct  = \app\models\SavingsProduct::find()
            ->where(['is_active' => 1])
            ->select(['id as value', 'description as label'])
            ->asArray()->all();

        $accountList = SavingsHelper::getAccountSavingsInfo();
        
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
        $savingsTransaction = $transaction->getAttributes();
        
        return $this->render('deposit', [ 'savingsTransaction' => $savingsTransaction]);
    }

    public function actionWithdraw()
    {
        $this->layout = 'main-vue';

        $transaction = new \app\models\SavingsTransaction;
        $savingsTransaction = $transaction->getAttributes();
        
        return $this->render('withdraw', [ 
            'savingsTransaction' => $savingsTransaction
        ]);
    }

    public function actionCreateAccount()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = false;
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();

            	$accountDetails = $post['account'];

            	$hasAccount = null;
            	$product = \app\models\SavingsProduct::find()->where(['id' => $accountDetails['saving_product_id']])->one();

            	if($product->is_multiple == 0 && $accountDetails['type'] == "Member")
            		$hasAccount = \app\models\SavingsAccount::find()->where(['member_id' => $accountDetails['member_id'], 'saving_product_id' => $accountDetails['saving_product_id']])->one();

        		if($hasAccount == null){
        			//$member = \app\models\Member::find()->where(['id' => $accountDetails['member_id']])->one();
    	        	$account = new SavingsAccount;
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
                        $success = true;

    	        		$product->trans_serial = $trans_serial;
                        if(!$product->save()){
                            $success = false;
                        }

                        if($success){
                            if($account->type == "Group" && isset($post['signatoryList'])){
                                $signatories = $post['signatoryList'];
                                foreach ($signatories as $sign) {
                                    $newSign = new \app\models\SaGroupSignatory;
                                    $newSign->savings_account = $account->account_no;
                                    $newSign->member_id = $sign['id'];
                                    if(!$newSign->save()){
                                        $success = false;
                                    }
                                }
                            }
                        }
                        
    	        	}
    	        	else{
                        $success = false;
    	        		$errorList = $account->getErrors();
    	        	}
        		}
        		else{
                    $success = false;
                    $error = 'HAS_ACCOUNT';
        		}

                if($success){
                    $transaction->commit();
                }
                else{
                    $transaction->rollBack();
                }
                
        	} catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return [
                'success'   => $success,
                'error'     => $error,
                'data'      => $data
            ];

        	
        }
    }

    public function actionGetAccount(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){
        	$model = new \app\models\SavingsAccount;
        	
        	$accountList = $model->getAccountList();
        	return $accountList;
        }
    }

    public function actionGetTransaction(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if(\Yii::$app->getRequest()->getBodyParams()){
            
            $post = \Yii::$app->getRequest()->getBodyParams();
            $fk_savings_id = $post['fk_savings_id'];
            
            $accountList = SavingsHelper::getTransaction($fk_savings_id);
            return $accountList;
        }
    }


    public function actionSaveTransaction(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){
            $success = false;
            $error = '';
            $errorMessage = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();
            	$acct_transaction = $post['accountTransaction'];
                $product = $post['product'];
                $product_particularid = $product['particular_id'];

                //Check Reference Number if exist
                $reference_no = $acct_transaction['reference_number'];
                $getJH = JournalHeader::find()->where(['reference_no' => $reference_no])->one();
                if($getJH){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASRN',
                        'errorMessage' => 'Error processing the transaction. Please try again'
                    ];
                }


            	$getSavingsAccount = SavingsAccount::findOne($acct_transaction['fk_savings_id']);

                $trans_type = "";
                $coh_entrytype = ""; // entry_type for Cash On Hand
                $acct_entrytype = ""; // entry_type for the Account
                if($acct_transaction['transaction_type'] == 'WITHDRWL'){
                    //Withdrawal Transaction
                    $running_balance = $getSavingsAccount->balance - $acct_transaction['amount'];
                    $trans_type = "GeneralVoucher";
                    $coh_entrytype = "CREDIT"; 
                    $acct_entrytype = "DEBIT";
                }
                else{
                    //Deposit Transaction
                    $running_balance = $getSavingsAccount->balance + $acct_transaction['amount'];
                    $trans_type = "Payment";
                    $coh_entrytype = "DEBIT";
                    $acct_entrytype = "CREDIT";
                }
    	        $acct_transaction['running_balance'] = $running_balance;
    			
    	        if($running_balance >= 0)
    	        {
                    $saveSD = SavingsHelper::saveSavingsTransaction($acct_transaction);
    	        	if($saveSD){
    	        		$getSavingsAccount->balance = $running_balance;
    	        		$getSavingsAccount->save();

                        $success = true;
                        $data = $saveSD->id;

    	        	}
    	        	else{
    	        		//var_dump($model->getErrors());
    	        		$success = false;
                        $error = "SD_ERROR";
                        $errorMessage = 'Error processing the transaction. Please try again';
                        $transaction->rollBack();
    	        	}

                    //Save to Journal
                    if($success && $saveSD){
                        $journalHeader = new JournalHeader;
                        $journalHeaderData = $journalHeader->getAttributes();
                        $journalHeaderData['reference_no'] = $saveSD->reference_number;
                        $journalHeaderData['posting_date'] = $saveSD->transaction_date;
                        $journalHeaderData['total_amount'] = $saveSD->amount;
                        $journalHeaderData['trans_type'] = $trans_type;
                        $journalHeaderData['remarks'] = $saveSD->remarks;

                        $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                        if($saveJournal){
                            //Entries

                            $journalList = new JournalDetails;
                            $journalListAttr = $journalList->getAttributes();
                            $lists = array();

                            $coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);//Cash on Hand particular id
                            // Account
                            $arr = $journalListAttr;
                            $arr['amount'] = $saveSD->amount;
                            $arr['particular_id'] = $product['particular_id'];
                            $arr['entry_type'] = $acct_entrytype;
                            array_push($lists, $arr);

                            // Cash On Hand                            
                            $arr = $journalListAttr;
                            $arr['amount'] = $saveSD->amount;
                            $arr['particular_id'] = $coh_id->id;
                            $arr['entry_type'] = $coh_entrytype;
                            array_push($lists, $arr);


                            $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->reference_no);
                            if($insertSuccess){                                
                                $success = true;
                            }
                            else{
                                $success = false;
                                $error = "SD_ERROR";
                                $errorMessage = 'Error processing the transaction in Journal List. Please try again';
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $error = "SD_ERROR";
                            $errorMessage = 'Error processing the transaction in Journal Header. Please try again';
                            $transaction->rollBack();
                        }
                    }
    	        	
    	        }
    	        else
    	        {
                    $success = false;
                    $error = "SD_NEGATIVE";
                    $errorMessage = 'Savings Deposit cannot be negative';
                    $transaction->rollBack();
    	        }

                if($success){
                    $transaction->commit();
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return [
                'success'       => $success,
                'error'         => $error,
                'errorMessage'  => $errorMessage,
                'data'          => $data,
            ];

        	
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

    public function actionPrintWithdraw(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            $account_no = $postData['account_no'];
            $type = $postData['type'];
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
                //$penalty = number_format($settings->getValue('savings_penalty'), 2, '.', ',');;
                //$account_balance = $model->account->account_balance;

                $template = Yii::$app->params['formTemplate']['savings_withdraw'];

                $template = str_replace('[account_name]', $account_name, $template);
                $template = str_replace('[account_number]', $account_no, $template);
                $template = str_replace('[last_transaction]', $last_transaction , $template);
                $template = str_replace('[balance]', $balance, $template);
                $template = str_replace('[penalty]', "", $template);


                if($type == "pdf"){
                    // Set up MPDF configuration
                    $config = [
                        'mode' => '+utf-8', 
                        "allowCJKoverflow" => true, 
                        "autoScriptToLang" => true,
                        "allow_charset_conversion" => false,
                        "autoLangToFont" => true,
                    ];
                    $mpdf = new Mpdf($config);
                    $mpdf->WriteHTML($template);

                    // Download the PDF file
                    $mpdf->Output();
                    exit();
                }
                else{
                    return [ 'data' => $template];
                }
                
            }
        }
        
    }

    public function actionTestLayout(){
        return $this->render('testlayout');
    }

}
