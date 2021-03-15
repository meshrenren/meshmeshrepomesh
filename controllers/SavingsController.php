<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\SavingAccounts;
use app\models\SavingsTransaction;
use \app\models\JournalHeader;
use \app\models\JournalDetails;
use \app\models\GeneralVoucher;
use \app\models\PaymentRecord;
use kartik\mpdf\Pdf; 
use \Mpdf\Mpdf;

use app\helpers\payment\PaymentHelper;
use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\journal\JournalHelper;
use app\helpers\voucher\VoucherHelper;


class SavingsController extends \yii\web\Controller
{


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'deposit', 'withdraw', 'list'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_savings_account_", "_view") ){
                                return true;
                            }
                        }
                    ],
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

        $savings = new \app\models\SavingAccounts;
        $savingsAccount = $savings->getAttributes();

        $savingsProduct  = \app\models\Savingsproduct::find()
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

    public function actionList()
    {
        $this->layout = 'main-vue';
        
        return $this->render('list');
    }

    public function actionCreateAccount()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();

            $currentDate = ParticularHelper::getCurrentDay();
            $today = date("Y-m-d H:i:s", strtotime($currentDate));

        	$accountDetails = $post['account'];
        	$accountDetails = (array)$accountDetails;

        	$hasAccount = null;
        	$product = \app\models\Savingsproduct::find()->where(['id' => $accountDetails['saving_product_id']])->one();

        	if($product->is_multiple == 0 && $accountDetails['type'] == "Member")
        		$hasAccount = \app\models\SavingAccounts::find()->where(['member_id' => $accountDetails['member_id'], 'saving_product_id' => $accountDetails['saving_product_id']])->one();

    		if($hasAccount == null){
    			//$member = \app\models\Member::find()->where(['id' => $accountDetails['member_id']])->one();
	        	$account = new SavingAccounts;
	        	$trans_serial = $product->trans_serial + 1;
	        	$trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);
	        	$account->account_no = $product->id . "-" . $trans_serial_pad;
	        	$account->member_id = $accountDetails['member_id'];
	        	$account->saving_product_id = $accountDetails['saving_product_id'];
	        	$account->balance = 0;
	        	$account->date_created = date('Y-m-d H:i:s');
	        	$account->transacted_date = $today;
                $account->type = $accountDetails['type'];
                if($accountDetails['type'] == "Group"){
                    $account->account_name = $accountDetails['account_name'];
                    $account->member_id = 0;
                }
	        	$account->is_active = 1;
	        	if($account->save()){
	        		$product->trans_serial = $trans_serial;
	        		$product->save();
	        		$getAccount = SavingAccounts::find()->where(['account_no' => $account->account_no])->one();

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
    	$model = new \app\models\SavingAccounts;
    	if(isset($_POST['nameInput']))
    	   $accountList = $model->getAccountList($_POST['nameInput']);
        else{
            $accountList = $model->getAccountList();
        }
    	return $accountList;
    }

    public function actionGetTransaction(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = \Yii::$app->getRequest()->getBodyParams();
        $fk_savings_id = $post['fk_savings_id'];
        
        $accountList = SavingsHelper::getTransaction($fk_savings_id);
        return $accountList;
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
                $trans_amount = $acct_transaction['amount'];
                $transaction_date = isset($acct_transaction['transaction_date']) ? $acct_transaction['transaction_date'] : \Yii::$app->user->identity->DateTimeNow ;

                //Check Reference Number if exist
                $ref_no = $acct_transaction['ref_no'];
                $getJH = JournalHeader::find()->where(['reference_no' => $ref_no])->one();
                if($getJH){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASRN',
                        'errorMessage' => 'Reference number exist. Please try again'
                    ];
                }

                $coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);//Cash on Hand particular id
            	$getSavingsAccount = SavingAccounts::findOne($acct_transaction['fk_savings_id']);
                $member = \app\models\Member::find()->where(['id' => $getSavingsAccount->member_id])->one();

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
                    //return $saveSD;
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

                    if($success && $saveSD){
                        $name = $getSavingsAccount->account_name;
                        $type = "Group";
                        $member_id = null;
                        if($member){
                            $name =  $member->first_name . " " . $member->middle_name . " " . $member->last_name;
                            $type = "Individual";
                            $member_id = $member->id;
                        }

                        //Save to Voucher if Withdrwal
                        if($acct_transaction['transaction_type'] == 'WITHDRWL'){
                            

                            $voucher = new GeneralVoucher;
                            $voucherData = $voucher->getAttributes();
                            $voucherData['gv_num'] = $ref_no;
                            $voucherData['name'] = $name;
                            $voucherData['type'] = $type;
                            $voucherData['date_transact'] = $transaction_date;
                            $voucherData['posted_date'] = \Yii::$app->user->identity->DateTimeNow;

                        
                            $voucherModel = VoucherHelper::saveVoucher($voucherData);
                            if($voucherModel){
                                $success = true;

                                $entries = array();
                                $arr = [
                                    'member_id'        => $member_id,
                                    'account_no'       => $getSavingsAccount->account_no,
                                    'type'             => "SAVINGS",
                                    'particular_id'    => $product_particularid, // Savings Deposit
                                    'debit'            => $saveSD->amount,
                                    'credit'           => 0,
                                    'posted_date'      => \Yii::$app->user->identity->DateTimeNow
                                ];
                                array_push($entries, $arr);

                                $arr = [
                                    'member_id'        => $member_id,
                                    'account_no'       => null,
                                    'type'             => $coh_id->category,
                                    'particular_id'    => $coh_id->id, //Cash on Hand
                                    'debit'            => 0,
                                    'credit'           => $saveSD->amount,
                                    'posted_date'      => \Yii::$app->user->identity->DateTimeNow
                                ];
                                array_push($entries, $arr);

                                $saveEntries = VoucherHelper::insertEntries($entries, $voucherModel->id, $voucherData['posted_date']);
                                if($saveEntries){
                                    $success = true;
                                }
                                else{
                                    $success = false;
                                    $error = "GV_ERROR";
                                    $errorMessage = 'Error processing the transaction in saving general voucher data. Please try again';
                                    $transaction->rollBack();
                                }
                            }else{
                                $success = false;
                                $error = "GV_ERROR";
                                $errorMessage = 'Error processing the transaction in saving general voucher data. Please try again';
                                $transaction->rollBack();
                            } 
                        }
                        //Save to Payment if Deposit Transaction
                        else{
                            $payment = new PaymentRecord;
                            $paymentData = $payment->getAttributes();
                            $paymentData['date_transact'] = $transaction_date;
                            $paymentData['or_num'] = $ref_no;
                            $paymentData['name'] = $name;
                            $paymentData['type'] = 'Individual';
                            $paymentData['amount_paid'] = $saveSD->amount;
                            $paymentData['posted_date'] = \Yii::$app->user->identity->DateTimeNow;

                            $paymentModel = PaymentHelper::savePayment($paymentData);
                            if($paymentModel){
                                $success = true;

                                $entries = array();
                                $arr = [
                                    'type'          => 'SAVINGS', // Savings Deposit
                                    'amount'        => $saveSD->amount,
                                    'member_id'     => $member_id,
                                    'particular_id' => $product_particularid,
                                    'product_id'    => $getSavingsAccount->saving_product_id, 
                                    'account_no'    => $getSavingsAccount->account_no,
                                    'posted_date'   => \Yii::$app->user->identity->DateTimeNow
                                ];
                                array_push($entries, $arr);     
                                $insertSuccess = PaymentHelper::insertAccount($entries, $paymentModel->id, \Yii::$app->user->identity->DateTimeNow);
                                if($insertSuccess){
                                    $success = true;
                                }  
                                else{
                                    $success = false;
                                    $error = "PAYMENT_ERROR";
                                    $errorMessage = 'Error processing the transaction in saving payment data. Please try again';
                                    $transaction->rollBack();
                                }     
                            }
                            else{
                                $success = false;
                                $error = "PAYMENT_ERROR";
                                $errorMessage = 'Error processing the transaction in saving payment data. Please try again';
                                $transaction->rollBack();
                            }
                        }
                    
                        //Save in Journal
                        $journalHeader = new JournalHeader;
                        $journalHeaderData = $journalHeader->getAttributes();
                        $journalHeaderData['reference_no'] = $saveSD->ref_no;
                        $journalHeaderData['posting_date'] = $saveSD->transaction_date;
                        $journalHeaderData['total_amount'] = $saveSD->amount;
                        $journalHeaderData['trans_type'] = $trans_type;
                        $journalHeaderData['remarks'] = $saveSD->remarks;
                        $journalHeaderData['transacted_date'] = $transaction_date;

                        $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                        if($saveJournal){
                            //Entries

                            $journalList = new JournalDetails;
                            $journalListAttr = $journalList->getAttributes();
                            $lists = array();

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
            $amount = $postData['amount'];
            $model = \app\models\SavingAccounts::find()->where(['savingsaccount.account_no' => $account_no])->joinWith(['member'])->one();
            if($model){
                $transaction = \app\models\SavingAccounts::find()->where(['account_no' => $model->account_no])->one();
                $account_no = $model->account_no;
                $account_name = $model->member ? $model->member->fullname : $model->account_name;
                $last_transaction = "";
                if($model->lastTransaction)
                    $last_transaction = date('M d, Y', strtotime($model->lastTransaction->transaction_date));
                $balance = number_format($model->balance, 2, '.', ',');

                $settings  = new \app\models\DefaultSettings;
                //$penalty = number_format($settings->getValue('savings_penalty'), 2, '.', ',');;
                //$account_balance = $model->account->account_balance;

                $template = Yii::$app->params['formTemplate']['savings_withdraw'];

                $template = str_replace('[account_type]', "Savings Account", $template);
                $template = str_replace('[account_name]', $account_name, $template);
                $template = str_replace('[account_number]', $account_no, $template);
                $template = str_replace('[last_transaction]', $last_transaction , $template);
                $template = str_replace('[balance]', $balance, $template);
                $template = str_replace('[penalty]', "", $template);
                $template = str_replace('[amount]', Yii::$app->view->formatNumber($amount), $template);


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

    //This should be run after the cutoff year. Example this cutoff is for 2020. It should be run on 2021
    public function actionCutOff(){
        $this->layout = 'main-vue';
        $cutOff = Yii::$app->view->getCutOff();
        $cutOffYear =  date('Y', strtotime($cutOff));

        $cutoffDone = false;

        //Check if has cutoff data from last year
        $getcutOff = SavingsTransaction::find()->where('YEAR(transaction_date) = "' . $cutOffYear . '" AND transaction_type = "INTEREST"')->count(); //E.G. Current year 2021. Result is 2020

        $savingsAccount = [];
        if($getcutOff > 0){
            $cutoffDone = true;
        }
        else{
            $savingsAccount = SavingsHelper::calculateCutOffInterest($cutOffYear);
        }

        
        $pageData = [
            'cutOff' => $cutOff,
            'cutOffYear' => $cutOffYear,
        ];

        return $this->render('cut-off', [ 
            'savingsAccount' => $savingsAccount,
            'pageData' => $pageData,
            'cutoffDone' => $cutoffDone
        ]);
    }

    public function actionSaveCutoff()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = \Yii::$app->getRequest()->getBodyParams();
        
        if($post)
        {
            $success = false;
            $error = '';
            $data = null;

            $dateToday = date('Y-m-d H:i:s', strtotime(\Yii::$app->user->identity->DateTimeNow));
            $currentDate = ParticularHelper::getCurrentDay();
            $systemDate = date("Y-m-d", strtotime($currentDate));

            $cutOff = Yii::$app->view->getCutOff();
            $cutOffYear =  date('Y', strtotime($cutOff));

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $success = true;
                $savingsToSave = $post['savingsToSave'];
                $voucherDetails = $post['voucherDetails'];
                $gv_num = $post['gv_num'];
                $transaction_date = $post['transaction_date'];

                //Save savings transaction
                foreach ($savingsToSave as $savings) {
                    $getSavingsAccount = SavingAccounts::findOne($savings['account_no']);

                    $amount = $savings['total_interest'];
                    $running_balance = $getSavingsAccount->balance + $amount;

                    $acct_transaction = [
                        'fk_savings_id' => $savings['account_no'],
                        'amount' => $amount,
                        'transaction_type' => 'INTEREST',
                        'transaction_date' => $transaction_date,
                        'running_balance' => $running_balance,
                        'remarks' => 'Added as interest for year ' . $cutOffYear,
                        'ref_no' => $gv_num,
                    ];

                    $saveSD = SavingsHelper::saveSavingsTransaction($acct_transaction);
                    //return $saveSD;
                    if($saveSD){
                        $getSavingsAccount->balance = $running_balance;
                        $getSavingsAccount->save();

                    }
                    else{
                        $success = false;
                    }
                }

                if($success){
                    //Save in voucher
                    $voucherData = array();
                    $voucherData['gv_num'] = $gv_num;
                    $voucherData['name'] = "DILG XI EMPC";
                    $voucherData['type'] = 'Group';
                    $voucherData['date_transact'] = $transaction_date;
                    $voucherData['posted_date'] = $systemDate;

                
                    $voucherModel = VoucherHelper::saveVoucher($voucherData);
                    if($voucherModel){
                        $entries =  $voucherDetails;
                        foreach ($entries as  $key => $ent) {
                            $entries[$key]['member_id'] = null;
                            $entries[$key]['posted_date'] = $systemDate;
                        }
                        $saveEntries = VoucherHelper::insertEntries($entries, $voucherModel->id, $systemDate);
                        if(!$saveEntries){
                            $success = false;
                        }
                        else{
                            $success = VoucherHelper::saveJournalVoucherBase($voucherModel->id);
                        }
                    }else{
                        $success = false;
                    }
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

    public function actionPrintCutOff(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            $template = SavingsHelper::printCutOff($postData['data']);
            
            $type = $postData['type'];
            if($type == "pdf"){
                // Set up MPDF configuration
                $config = [
                    'mode' => '+utf-8', 
                    "allowCJKoverflow" => true, 
                    "autoScriptToLang" => true,
                    "allow_charset_conversion" => false,
                    "autoLangToFont" => true,
                    'orientation' => 'L'
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
