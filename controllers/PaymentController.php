<?php

namespace app\controllers;

use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;
use app\helpers\accounts\TimeDepositHelper;

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

    public function actionSavePaymentList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {

            $post = \Yii::$app->getRequest()->getBodyParams();
            $paymentList = $post['paymentList'];
            $orNum = $post['orNum'];
            $forceAdd = $post['forceAdd'];
            $paymentIds = [];
            $hasError = false;
            if(!$forceAdd){
                $getVoucher  = \app\models\PaymentRecord::find()->where(['or_num' => $orNum])->one();
                if($getVoucher){
                     return [
                        'error'     => 'has_ornum',
                        'hasError'  => true
                    ];
                }
               
            }
            $error = [];
            foreach ($paymentList as $entry) {
                $createEntry  = new \app\models\PaymentRecord;
                if(isset($entry['name_id'])){
                    unset($entry['name_id']);
                }
                $createEntry->attributes = $entry;
                if($createEntry->save()){
                    array_push($paymentIds, $createEntry->id);
                }
                else{
                    array_push($error, $createEntry->getErrors()) ;
                    $hasError = true;
                }
            }

            return [
                'data'      => $paymentIds,
                'hasError'  => $hasError,
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
