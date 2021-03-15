<?php

namespace app\controllers;

use Yii;
use \Mpdf\Mpdf;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf; 
use app\models\TimeDepositAccount;
use app\models\TimeDepositRateTable;

use app\helpers\accounts\PaymentHelper;
use app\helpers\accounts\TimeDepositHelper;
use app\helpers\accounts\AccountHelper;
use app\helpers\voucher\VoucherHelper;
use app\helpers\journal\JournalHelper;
use app\models\JournalHeader;
use app\models\PaymentRecord;
use app\models\JournalDetails;

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

        $tdlist = \app\models\TimeDepositAccount::find()->joinWith(['member'])
            ->asArray()->all();
        
        $tdRates = TimeDepositRateTable::find()->asArray()->all();

    	$tdProduct  = \app\models\TimeDepositProduct::find()
    		->joinWith(['ratetable'])
    		->asArray()->all();
    	
        return $this->render('create', [
            'tdlist'        => $tdlist,
        	'tdProduct'		=> $tdProduct,
        	'tdAccount'		=> $tdAccount,
        	'tdTransaction'	=> $tdTransaction,
        	'tdRates'		=> $tdRates
        ]);
    }
    
    public function actionShowtd()
    {
    	$model = new TimeDepositAccount();
    	//$model->checkMaturity();
    }

    public function actionSaveTdAccount(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = false;
            $error = '';
            $errorMessage = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();

                $today = Yii::$app->user->identity->DateNow;
                $todayDateTime = Yii::$app->user->identity->DateTimeNow;

                $tdaccount = $post['accountDetails'];
                $tdaccount = (array)$tdaccount;
                //Check Reference Number if exist
                $ref_no = $tdaccount['or_number'];
                $getJH = JournalHeader::find()->where(['reference_no' => $ref_no])->one();
                if($getJH){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASRN',
                        'errorMessage' => 'Reference number exist. Please try again'
                    ];
                }
                //Create new TD account
                $model = $this->createNewAccount($tdaccount, $ref_no);

    	        if($model->save()){
                    $success = true;

                    if($model->type == "Group" && isset($post['signatoryList'])){
                        $signatories = $post['signatoryList'];
                        foreach ($signatories as $sign) {
                            $newSign = new \app\models\TdGroupSignatory;
                            $newSign->td_account = $model->accountnumber;
                            $newSign->member_id = $sign['id'];
                            $newSign->save();
                        }
                    }

                    $member = \app\models\Member::find()->where(['id' => $model->member_id])->one();
                    $name = $model->account_name;
                    $type = "Group";
                    $member_id = null;
                    if($member){
                        $name =  $member->first_name . " " . $member->middle_name . " " . $member->last_name;
                        $type = "Individual";
                        $member_id = $member->id;
                    }

                    //Save in Payment
                    $payment = new PaymentRecord();
                    $paymentData = $payment->getAttributes();
                    $paymentData['date_transact'] = \Yii::$app->user->identity->DateTimeNow;
                    $paymentData['or_num'] = $ref_no;
                    $paymentData['name'] = $name;
                    $paymentData['type'] = 'Individual';
                    $paymentData['amount_paid'] = $model->amount;
                    $paymentData['posted_date'] = \Yii::$app->user->identity->DateTimeNow;

                    $paymentModel = \app\helpers\payment\PaymentHelper::savePayment($paymentData);
                    if($paymentModel){

                        $entries = array();
                        $arr = [
                            'type'          => 'TIME_DEPOSIT', // Time Deposit
                            'amount'        => $model->amount,
                            'member_id'     => $member_id,
                            'particular_id' => 20,
                        	'product_id'    => $tdaccount['fk_td_product'], 
                        	'account_no'    => $model->accountnumber,
                            'or_num'        => $ref_no,
                            'posted_date'   => \Yii::$app->user->identity->DateTimeNow
                        ];
                        array_push($entries, $arr);     
                        $insertSuccess = \app\helpers\payment\PaymentHelper::insertAccount($entries, $paymentModel->id, \Yii::$app->user->identity->DateTimeNow);
                        if(!$insertSuccess){
                            $success = false;
                            $error = "PAYMENT_ERROR";
                            $errorMessage = 'Error processing the transaction in saving payment data. Please try again or contact technical support.';
                            $transaction->rollBack();
                        }     
                    }
                    else{
                        $success = false;
                        $error = "PAYMENT_ERROR";
                        $errorMessage = 'Error processing the transaction in saving payment data. Please try again or contact technical support.';
                        $transaction->rollBack();
                    }

                    //Check if success, return false if not
                    if(!$success){
                        return [
                            'success'       => $success,
                            'error'         => $error,
                            'errorMessage'  => $errorMessage
                        ];
                    }
                    
                    $service_amount = isset($tdaccount['service_amount']) ? $tdaccount['service_amount'] : 0;
                    //Save in Journal
                    $journalHeader = new JournalHeader;
                    $journalHeaderData = $journalHeader->getAttributes();
                    $journalHeaderData['reference_no'] = $tdaccount['or_number'];
                    $journalHeaderData['posting_date'] = $today;
                    $journalHeaderData['total_amount'] = $tdaccount['amount'] + $service_amount;
                    $journalHeaderData['trans_type'] = 'Payment';
                    $journalHeaderData['remarks'] = 'TD Application';

                    $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                    if($saveJournal){
                        //Entries
                        $journalList = new JournalDetails();
                        $journalListAttr = $journalList->getAttributes();
                        $lists = array();

                        // Account
                        
                        $arr = $journalListAttr;
                        $arr['amount'] = $tdaccount['amount'] + $service_amount;
                        $arr['particular_id'] = 99; //--> 99 is cash on hand
                        $arr['entry_type'] = 'DEBIT';
                        array_push($lists, $arr);
                        
                        
                        $arr = $journalListAttr;
                        $arr['amount'] = $tdaccount['amount'];
                        $arr['particular_id'] = 20; //--> time deposit
                        $arr['entry_type'] = 'CREDIT';
                        array_push($lists, $arr);
                        
                        if($service_amount > 0){
                            $arr = $journalListAttr;
                            $arr['amount'] = $tdaccount['service_amount'];
                            $arr['particular_id'] = 7; //--> service fee
                            $arr['entry_type'] = 'CREDIT';
                            array_push($lists, $arr);
                        }

                        $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->reference_no);
                        if($insertSuccess){                                
                            $success = true;
                        }
                        else{
                            $success = false;
                            $error = "SD_ERROR";
                            $errorMessage = 'Error processing the transaction in Journal List. Please try again or contact technical support.';
                            $transaction->rollBack();
                        }
                    }
                    else{
                        $success = false;
                        $error = "SD_ERROR";
                        $errorMessage = 'Error processing the transaction in Journal Header. Please try again or contact technical support.';
                        $transaction->rollBack();

                    }

                    //Check if success, return false if not
                    if(!$success){
                        return [
                            'success'       => $success,
                            'error'         => $error,
                            'errorMessage'  => $errorMessage
                        ];
                    }
            
    	        }
    	        else{
                    $success = false;
                    $error = "TD_ERROR";
                    $errorMessage = 'Error processing the account. Please try again or contact technical support.';
                    $transaction->rollBack();

    	        	return [
                        'success'       => $success,
                        'error'         => $error,
                        'errorMessage'  => $errorMessage
                    ];
    	        }

                if($success){
                    $transaction->commit();
                    return [
                        'success'       => $success,
                        'error'         => $error,
                        'errorMessage'  => $errorMessage,
                        'data'          => $data,
                    ];
                }
                else{
                    $transaction->rollBack();
                    return [
                        'success'       => $success,
                        'error'         => $error,
                        'errorMessage'  => $errorMessage,
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
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

    public function actionList($year = null){
        $this->layout = 'main-vue';
        
        $date = date("Y-m-d");
        $tdlist = \app\models\TimeDepositAccount::find()->joinWith(['member', 'transactions']);
        if($year){
            $tdlist = $tdlist->andWhere("DATE_FORMAT(open_date, '%Y') = '$year' OR DATE_FORMAT(maturity_date, '%Y') = '$year'");
        }
        $tdlist = $tdlist->asArray()->all();
        $pageData = [
            'year' => $year
        ];
        return $this->render('list', [
            'tdAccounts'    => $tdlist,
            'typeList'      => 'td_list',
            'header'        => 'All Accounts',
            'pageData'      => $pageData
        ]);

    }

    public function actionMaturedAccounts(){
        $this->layout = 'main-vue';

        $date = date("Y-m-d");
        $maturedtdlist = \app\models\TimeDepositAccount::find()->joinWith(['member'])
            ->where("maturity_date <= '" .$date . "'")
            ->asArray()->all();
        return $this->render('list', [
            'tdAccounts'    => $maturedtdlist,
            'typeList'      => 'td_matured',
            'header'        => 'Matured Accounts'
        ]);

    }

    public function actionGetMaturedAccounts(){
        $date = date("Y-m-d");
        $model = \app\models\TimeDepositAccount::find()->where(['maturity_date' => $date])->all();
        foreach ($model as $timedeposit) {
            $totalInterest = $timedeposit->getMatureDays($timedeposit->maturity_date, $timedeposit->amount, $timedeposit->interest_rate, $timedeposit->term);
            $totalAmount = $timedeposit->amount + $totalInterest;
        }
    }

    public function actionCertificate($accountnumber){
        $model = \app\models\TimeDepositAccount::find()->where(['accountnumber' => $accountnumber])->joinWith(['member'])->one();
        if($model){
            /*$transaction = \app\models\SavingsAccount::find()->where(['account_no' => $model->account_no])->one();
            $account_no = $model->account_no;
            $account_name = $model->member->fullname;
            $last_transaction = "";
            if($model->lastTransaction)
                $last_transaction = date('M d, Y', strtotime($model->lastTransaction->transaction_date));
            $balance = number_format($model->balance, 2, '.', ',');

            $settings  = new \app\models\DefaultSettings;
            $penalty = number_format($settings->getValue('savings_penalty'), 2, '.', ',');;*/
            //$account_balance = $model->account->account_balance;

            $template = Yii::$app->params['formTemplate']['timedeposit_certificate'];

            /*$template = str_replace('[account_name]', $account_name, $template);
            $template = str_replace('[account_number]', $account_no, $template);
            $template = str_replace('[last_transaction]', $last_transaction , $template);
            $template = str_replace('[balance]', $balance, $template);
            $template = str_replace('[penalty]', $penalty, $template);*/

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

    public function actionSavingsCalculation(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $post = \Yii::$app->getRequest()->getBodyParams();
            $account_id = $post['account_id'];
            $date = isset($post['date']) && $post['date'] ? $post['date'] : null;
        
            $account = \app\models\TimeDepositAccount::find()->where(['accountnumber' => $account_id])->one();
            $calculation = 0;
            if($account){
                $calculation = TimeDepositHelper::getSavingsCalculation($account, $date);
            }

            return [
                'data' => $calculation
            ] ;
            
        }
    }

    /*
    Withdraw or Renew account
    Also has savings transactions for overdue account
    */
    public function actionTdVoucher(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $post = \Yii::$app->getRequest()->getBodyParams();
        }
    }

    /*
    Withdraw or Renew account
    Also has savings transactions for overdue account
    */
    public function actionProcessAccount(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $post = \Yii::$app->getRequest()->getBodyParams();

            $success = true;
            $errorMessage = "";
            $transaction = \Yii::$app->db->beginTransaction();
            try {

                $account_id = $post['account_id'];
                $total_amount = $post['total_amount'];
                $savings_transaction = $post['savings_transaction'];
                $withdraw_amount = $post['withdraw_amount'];
                $renew_amount = $post['renew_amount'];
                $voucher = $post['general_voucher'];
                $gv_num = $voucher['gv_num'];
                $open_date = isset($post['open_date']) ? $post['open_date'] : Yii::$app->user->identity->DateTimeNow;

                $transaction_date = isset($voucher['transaction_date']) ? $voucher['transaction_date'] : \Yii::$app->user->identity->DateTimeNow;

                $checkGV = VoucherHelper::getVoucherByGvNum($gv_num);
                if($checkGV){
                    $success = false;
                    $errorMessage = "GV Number already exist";
                    return [
                        'success' => $success,
                        'errorMessage' => $errorMessage
                    ] ;
                }

                $account = \app\models\TimeDepositAccount::find()->where(['accountnumber' => $account_id])->one();
                $member = \app\models\Member::find()->where(['id' => $account->member_id])->one();
                $balance = $account->balance;

                //Add in transaction data
                if($savings_transaction){
                    $balance = $balance + $savings_transaction['amount'];
                    $trans = new \app\models\TimeDepositTransaction;
                    $trans->fk_account_number = $account->accountnumber;
                    $trans->transaction_type = $savings_transaction['transaction_type'];
                    $trans->amount = $savings_transaction['amount'];
                    $trans->balance = $balance;
                    $trans->ref_no = $gv_num;
                    $trans->remarks = $savings_transaction['remarks'];
                    $trans->transaction_date = $transaction_date;
                    $trans->transacted_by = \Yii::$app->user->identity->id;
                    if($trans->save()){
                        $account->balance = $balance;
                        $account->save();
                    }else{
                        $success = false;
                    }
                }

                if($success){
                    $balance = 0;
                    $trans = new \app\models\TimeDepositTransaction;
                    $trans->fk_account_number = $account->accountnumber;
                    $trans->transaction_type = "TDCASHWITHDRWL";
                    $trans->amount = $balance;
                    $trans->balance = 0;
                    $trans->remarks = '';
                    $trans->ref_no = $gv_num;
                    $trans->transaction_date = $transaction_date;
                    $trans->transacted_by = \Yii::$app->user->identity->id;
                    if($trans->save()){
                        $account->balance = $balance;
                        $account->account_status = "CLOSED";
                        $account->save();
                    }else{
                        $success = false;
                    }
                }

                if($success){
                    //If has renew amount
                    //Save in general voucher
                    if($renew_amount && $renew_amount > 0){
                        $getModelAttr = $account->getAttributes();
                        $new_account = (array) $getModelAttr;
                        $new_account['accountnumber'] = null;
                        $new_account['amount'] = $renew_amount;
                        $new_account['term'] = 12; //New policy
                        $getrate = TimeDepositHelper::getInterestRate(12, $renew_amount);
                        $new_account['interest_rate'] = $getrate; 
                        $new_account['open_date'] = $open_date; 
                        $renewAccount = $this->createNewAccount($new_account, $gv_num);
                        if($renewAccount == null){
                            $success = false;
                        }
                    }
                }

                if($success){
                    //Process other account
                    if(isset($post['otherAccToProcess']) && count($post['otherAccToProcess']) > 0){
                        $processOtherAccount = AccountHelper::processOtherAccount($post['otherAccToProcess'], $voucher['gv_num'], $transaction_date);
                        if(!$processOtherAccount['success']){
                            $success = false;
                        }
                    }
                }


                //Save in Genereral Voucher then Journal Entry
                if($success){

                    $name = $account->account_name;
                    $type = "Group";
                    $member_id = null;

                    $checkGV = VoucherHelper::getVoucherByGvNum($gv_num);
                    if($checkGV){
                        $success = false;
                        $errorMessage = "GV Number already exist";
                    }
                    else{

                        $voucherData = array();
                        $voucherData['gv_num'] = $voucher['gv_num'];
                        if($member){
                            $name =  $member->first_name . " " . $member->middle_name . " " . $member->last_name;
                            $type = "Individual";
                            $member_id = $member->id;
                        }
                        $voucherData['name'] = $name;
                        $voucherData['type'] = $type;
                        $voucherData['date_transact'] = isset($voucher['transaction_date']) ? $voucher['transaction_date'] : \Yii::$app->user->identity->DateTimeNow;
                        $voucherData['posted_date'] = \Yii::$app->user->identity->DateTimeNow;

                    
                        $voucherModel = VoucherHelper::saveVoucher($voucherData);
                        if($voucherModel){
                            $entries =  $voucher['voucher_entries'];
                            foreach ($entries as  $key => $ent) {
                                $entries[$key]['member_id'] = $member_id;
                                $entries[$key]['posted_date'] = \Yii::$app->user->identity->DateTimeNow;
                            }
                            //Ren: 08-24-2020, Note: Add posted date
                            $saveEntries = VoucherHelper::insertEntries($entries, $voucherModel->id, \Yii::$app->user->identity->DateTimeNow);
                            if(!$saveEntries){
                                $success = false;
                            }
                        }else{
                            $success = false;
                        } 
                    }

                    
                }

                //Save in Journal Entry
                if($success){
                    $checkGV = JournalHelper::getVoucherByRefNo($gv_num);
                    if($checkGV){
                        $success = false;
                        $errorMessage = "Reference Number already exist";
                    }
                    else{
                        $journal = array();
                        $journal['reference_no'] = $gv_num;
                        $journal['posting_date'] = \Yii::$app->user->identity->DateNow;
                        $journal['total_amount'] = $total_amount;
                        $journal['trans_type'] = 'GeneralVoucher';
                        $journal['remarks'] = "";
                        $journal['transacted_date'] = \Yii::$app->user->identity->DateTimeNow;

                        $journal = JournalHelper::saveJournalHeader($journal);
                        if($journal){
                            $journalEntry = [];
                            $entries =  $voucher['voucher_entries'];
                            foreach ($entries as  $key => $ent) {
                                if($ent['credit'] && floatval($ent['credit']) > 0){
                                    $arr = [
                                        'amount'        => floatval($ent['credit']),
                                        'entry_type'    => 'CREDIT',
                                        'particular_id' => $ent['particular_id']

                                    ];
                                    array_push($journalEntry, $arr);
                                }

                                if($ent['debit'] && floatval($ent['debit']) > 0){
                                    $arr = [
                                        'amount'        => floatval($ent['debit']),
                                        'entry_type'    => 'DEBIT',
                                        'particular_id' => $ent['particular_id']

                                    ];
                                    array_push($journalEntry, $arr);
                                }
                            }

                            $saveEntries = JournalHelper::insertJournal($journalEntry, $journal->reference_no);
                            if(!$saveEntries){
                                $success = false;
                            }
                        }
                    }
                }
                

                if($success){
                    $transaction->commit();
                }else{
                    $transaction->rollBack();
                }
                return [
                    'success' => $success,
                    'errorMessage' => $errorMessage
                ] ;

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            
            return [
                'success'   => false,
                'errorMessage' => ""
            ];
            
        }
    }

    public function createNewAccount($tdaccount, $gv_num){

        $today = Yii::$app->user->identity->DateNow;
        $todayDateTime = Yii::$app->user->identity->DateTimeNow;

        $model = new \app\models\TimeDepositAccount;
        $model->attributes = $tdaccount;
        $product = \app\models\TimeDepositProduct::find()->where(['id' => $tdaccount['fk_td_product']])->one();

        $openDate = isset($tdaccount['open_date']) ? $tdaccount['open_date'] : $today;

        $trans_serial = $product->trans_serial + 1;
        $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);
        $trans_serial_pad_4 = str_pad($trans_serial, 4, '0', STR_PAD_LEFT);
        $model->accountnumber = $product->id . "-" . $trans_serial_pad;
        $model->account_no = $product->id . "-" . date("Y", strtotime($openDate)) . "-" . $trans_serial_pad_4;


        $mature_days = date('Y-m-d', strtotime($openDate. ' + '. $tdaccount['term'] . ' months'));

        $model->maturity_date = $mature_days;
        $model->open_date = $openDate;
        $model->date_created = date('Y-m-d H:i:s');
        $model->account_status = 'ACTIVE';
        $model->created_by = \Yii::$app->user->identity->id;
        $model->amount = $tdaccount['amount'];
        $model->balance = $tdaccount['amount'];
        $model->type = $tdaccount['type'];
        $model->interest_rate = $tdaccount['interest_rate'];
        if($tdaccount['type'] == "Group"){
            $model->account_name = $tdaccount['account_name'];
            $model->member_id = 0;
        }
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
            $tdTransaction->transaction_date = $todayDateTime;
            $tdTransaction->ref_no = $gv_num;
            $tdTransaction->transacted_by = \Yii::$app->user->identity->id;
            $tdTransaction->save();

            return $model;
        }

        return null;
    }

    public function actionPrintList(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();

            $template = TimeDepositHelper::listingPrint($postData['data']);
            
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
