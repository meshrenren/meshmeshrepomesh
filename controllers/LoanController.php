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
            $query->select('DISTINCT(loan_id) as loan_id')
                ->from('loanaccount la')
                ->where('member_id = '. $member_id);
            $loanAccounts = $query->all();
            $accountList = array();
            if(count($loanAccounts) >= 1){
                foreach ($loanAccounts as $loan) {
                    $acc = \app\models\LoanAccount::find()
                        ->innerJoinWith(['product'])
                        ->where(['member_id' => $member_id, 'loan_id' =>  $loan['loan_id']])
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
                ->where(['member_id' => $member_id, 'loan_id' => $loan_id])
                ->orderBy('release_date DESC')
                ->asArray()->one();
            $res = [
                'success' => true,
                'data' => $acc
            ];
			
            
            
            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("
				    SELECT sum(prepaid_intpaid) as prepaid_interest, sum(interest_paid) as interest_accum, DATE_FORMAT(NOW(), '%Y-%m-%d') as datenow,
					ifnull((select date_posted from loan_transaction where loan_account=:accountnumber and LEFT(transaction_type,3)='PAY' order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber)) as last_tran_date
					FROM `loan_transaction` lt where loan_account=:accountnumber and LEFT(transaction_type,3)='PAY'
					order by id, date_posted", [':accountnumber' => $acc['account_no'] ]);				            
				            $result = $command->queryOne();
            
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
    
    
    public function actionApplyLoan()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	if(isset($_POST))
    	{
    		$loanaccount  = json_decode($_POST['applyLoan']);
    		$loanaccount = (array)$loanaccount;
    		
    		$loanproduct = LoanProduct::findOne($loanaccount['product_loan_id']);
    		$loanproduct->trans_serial = $loanproduct->trans_serial + 1;
    		
    		$loanmodel = new LoanAccount();
    		
    		//$loanmodel->attributes = $loanaccount;
    		$loanmodel->account_no = $loanaccount['product_loan_id']."-".str_pad($loanproduct->trans_serial, 6, '0', STR_PAD_LEFT);
    		$loanmodel->gv_or_number = '';
    		$loanmodel->principal = $loanaccount['amount'];
    		$loanmodel->interest_balance = 0;
    		$loanmodel->principal_balance = $loanmodel->principal;
    		$loanmodel->release_date = date('Y-m-d');
    		$loanmodel->term = $loanaccount['duration'];
    		$loanmodel->prepaid = 0;
    		$loanmodel->maturity_date = date('Y-m-d', strtotime("+".$loanmodel->term." months", strtotime($loanmodel->release_date)));
    		$loanmodel->service_charge = $loanaccount['service_charge_amount'];
    		$loanmodel->prepaid_int = 0;
    		$loanmodel->is_active = 1;
    		$loanmodel->status='Verified';
    		$loanmodel->prepaid_accum = 0;
    		$loanmodel->interest_accum = 0;
    		$loanmodel->loan_id = $loanaccount['product_loan_id'];
    		$loanmodel->redemption_insurance = $loanaccount['credit_redemption_ins'];
    		$loanmodel->credit_interest = $loanaccount['credit_interest'];
    		$loanmodel->credit_loan = $loanaccount['credit_loan'];
    		$loanmodel->credit_preinterest = $loanaccount['credit_preinterest'];
    		$loanmodel->debit_interest = $loanaccount['debit_interest'];
    		$loanmodel->debit_redemption_ins = $loanaccount['debit_redemption_ins'];
    		$loanmodel->savings_retention = $loanaccount['savings_retention'];
    		$loanmodel->notary_amount = $loanaccount['notary_amount'];
    		$loanmodel->prepaid_amortization_quincena = $loanaccount['prepaid_amortization_quincena'];
    		$loanmodel->principal_amortization_quincena = $loanaccount['principal_amortization_quincena'];
    		$loanmodel->net_cash = $loanaccount['net_cash'];
    		$loanmodel->member_id = $loanaccount['member_id'];
    		
    		$loanTransaction = new LoanTransaction();
    		$loanTransaction->loan_account = $loanmodel->account_no;
    		$loanTransaction->amount = $loanaccount['amount'];
    		$loanTransaction->transaction_type='RELEASE';
    		$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    		$loanTransaction->transaction_date = date('Y-m-d');
    		$loanTransaction->running_balance = $loanaccount['amount'];
    		$loanTransaction->remarks = "loan release";
    		$loanTransaction->prepaid_intpaid = 0;
    		$loanTransaction->interest_paid = 0;
    		$loanTransaction->OR_no = "GV123.sample";
    		$loanTransaction->principal_paid = 0;
    		$loanTransaction->arrears_paid = 0;
    		$loanTransaction->date_posted = date('Y-m-d');
    		$loanTransaction->interest_earned = 0;
    		
    		
    		if($loanproduct->save() && $loanmodel->save() && $loanTransaction->save())
    		{
    			return $loanaccount;
    			
    		} else return [
    				'status'=>'not saved',
    				 'errors'=> [
    				 		'lpError' => $loanproduct->getErrors(),
    				 		'lmError' => $loanmodel->getErrors(),
    				 		'ltError' => $loanTransaction->getErrors()
    				 ]
    				
    		];
    		
    		
    		
    		
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
