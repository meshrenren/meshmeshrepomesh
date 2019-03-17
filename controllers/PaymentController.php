<?php

namespace app\controllers;

use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;
use app\helpers\accounts\TimeDepositHelper;

use app\helpers\payment\PaymentHelper;

class PaymentController extends \yii\web\Controller
{
    public $entryType = array();

    public function __construct($id, $module, $config = [])
    {
        $paymentHelper = new PaymentHelper;
        $entryType = $paymentHelper->entry_type;

        parent::__construct($id, $module, $config);
    } 


    public function actionIndex()
    {
        $this->layout = 'main-vue';
    	$paymentModel = new \app\models\PaymentRecord;
        $paymentModel = $paymentModel->getAttributes();

        $filter  = ['category' => ['OTHERS']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);


        return $this->render('index', [
        	'model'         	=> $paymentModel,
            'particularList'   => $getParticular
        ]);
    }
    
    
    public function actionPostPayment($id)
    {
    	PaymentHelper::postPayment($id);
    }

    public function actionSavePaymentList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = "begin me";
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();
                $paymentModel = $post['paymentModel'];
                $allAccounts = $post['allAccounts'];

                //Check GV Number if exist
                $or_num = $paymentModel['or_num'];
                $getOR = \app\models\JournalHeader::find()->where(['reference_no' => $or_num])->one();
                if($getOR){
                    return [
                        'success'   => "mesry",
                        'error'     => 'ERROR_HASOR'
                    ];
                }
                else{

                    //Save Loan payment here


                    //After loan transaction is saved, Save General Voucher and Entries
                    if($success){
                        //Save gv and entries
                        $saveOR = PaymentHelper::savePayment($paymentModel);
                        if($saveOR){
                            //Entries
                            $insertSuccess = PaymentHelper::insertAccount($allAccounts, $saveOR->id);
                            if(!$insertSuccess){
                                $success = "wtf";
                            }
                            else{
                                $success = false;
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $transaction->rollBack();
                        }

                    }

                    //Save in journal
                    if($success && $saveOR){
                        $journalHeader = new \app\models\JournalHeader;
                        $journalHeaderData = $journalHeader->getAttributes();
                        $journalHeaderData['reference_no'] = $saveOR->or_num;
                        $journalHeaderData['posting_date'] = $saveOR->date_transact;
                        $journalHeaderData['total_amount'] = 0;
                        $journalHeaderData['trans_type'] = 'Payment';
                        $journalHeaderData['remarks'] = '';

                        $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                        if($saveJournal){
                            //Entries

                            $journalList = new \app\models\JournalDetails;
                            $journalListAttr = $journalList->getAttributes();
                            $lists = array();
                            $totalAmount = 0;
                            $totalCredit = 0;
                            $totalDebit = 0;
                            foreach ($allAccounts as $acct) {
                                $arr = $journalListAttr;
                                $arr['amount'] = $acct['amount'];
                                $arr['particular_id'] = $acct['particular_id'];
                                $arr['entry_type'] = $entryType[$acct['type']];

                                if($arr['entry_type'] == 'CREDIT'){
                                    $totalCredit += $acct['amount'];
                                }
                                else if($arr['entry_type'] == 'CREDIT'){
                                    $totalDebit += $acct['amount'];
                                }

                                $totalAmount += $acct['amount'];
                                array_push($lists, $arr);
                            }

                            // Set total credit and total debit as Cash On Hand
                            $coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);
                            if($totalCredit > 0){
                                $arr = $journalListAttr;
                                $arr['amount'] = $totalCredit;
                                $arr['particular_id'] = $coh_id->id;
                                $arr['entry_type'] = "DEBIT";
                                array_push($lists, $arr);
                            } 

                            if($totalDebit > 0){
                                $arr = $journalListAttr;
                                $arr['amount'] = $totalDebit;
                                $arr['particular_id'] = $coh_id->id;
                                $arr['entry_type'] = "CREDIT";
                                array_push($lists, $arr);
                            } 

                            $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->id);
                            if($insertSuccess){
                                $saveJournal->total_amount = $totalAmount;
                                $saveJournal->save();
                                
                                $success = true;
                            }
                            else{
                                $success = false;
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $transaction->rollBack();
                        }
                    }
                }

                if($success){
                    $transaction->commit();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                $error =  $e->getMessage();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                $error = $e->getMessage();
            }

            return [
                'success'   => $success,
                'data'      => $data,
                'error'     => $error
            ];
        }
    }

    public function actionPayroll(){
        $this->layout = 'main-vue';

        $paymentModel = new \app\models\PaymentRecord;
        $paymentModel = $paymentModel->attributes();

        $pytPayroll = new \app\models\PaymentPayroll;
        $pytPayrollModel = $pytPayroll->attributes();

        $pytPayrollList = new \app\models\PaymentPayrollList;
        $pytPayrollListModel = $pytPayrollList->attributes();

        $getParticular = ParticularHelper::getPayrollParticulars();

        $members = \app\models\Member::find()->all();

        return $this->render('payroll-payment', [
            'paymentModel'          => $paymentModel,
            'pytPayrollModel'       => $pytPayrollModel,
            'pytPayrollListModel'   => $pytPayrollListModel,
            'memberList'            => $members
        ]);
    }

    public function actionGetPaymentRecord(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $paymentRecord  = \app\models\PaymentRecord::find()->joinWith(['particular'])->asArray()->all();

            return [
                'data' => $paymentRecord,
            ];
        }
    }

    public function actionGetMemberAccounts(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $id = $post['member_id'];

            $filter = ['member_id' => $id];

            $getSavings = SavingsHelper::getAccountSavingsInfo($filter);
            $getShare = ShareHelper::getAccountShareInfo($filter);
            $getLoan = LoanHelper::getAccountLoanInfo($id);
        
            return [
                'savingsAccounts'        => $getSavings,
                'shareAccounts'          => $getShare,
                'loanAccounts'           => $getLoan
            ];
            
        }

    }

}
