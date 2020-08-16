<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use \Mpdf\Mpdf;
use app\models\LoanAccount;
use app\models\LoanProduct;

use app\models\LoanTransaction;
use app\models\SavingsAccounts;
use app\models\SavingsTransaction;
use app\models\Savingsproduct;
use app\models\SavingAccounts;


use app\models\Shareaccount;
use app\models\ShareTransaction;
use app\models\ShareProduct;

use app\helpers\payment\PaymentHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\particulars\ParticularHelper;
use app\helpers\voucher\VoucherHelper;


class LoanController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'list'],
                'rules' => [
                    [
                        'actions' => ['index', 'list'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_loan_account_","_view") ){
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

    	$loandProduct  = \app\models\LoanProduct::find()->joinWith(['serviceCharge'])
    		->asArray()->all();
        $default_setting = array();

        $settings  = new \app\models\DefaultSettings;
        $default_setting['loan_redemption_insurance'] = $settings->getValue('loan_redemption_insurance');
        $default_setting['loan_refundable_retention'] = $settings->getValue('loan_redemption_insurance');
    	
        return $this->render('index', [
        	'loandProduct'		   => $loandProduct,
            'default_setting'      => $default_setting,
        ]);
    }
    
    public function actionPendingList()
    {
    	$this->layout = 'main-vue';
    	
    //	$loandetails = LoanAccount::find()->where(['status'=>'Verified'])->asArray()->all();
    	
    	/*
    	 * start
    	 */
    	$connection = Yii::$app->getDb();
    	$command = $connection->createCommand("SELECT la.*, CONCAT(m.last_name,', ',m.first_name,' ',m.middle_name) as fullname, lp.product_name  FROM `loanaccount` la inner join member m on la.member_id = m.id
		inner join loanproduct lp on la.loan_id = lp.id
		where la.status=:muststatus", [':muststatus' => 'Verified']);
    	
    	$loandetails= $command->queryAll();

        $acc = \app\models\LoanAccount::find()->innerJoinWith(['member', 'product'])
            ->where(['status' => 'Verified'])
            ->asArray()->all();

        $filter  = ['category' => ['LOAN', 'OTHERS']];
        $getParticular = ParticularHelper::getParticulars($filter);
        $pageData = ['particulars' => $getParticular];
    	
    	/*
    	 * end
    	 */
    	return $this->render('pending-list', ['pageData' => $pageData, 'ForApprovalLoans'=>$acc]);
    }

    public function actionList()
    {
        $this->layout = 'main-vue';
        
        return $this->render('list');
    }

    public function actionGetTransaction(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $transaction = LoanHelper::getLoanTransaction($post['loan_account']);
            return $transaction;
        }
    }

    /*
    Retrieve all loan account of member base on product
    */
    public function actionGetLoanHistory(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $loan_id = $post['loan_id'];
            $member_id = $post['member_id'];

            $joinWith = ['loanTransaction' => function ($query){
                $query->orderBy('date_posted')
                ->asArray()->all();
            }];
            $loanAccounts = LoanHelper::getMemberLoan($member_id, $loan_id, true, $joinWith, true, 'release_date');
            return $loanAccounts;
        }
    }
    
    
    public function actionGetLoansOfMember()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	if(\Yii::$app->getRequest()->getBodyParams())
    	{
    		try {
    			$connection = Yii::$app->getDb();
    			$command = $connection->createCommand("SELECT release_date, loan_id, principal, principal_balance, term, maturity_date
												FROM `loanaccount` where member_id=:customerid", [':customerid' => '000163']);
    			
    			$result = $command->queryAll();
    			
    			return $result;
    		} catch (Exception $e) {
    			return [
    					'status' => 'error',
    					'errmsg' => $e->getMessage()
    			];
    		}
    		
    	}
   
    }
    
    
    public function actionApproveLoan()
    {
    	
    	
    	
    }

    public function actionGetAccountLoanInfo(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
        	$member_id = $post['member_id'];
            $query = new \yii\db\Query;
            $query->select('loan_id')
                ->from('loanaccount la')
                ->where('member_id = '. $member_id)
                ->groupBy('loan_id');
            $loanAccounts = $query->all();
            $accountList = array();
            if(count($loanAccounts) >= 1){
                foreach ($loanAccounts as $loan) {
                    $acc = \app\models\LoanAccount::find()
                        ->innerJoinWith(['product'])
                        ->where(['member_id' => $member_id, 'loan_id' =>  $loan['loan_id']])
                        ->andWhere('status != "Cancel" AND status != "Verified" ')
                        ->orderBy('release_date DESC')
                        ->asArray()->one();

                     $accArr = array();
                    if($acc){
                        $accArr = $acc;
                        //Getarrear
                        $getArrear = LoanHelper::getArrears($acc['account_no']);
                        $acc['arrears'] = $getArrear['arrearAmount'];

                        //Last Payment
                        $acc['account_last_payment'] = "";
                        $lastPayment = LoanHelper::getLastPayment($acc['account_no']);
                        if($lastPayment){
                            $acc['account_last_payment'] = $lastPayment->date_posted;
                        }
                        //Check LoanHelper -> getAccountLoanInfo for updates
                    }

                    array_push($accountList, $acc);
                }
            }
    	
	    	//$accountList = $model->getAccountListByMemberID($member_id);
	    	return $accountList;
        	
        }

    }
    
    
    public function actionPrintEval()
    {
    	$pdf = new \mPDF();
    	$pdf->WriteHTML($this->renderPartial('loaneval'));
    	$pdf->Output();
    	exit();
    	
    }

    public function actionGetLatestInfo(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $member_id = $post['member_id'];
            $loan_id = $post['loan_id'];
            $acc = array();
            $acc = \app\models\LoanAccount::find()
                ->innerJoinWith(['product'])
                ->joinWith(['loanTransaction'])
                ->where(['loanaccount.member_id' => $member_id, 'loanaccount.loan_id' => $loan_id])
                ->andWhere('status != "Cancel" AND status != "Verified" ')
                //->where(['member_id' => $member_id, 'loan_id' => $loan_id])
                ->orderBy('release_date DESC')
                ->asArray()->one();
            $res = [
                'success' => true,
                'data' => $acc
            ];
            
				            
            $result = array();
            $getTransactions = [];
            $currentDate = ParticularHelper::getCurrentDay();
            $systemDate = date("Y-m-d", strtotime($currentDate));
            if($acc != null)
            {
                $release_date = $acc['release_date'];
                $prepaid_interest = 0;
                $interest_accum = 0;
                $last_tran_date = $acc['release_date'];
                $total_amount_paid = 0;
                $balance_after_cutoff = 0;
                $cutOff = Yii::$app->view->getCutOff();
                $balance_after_cutoff = $acc['principal'];

                $calVersion = Yii::$app->view->getVersion($acc['release_date']);

                $lastPayment = date('Y-m-d', strtotime($cutOff . ' +1 day'));
                $lastRunningBal = $acc['principal'];
                if($release_date > $lastPayment){
                   $lastPayment =  $release_date;
                }

                $firstTransaction = null;
                $getTransactions = LoanTransaction::find()->where(['loan_account' => $acc['account_no'], 'is_cancelled' => "0"])
                    ->andWhere('transaction_type = "RELEASE" OR transaction_type = "PAYPARTIAL"')
                    ->orderBy('date_posted')
                    ->asArray()->all();
                $transLength = count($getTransactions);

                foreach ($getTransactions as $transKey => $transaction) {

                    $last_tran_date = $transaction['date_posted'];

                    if($transaction['date_posted'] <= $cutOff){
                        $balance_after_cutoff = $transaction['running_balance'];
                        $lastRunningBal = $transaction['running_balance'];
                        continue;
                    }

                    if($transaction['transaction_type'] == "PAYPARTIAL"){
                        if($transaction['amount'] > 0){
                            $total_amount_paid = $total_amount_paid + $transaction['amount'];
                        }
                    }

                    if($transaction['prepaid_intpaid'] && floatval($transaction['prepaid_intpaid']) > 0){
                        $prepaid_interest = $prepaid_interest + $transaction['prepaid_intpaid'];
                    }

                    //Recalculate interest earned. Dili magsalig sa DB
                    $interestEarned = LoanHelper::getInterest($lastPayment, $transaction['date_posted'], $lastRunningBal, $acc['int_rate']);
                    $interest_accum = $interest_accum + $interestEarned;

                    //If at the last transaction
                    if($transLength == $transKey+1){
                        $interestEarnedLast = LoanHelper::getInterest($transaction['date_posted'], $systemDate, $transaction['running_balance'], $acc['int_rate']);
                        $interest_accum = $interest_accum + $interestEarnedLast;
                    }

                    $lastPayment = $transaction['date_posted'];
                    $lastRunningBal = $transaction['running_balance'];
                }

                //CUT OF PI AND INTEREST
                if($acc['loan_id'] == 2 || ($acc['loan_id'] == 1 && $calVersion == "1") ) {// Regular loan
                    //If loan exist before cut off. Get Cut Off Data
                    if($release_date <= $cutOff){
                        $cutOffYear = date('Y', strtotime($cutOff));
                        $getCutOff = LoanHelper::getCutOff($cutOffYear, $acc['loan_id'], $acc['member_id']);
                        if($getCutOff){
                            $prepaid_interest += $getCutOff['finalPi'];
                            $interest_accum += $getCutOff['finalInt'];
                        }
                        /*$beforeCutOff = LoanHelper::calculateBeforeCutOff($acc['account_no']);
                        if($beforeCutOff){
                            $interest_accum += $beforeCutOff['cutOffInt'];
                            $prepaid_interest += $beforeCutOff['cutOffPi'];
                        }*/
                    }
                    
                }

                $result['prepaid_interest'] = $prepaid_interest;
                $result['interest_accum'] = $interest_accum;
                $result['last_tran_date'] = $last_tran_date;
                $result['total_amount_paid'] = $total_amount_paid;
                $result['balance_after_cutoff'] = $balance_after_cutoff;
            	
            }
            
            
            $res = [
            		'success' => true,
            		'data' => [
            				'latestLoan' => $acc,
            				'lastTransaction' => $result,
                            'loanTransaction' => $getTransactions
            		]
            ];
				            
            return $res;
            
        }
    }

    public function actionEvaluateLoan(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
        	$member_id = $post['member_id'];
        	$model = new \app\models\LoanAccount;
    	
	    	$accountList = $model->getAccountListByMemberID($member_id);
	    	return $accountList;
        	
        }
    }
    
    
    public function actionVerifyLoan()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	if(\Yii::$app->getRequest()->getBodyParams())
        {
    		$success = true;
    		$transaction = \Yii::$app->db->beginTransaction();

            $post = \Yii::$app->getRequest()->getBodyParams();
    		
    		$loanaccount  = (object)$post['evaluationFormss'];
            //$loanToRenew  = $post['loanToRenew'];
    		
    		//	return $loanaccount_array;
    		
    		$loanproduct = LoanProduct::findOne($loanaccount->product_loan_id);
    		$loanproduct->trans_serial = $loanproduct->trans_serial + 1;
    		
    		$loanmodel = new LoanAccount();
    		
    		//$loanmodel->attributes = $loanaccount;
    		$loanmodel->account_no = $loanaccount->product_loan_id."-".str_pad($loanproduct->trans_serial, 6, '0', STR_PAD_LEFT);
    		$loanmodel->gv_or_number = '';
    		$loanmodel->principal = $loanaccount->amount;
    		$loanmodel->interest_balance = 0;
    		$loanmodel->principal_balance = $loanmodel->principal;
    		$loanmodel->release_date = date('Y-m-d');
    		$loanmodel->term = $loanaccount->duration;
    		$loanmodel->prepaid = 0;
    		$loanmodel->maturity_date = date('Y-m-d', strtotime("+".$loanmodel->term." months", strtotime($loanmodel->release_date)));
    		$loanmodel->service_charge = $loanaccount->service_charge_amount;
    		$loanmodel->prepaid_int = $loanproduct->prepaid_interest;
            $loanmodel->int_rate = $loanproduct->int_rate;
    		$loanmodel->is_active = 1;
    		$loanmodel->status='Verified';
    		$loanmodel->prepaid_accum = 0;
    		$loanmodel->interest_accum = 0;
    		$loanmodel->loan_id = $loanaccount->product_loan_id;
    		$loanmodel->redemption_insurance = $loanaccount->credit_redemption_ins;
    		$loanmodel->credit_interest = $loanaccount->credit_interest;
    		$loanmodel->credit_loan = $loanaccount->credit_loan;
    		$loanmodel->credit_preinterest = $loanaccount->credit_preinterest;
    		$loanmodel->debit_interest = $loanaccount->debit_interest;
    		$loanmodel->debit_redemption_ins = $loanaccount->debit_redemption_ins;
            $loanmodel->debit_preinterest = $loanaccount->debit_preinterest;
    		$loanmodel->savings_retention = $loanaccount->savings_retention;
    		$loanmodel->notary_amount = $loanaccount->notary_amount;
    		$loanmodel->prepaid_amortization_quincena = $loanaccount->prepaid_amortization_quincena;
    		$loanmodel->principal_amortization_quincena = $loanaccount->principal_amortization_quincena;
    		$loanmodel->net_cash = $loanaccount->net_cash;
    		$loanmodel->member_id = $loanaccount->member_id;
    		$loanmodel->date_created = date('Y-m-d');
    		
    		/*$loanTransaction = new LoanTransaction();
    		$loanTransaction->loan_account = $loanmodel->account_no;
    		$loanTransaction->amount = $loanaccount->amount;
    		$loanTransaction->transaction_type='Verified';
    		$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    		$loanTransaction->transaction_date = date('Y-m-d');
    		$loanTransaction->running_balance = $loanaccount->amount;
    		$loanTransaction->remarks = "loan release";
    		$loanTransaction->prepaid_intpaid = $loanaccount->credit_preinterest;
    		$loanTransaction->interest_paid = 0;
    		$loanTransaction->OR_no = "";
    		$loanTransaction->principal_paid = 0;
    		$loanTransaction->arrears_paid = 0;
    		$loanTransaction->date_posted = date('Y-m-d');
    		$loanTransaction->interest_earned = 0;*/
    		
    		
    		
    		if($loanproduct->save() && $loanmodel->save()/* && $loanTransaction->save()*/)
    		{
    			
    			$transaction->commit();
    			return $loanaccount;
    			
    		} else
    		{
    			$transaction->rollBack();
    			return [
    					'status'=>'not saved',
    					'errors'=> [
    							'lpError' => $loanproduct->getErrors(),
    							'lmError' => $loanmodel->getErrors(),
    							'ltError' => $loanTransaction->getErrors()
    					]
    					
    			];
    		}
    		
    		return $loanaccount;
    		
    	}
    }
    
    //JUst update the status to approve for ready to release
    public function actionUpdateLoanStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();

            $transaction = \Yii::$app->db->beginTransaction();
            
            $loanaccount = $post['loanAccount'];
            $status = $post['status'];

            $loanmodel = LoanAccount::findOne($loanaccount['account_no']);
            $loanmodel->status = $status;

            if($loanmodel->save()){
                $loanArr = LoanAccount::find()->where(['account_no' => $loanaccount['account_no']])->asArray()->one();
                $transaction->commit();
                return ['success' => true,
                    'data' => $loanArr];
            }
            else{
                $transaction->commit();
                return ['success' => true];
            }
        }


    }
    
    public function actionApplyLoan()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	if(\Yii::$app->getRequest()->getBodyParams())
    	{
            $post = \Yii::$app->getRequest()->getBodyParams();
    		$success = true;
    		$transaction = \Yii::$app->db->beginTransaction();
    		
    		$loanaccount_array  = $post;
    		$loanaccount = (object) $post['evaluationFormss'];
            $gv_num = $post['gv_num'];
            $voucherDetails = $post['voucherDetails'];
            $otherLoanToPay = $post['otherLoanToPay'];

            $currentDate = ParticularHelper::getCurrentDay();
            $systemDate = date("Y-m-d", strtotime($currentDate));
            $transac_date = $systemDate;
    		
    		/* For renewal, go give these parameters to close the loan.
    		 * Reminder: this function is not reusable for future closing of loans. 
    		 */
    		if($loanaccount_array['loanToRenew'])
    		{
                $loanToRenew = (object) $post['loanToRenew'];
    			$closeDetails = [];
    			$closeDetails['accountnumber'] = $loanToRenew->account_number;
    			$closeDetails['product_id'] = $loanToRenew->product_id;
    			$closeDetails['interest_pay'] = $loanaccount->credit_interest;
    			$closeDetails['principal_pay'] = $loanaccount->credit_loan;
    		//	$closeDetails['prepaid_int_return'] = $loanaccount->debit_preinterest;
    			$closeDetails['prepaid_int_pay'] = $loanaccount->credit_preinterest;
    			$closeDetails['reference'] = $gv_num;
    			
    			$closeLoan =  LoanHelper::closeAccountDueToRenewal($closeDetails);
    			
    			if($closeLoan!="success")
    			{
    				$transaction->rollBack();
    				return [
    						'status'=>'not saved',
    						'errors'=>$closeLoan    						
    				];
    			}
    		}
    		
    	//	return $loanaccount_array;
    		
    		/*$loanproduct = LoanProduct::findOne($loanaccount->loan_id);
    		$loanproduct->trans_serial = $loanproduct->trans_serial + 1;*/
    		
    		$loanmodel = LoanAccount::findOne($loanaccount->account_no);
    		$loanmodel->status='Released';

            $member = \app\models\Member::find()->where(['id' => $loanmodel->member_id])->one();
    		
    		$loanTransaction = new LoanTransaction();
    		$loanTransaction->loan_account = $loanmodel->account_no;
    		$loanTransaction->amount = $loanaccount->principal;
    		$loanTransaction->transaction_type='RELEASE';
    		$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    		$loanTransaction->transaction_date = $transac_date;
    		$loanTransaction->running_balance = $loanaccount->principal;
    		$loanTransaction->remarks = "loan release";
    		$loanTransaction->prepaid_intpaid = $loanaccount->credit_preinterest;
    		$loanTransaction->interest_paid = 0;
    		$loanTransaction->OR_no = $gv_num;
    		$loanTransaction->principal_paid = 0;
    		$loanTransaction->arrears_paid = 0;
    		$loanTransaction->date_posted = $transac_date;
    		$loanTransaction->interest_earned = 0;
    		
    		if(/*$loanproduct->save() && */$loanmodel->save() && $loanTransaction->save())
    		{
                //Update other paid loan
                if($otherLoanToPay && count($otherLoanToPay) > 0){
                    foreach ($otherLoanToPay as $lnKey => $ln) {
                        if($ln['amountToPay'] > 0){
                            $amountToPay = $ln['amountToPay'];
                            $otherLoanModel = LoanAccount::findOne($ln['account_no']);
                            $running_balance = $otherLoanModel->principal_balance - $amountToPay;
                            $otherLoanModel->principal_balance = $running_balance;

                            $transaction_type = 'PAYPARTIAL';
                            if($running_balance == 0){
                                $loanmodel->status='Closed';
                                $transaction_type="PAYCLOSE";
                            }
                        
                            $otherLoanTransaction = new LoanTransaction();
                            $otherLoanTransaction->loan_account = $otherLoanModel->account_no;
                            $otherLoanTransaction->amount = $amountToPay;
                            $otherLoanTransaction->transaction_type=$transaction_type;
                            $otherLoanTransaction->transacted_by = \Yii::$app->user->identity->id;
                            $otherLoanTransaction->transaction_date = $transac_date;
                            $otherLoanTransaction->running_balance = $running_balance;
                            $otherLoanTransaction->remarks = "loan payment by regular loan";
                            $otherLoanTransaction->prepaid_intpaid = 0;
                            $otherLoanTransaction->interest_paid = 0;
                            $otherLoanTransaction->OR_no = $gv_num;
                            $otherLoanTransaction->principal_paid = $amountToPay;
                            $otherLoanTransaction->arrears_paid = 0;
                            $otherLoanTransaction->date_posted = $transac_date;
                            $otherLoanTransaction->interest_earned = 0;

                            if($otherLoanModel->save() && $otherLoanTransaction->save())
                            {
                                $success = true;    
                            }
                            else{
                                $success = false;
                                break;
                            }
                        }
                        
                    }
                }

    			if($loanaccount->savings_retention>0 && $success)
    			{
    				$shareaccount = Shareaccount::findOne(['fk_memid'=>$loanaccount->member_id, 'is_active'=>1]);
    				$sharetransaction = new ShareTransaction();
    				$sshareproduct = ShareProduct::findOne($shareaccount->fk_share_product);
    				
    				$sharetransaction->fk_share_id = $shareaccount->accountnumber;
    				$sharetransaction->amount = $loanaccount->savings_retention;
    				$sharetransaction->transaction_type = 'CASHDEP';
    				$sharetransaction->transacted_by = \Yii::$app->user->identity->id;
    				$sharetransaction->transaction_date = date('Y-m-d H:i:s', strtotime($transac_date));
    				$sharetransaction->running_balance = $shareaccount->balance + $loanaccount->savings_retention;
    				$sharetransaction->remarks = "posted as Retention from loan ".$loanmodel->account_no;
    				$sharetransaction->reference_number = $gv_num; //"GV123.sample";
    				
    				$shareaccount->balance = $shareaccount->balance + $loanaccount->savings_retention;
    				
    				if($shareaccount->save() && $sharetransaction->save())
    				{
                        $success = true;	
    				}
    				
    			}

                if($success){
                    //Save in voucher
                    $voucherData = array();
                    $voucherData['gv_num'] = $gv_num;
                    if($member){
                        $name =  $member->first_name . " " . $member->middle_name . " " . $member->last_name;
                        $type = "Individual";
                        $member_id = $member->id;
                    }
                    $voucherData['name'] = $name;
                    $voucherData['type'] = $type;
                    $voucherData['date_transact'] = \Yii::$app->user->identity->DateTimeNow;

                
                    $voucherModel = VoucherHelper::saveVoucher($voucherData);
                    if($voucherModel){
                        $entries =  $voucherDetails;
                        foreach ($entries as  $key => $ent) {
                            $entries[$key]['member_id'] = $member_id;
                        }
                        $saveEntries = VoucherHelper::insertEntries($entries, $voucherModel->id);
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
                    return [
                        'success' => true,
                        'loanaccount' => $loanaccount
                            
                    ];
                }else{
                    $transaction->rollBack();
                    return [
                        'success' => false,
                        'status'=>'not saved'
                    ];
                }
    			
    		} else
    		{
    			$transaction->rollBack();
    			return [
                    'success' => false,
					'status'=>'not saved',
					'errors'=> [
							'lmError' => $loanmodel->getErrors(),
							'ltError' => $loanTransaction->getErrors()
					]
    					
    			];
    		}
    		
            return [
                'success' => false
            ];
    		
    	}
    }

    public function actionReleaseVoucher(){
        $this->layout = 'main-vue';

        $voucher = new \app\models\GeneralVoucher;
        $voucherModel = $voucher->attributes();

        $details = new \app\models\VoucherDetails;
        $detailsModel = $details->attributes();

        $filter  = ['category' => ['LOAN', 'OTHERS']];
        $getParticular = ParticularHelper::getParticulars($filter);

        return $this->render('loan-voucher', [
            'voucherModel'      => $voucherModel,
            'detailsModel'      => $detailsModel,
            'particularList'    => $getParticular
        ]);
    }

    public function actionSaveReleaseLoan(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = false;
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();
                $voucherModel = $post['voucherModel'];
                $entryList = $post['entryList'];

                //Check GV Number if exist
                $gv_num = $voucherModel['gv_num'];
                $getGV = \app\models\GeneralVoucher::find()->where(['gv_num' => $gv_num])->one();
                if($getGV){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASGV'
                    ];
                }
                else{

                    //Save Loan transaction here
                    $saveTransaction = false;


                    //After loan transaction is saved, Save General Voucher and Entries
                    if($saveTransaction){
                        //Save gv and entries
                        $saveGV = VoucherHelper::saveVoucher($voucherModel);
                        if($saveGV){
                            //Entries
                            VoucherHelper::insertEntries($entryList,$saveGV->id, 'LOAN');
                            $success = true;
                        }
                        //Save in journal entry

                    }
                }
                $transaction->commit();
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



    public function actionPrintSummary(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            $dataLoan = $postData['dataLoan'];
            $type = $postData['type'];
            
            $template = LoanHelper::printLoanSummary($dataLoan);
            
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

    public function actionPrintLedger(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            $dataLoan = $postData['dataLoan'];
            $type = $postData['type'];
            
            $template = LoanHelper::printLoanLedger($dataLoan);
            
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
    
    
    public function actionGetCurrentLoans()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	$post = \Yii::$app->getRequest()->getBodyParams();
    	
    	$loans = LoanAccount::find()->innerJoinWith('product')
							    	->innerJoinWith('member')
							    	->innerJoinWith('loanTransaction')
							    	->where(['in', 'status', ['Current']])
							    	->andWhere(['like', "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name)", $post['nameInput'].'%', false])->asArray()->all();
    	
    	return $loans;
    	
    }
    
    public function actionGetCurrentLoanInterest()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$post = \Yii::$app->getRequest()->getBodyParams();
    	
    	if($post)
    	{
    		$interest = PaymentHelper::getCurrentInterest($post['accountnumber'], $post['int_rate']);
    		
    		return $interest;
    	}
    	
 
    }
    
    
    public function actionLoanClosure() {
    	$this->layout = 'main-vue';
    	
    	
    	
    	/*$voucher = new \app\models\GeneralVoucher;
    	$voucherModel = $voucher->attributes();
    	
    	$details = new \app\models\VoucherDetails;
    	$detailsModel = $details->attributes();
    	
    	$filter  = ['category' => ['LOAN', 'OTHERS']];
    	$getParticular = ParticularHelper::getParticulars($filter); */
    	
    	return $this->render('loan-closure', []);
    	
    }
    
    public function actionCancelPayments() {
    	
    	$this->layout = 'main-vue';
    	
    	return $this->render('payment-cancellation', []);
    }
}
