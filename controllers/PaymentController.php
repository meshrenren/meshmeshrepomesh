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
    public function actionIndex()
    {
        $this->layout = 'main-vue';
    	$paymentModel = new \app\models\PaymentRecord;
        $paymentModel = $paymentModel->attributes();

        $filter  = ['category' => ['OTHERS']];
        $getParticular = ParticularHelper::getParticulars($filter);

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
                $getOR = \app\models\PaymentRecord::find()->where(['or_num' => $or_num])->one();
                if($getOR){
                    return [
                        'success'   => "mesry",
                        'error'     => 'ERROR_HASOR'
                    ];
                }
                else{

                    //Save Loan payment here
                    $saveTransaction = true;


                    //After loan transaction is saved, Save General Voucher and Entries
                    if($saveTransaction){
                        //Save gv and entries
                        $saveOR = PaymentHelper::savePayment($paymentModel);
                        if($saveOR){
                            //Entries
                            $insertSuccess = PaymentHelper::insertAccount($allAccounts, $saveOR->id);
                            if(!$insertSuccess){
                                $success = "wtf";
                            }
                            else{
                                $success = true;
                            }
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
