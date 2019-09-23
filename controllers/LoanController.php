<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use \Mpdf\Mpdf;
use app\models\LoanAccount;
use app\models\LoanProduct;

use app\helpers\accounts\LoanHelper;
use app\helpers\particulars\ParticularHelper;
use app\helpers\voucher\VoucherHelper;
use app\models\LoanTransaction;
use app\models\SavingsAccounts;
use app\models\SavingsTransaction;
use app\models\Savingsproduct;
use app\models\SavingAccounts;
use app\helpers\payment\PaymentHelper;


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
    	
    	/*
    	 * end
    	 */
    	return $this->render('pending-list', ['ForApprovalLoans'=>$loandetails]);
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
                $query->orderBy('transaction_date DESC')
                ->asArray()->all();
            }];
            $loanAccounts = LoanHelper::getMemberLoan($member_id, $loan_id, true, $joinWith, true);
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
            $query->select('loan_id, account_no')
                ->from('loanaccount la')
                ->where('member_id = '. $member_id);
            $loanAccounts = $query->all();
            $accountList = array();
            if(count($loanAccounts) >= 1){
                foreach ($loanAccounts as $loan) {
                    $acc = \app\models\LoanAccount::find()
                        ->innerJoinWith(['product'])
                        ->where(['member_id' => $member_id, 'loan_id' =>  $loan['loan_id'], 'account_no' => $loan['account_no']])
                        ->orderBy('release_date DESC')
                        ->asArray()->one();
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
                ->where(['member_id' => $member_id, 'loan_id' => $loan_id, 'status'=>'Current'])
                ->orderBy('release_date DESC')
                ->asArray()->one();
            $res = [
                'success' => true,
                'data' => $acc
            ];
            
			
            
            
            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("
				    SELECT sum(prepaid_intpaid) as prepaid_interest, sum(interest_earned) as interest_accum, DATE_FORMAT(NOW(), '%Y-%m-%d') as datenow,
					ifnull((select date_posted from loan_transaction where loan_account=:accountnumber and LEFT(transaction_type,3)='PAY' and is_cancelled=0 order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber)) as last_tran_date
					FROM `loan_transaction` lt where loan_account=:accountnumber and LEFT(transaction_type,3) in ('PAY', 'REL') and is_cancelled=0
					order by id, date_posted", [':accountnumber' => $acc['account_no'] ]);				            
				            $result = $command->queryOne();
				            
				            if($acc != null)
				            {
				            	$product = LoanProduct::findOne($acc['loan_id']);
				            	$noOfDaysPassed = date_diff(date_create(date('Y-m-d')), date_create($result['last_tran_date']));
				            	
				            	$noOfDaysPassed = $noOfDaysPassed->format("%a");
				            	
				            	$interestEarned = ($acc['principal_balance'] * ($product->int_rate/100))/30;
				            	$interestEarned = round($interestEarned * $noOfDaysPassed, 2);
				            	
				            	$result['interest_accum'] = $result['interest_accum'] + $interestEarned;
				            	
				            }
				            
				            
				            $res = [
				            		'success' => true,
				            		'data' => [
				            				'latestLoan' => $acc,
				            				'lastTransaction' => $result
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
    	
    	if(isset($_POST))
    	{
    		$success = true;
    		$transaction = \Yii::$app->db->beginTransaction();
    		
    		$loanaccount_object  = json_decode($_POST['applyLoan']);
    		$loanaccount_array = (array)$loanaccount_object;
    		$loanaccount = $loanaccount_array['evaluationFormss'];
    		
    		
    		
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
    		$loanmodel->prepaid_int = 0;
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
    		$loanmodel->savings_retention = $loanaccount->savings_retention;
    		$loanmodel->notary_amount = $loanaccount->notary_amount;
    		$loanmodel->prepaid_amortization_quincena = $loanaccount->prepaid_amortization_quincena;
    		$loanmodel->principal_amortization_quincena = $loanaccount->principal_amortization_quincena;
    		$loanmodel->net_cash = $loanaccount->net_cash;
    		$loanmodel->member_id = $loanaccount->member_id;
    		$loanmodel->date_created = date('Y-m-d');
    		
    		$loanTransaction = new LoanTransaction();
    		$loanTransaction->loan_account = $loanmodel->account_no;
    		$loanTransaction->amount = $loanaccount->amount;
    		$loanTransaction->transaction_type='Verified';
    		$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    		$loanTransaction->transaction_date = date('Y-m-d');
    		$loanTransaction->running_balance = $loanaccount->amount;
    		$loanTransaction->remarks = "loan release";
    		$loanTransaction->prepaid_intpaid = $loanaccount->credit_preinterest;
    		$loanTransaction->interest_paid = 0;
    		$loanTransaction->OR_no = "GV123.sample";
    		$loanTransaction->principal_paid = 0;
    		$loanTransaction->arrears_paid = 0;
    		$loanTransaction->date_posted = date('Y-m-d');
    		$loanTransaction->interest_earned = 0;
    		
    		
    		
    		if($loanproduct->save() && $loanmodel->save() && $loanTransaction->save())
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
    
    
    
    public function actionApplyLoan()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	if(isset($_POST))
    	{
    		$success = true;
    		$transaction = \Yii::$app->db->beginTransaction();
    		
    		$loanaccount_object  = json_decode($_POST['applyLoan']);
    		$loanaccount_array = (array)$loanaccount_object;
    		$loanaccount = $loanaccount_array['evaluationFormss'];
    		
    		
    		/* For renewal, go give these parameters to close the loan.
    		 * Reminder: this function is not reusable for future closing of loans. 
    		 */
    		if($loanaccount_array['loanToRenew'])
    		{
    			$closeDetails = [];
    			$closeDetails['accountnumber'] = $loanaccount_array['loanToRenew']->account_number;
    			$closeDetails['product_id'] = $loanaccount_array['loanToRenew']->product_id;
    			$closeDetails['interest_pay'] = $loanaccount->credit_interest;
    			$closeDetails['principal_pay'] = $loanaccount->credit_loan;
    		//	$closeDetails['prepaid_int_return'] = $loanaccount->debit_preinterest;
    			$closeDetails['prepaid_int_pay'] = $loanaccount->credit_preinterest;
    			$closeDetails['reference'] = "GV123.sample";
    			
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
    		
    		$loanproduct = LoanProduct::findOne($loanaccount->loan_id);
    		$loanproduct->trans_serial = $loanproduct->trans_serial + 1;
    		
    		$loanmodel = LoanAccount::findOne($loanaccount->account_no);
    		
    		//$loanmodel->attributes = $loanaccount;
    		/*$loanmodel->account_no = $loanaccount->product_loan_id."-".str_pad($loanproduct->trans_serial, 6, '0', STR_PAD_LEFT);
    		$loanmodel->gv_or_number = '';
    		$loanmodel->principal = $loanaccount->amount;
    		$loanmodel->interest_balance = 0;
    		$loanmodel->principal_balance = $loanmodel->principal;
    		$loanmodel->release_date = date('Y-m-d');
    		$loanmodel->term = $loanaccount->duration;
    		$loanmodel->prepaid = 0;
    		$loanmodel->maturity_date = date('Y-m-d', strtotime("+".$loanmodel->term." months", strtotime($loanmodel->release_date)));
    		$loanmodel->service_charge = $loanaccount->service_charge_amount;
    		$loanmodel->prepaid_int = 0;
    		$loanmodel->is_active = 1; */
    		$loanmodel->status='Released';
    		/*$loanmodel->prepaid_accum = 0;
    		$loanmodel->interest_accum = 0;
    		$loanmodel->loan_id = $loanaccount->product_loan_id;
    		$loanmodel->redemption_insurance = $loanaccount->credit_redemption_ins;
    		$loanmodel->credit_interest = $loanaccount->credit_interest;
    		$loanmodel->credit_loan = $loanaccount->credit_loan;
    		$loanmodel->credit_preinterest = $loanaccount->credit_preinterest;
    		$loanmodel->debit_interest = $loanaccount->debit_interest;
    		$loanmodel->debit_redemption_ins = $loanaccount->debit_redemption_ins;
    		$loanmodel->savings_retention = $loanaccount->savings_retention;
    		$loanmodel->notary_amount = $loanaccount->notary_amount;
    		$loanmodel->prepaid_amortization_quincena = $loanaccount->prepaid_amortization_quincena;
    		$loanmodel->principal_amortization_quincena = $loanaccount->principal_amortization_quincena;
    		$loanmodel->net_cash = $loanaccount->net_cash;
    		$loanmodel->member_id = $loanaccount->member_id; */
    		
    		$loanTransaction = new LoanTransaction();
    		$loanTransaction->loan_account = $loanmodel->account_no;
    		$loanTransaction->amount = $loanaccount->principal;
    		$loanTransaction->transaction_type='RELEASE';
    		$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    		$loanTransaction->transaction_date = date('Y-m-d');
    		$loanTransaction->running_balance = $loanaccount->principal;
    		$loanTransaction->remarks = "loan release";
    		$loanTransaction->prepaid_intpaid = $loanaccount->credit_preinterest;
    		$loanTransaction->interest_paid = 0;
    		$loanTransaction->OR_no = "GV123.sample";
    		$loanTransaction->principal_paid = 0;
    		$loanTransaction->arrears_paid = 0;
    		$loanTransaction->date_posted = date('Y-m-d');
    		$loanTransaction->interest_earned = 0;
    		

    		
    		if($loanproduct->save() && $loanmodel->save() && $loanTransaction->save())
    		{
    			if($loanaccount->savings_retention>0)
    			{
    				$savingsaccount = SavingAccounts::findOne(['member_id'=>$loanaccount->member_id, 'is_active'=>1]);
    				$savingstransaction = new SavingsTransaction();
    				$savingsproduct = Savingsproduct::findOne($savingsaccount->saving_product_id);
    				
    				$savingstransaction->fk_savings_id = $savingsaccount->account_no;
    				$savingstransaction->amount = $loanaccount->savings_retention;
    				$savingstransaction->transaction_type = 'CASHDEP';
    				$savingstransaction->transacted_by = \Yii::$app->user->identity->id;
    				$savingstransaction->transaction_date = date('Y-m-d H:i:s');
    				$savingstransaction->running_balance = $savingsaccount->balance + $loanaccount->savings_retention;
    				$savingstransaction->remarks = "posted as Retention from loan ".$loanmodel->account_no;
    				$savingstransaction->ref_no = "GV123.sample";
    				
    				$savingsaccount->balance = $savingsaccount->balance + $loanaccount->savings_retention;
    				
    				if($savingsaccount->save() && $savingstransaction->save())
    				{
    					$transaction->commit();
    					return $loanaccount;
    					
    				}
    				
    				else
    				{
    					$transaction->rollBack();
    					return [
    							'status'=>'not saved',
    							'errors'=> [
    									'saError' => $savingsaccount->getErrors(),
    									'stError' => $savingstransaction->getErrors(),
    									
    									
    							]
    						];
    							
    				}
    				
    				
    				
    			}
    			
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
}
