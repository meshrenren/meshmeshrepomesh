<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
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
            if(count($loanAccounts) > 1){
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
}
