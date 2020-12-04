<?php

namespace app\controllers;

use app\helpers\particulars\ParticularHelper;
use app\helpers\voucher\VoucherHelper;
use app\helpers\payment\PaymentHelper;
use app\helpers\journal\JournalHelper;
use app\helpers\member\MemberHelper;
use app\models\AccountParticulars;
use app\models\VoucherDetails;
use \app\models\LoanTransaction;

use app\models\GeneralVoucher;

class GeneralVoucherController extends \yii\web\Controller
{
    public function actionIndex($record = null)
    {
    	$this->layout = 'main-vue';

        $voucherList = [];
        if($record != null){
            $voucherModel = \app\models\GeneralVoucher::find()->where(['id' => $record])->asArray()->one();
            $voucherList = VoucherHelper::getList($record);
        }
        else{
            $voucher = new \app\models\GeneralVoucher;
            $voucherModel = $voucher->getAttributes();
        }

        $details = new \app\models\VoucherDetails;
        $detailsModel = $details->getAttributes();

        /*$filter  = ['category' => ['OTHERS', 'SAVINGS', 'SHARE', 'LOAN', 'TIME_DEPOSIT']];
        $orderBy = [new \yii\db\Expression('FIELD (category,"OTHERS","LOAN","SAVINGS","SHARE","TIME_DEPOSIT"), name ASC')];
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);*/
        $filter  = ['category' => ['OTHERS', 'LOAN']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);

        return $this->render('index', [
        	'voucherModel'      => $voucherModel,
            'voucherList'       => $voucherList,
            'detailsModel'      => $detailsModel,
            'particularList'    => $getParticular
        ]);
    }

    public function actionGetName(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $member  = \app\models\Member::find()->asArray()->all();
            $division  = \app\models\Division::find()->asArray()->all();
            $station  = \app\models\Station::find()->asArray()->all();

            return [
                'member' => $member,
                'division' => $division,
                'station' => $station,
            ];
        }
    }


    public function actionSaveVoucherEntries(){
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
                $allAccounts = $post['allAccounts'];
                $success = false;
                $error = '';
                $data = null;

                //Check GV Number if exist
                $gv_num = $voucherModel['gv_num'];
                //$getGV = \app\models\GeneralVoucher::find()->where(['gv_num' => $gv_num])->one();
                $getGV = GeneralVoucher::find()->where(['gv_num' => $gv_num])->andWhere('posted_date IS NOT NULL')->one();
                if($getGV){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASGV'
                    ];
                }
                else{
                /*if($getGV){
                    $getGV = VoucherHelper::saveVoucher($voucherModel);
                }*/
                    $genVoucher = GeneralVoucher::find()->where(['gv_num' => $gv_num])->one();
                    if($genVoucher){
                        if($genVoucher->posted_date != null){
                            return [
                                'success'   => false,
                                'error'     => 'ERROR_HASOR',
                                'test'     => 'ERROR_HASOR'
                            ];
                        }
                    }

                    $saveGV = VoucherHelper::saveVoucher($voucherModel);
                    if($saveGV){
                        if($genVoucher){
                            //ADD HISTORY FUNCTION: Get then save to history
                            //Delete existing payment record
                            VoucherDetails::deleteAll('voucher_id = :voucher_id', [':voucher_id' => $genVoucher->id]);
                        }

                        //Entries
                        $insertSuccess = VoucherHelper::insertEntries($allAccounts, $saveGV->id);
                        if($insertSuccess){
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
                'success'   => $success,
                'error'     => $error,
                'data'      => $data
            ];
        }
    }


    public function actionView(){
        $this->layout = 'main-vue';
        //$voucherList = $this->getVoucherList(null, 100);

        return $this->render('view', [
            'voucherList'   => []
        ]);
    }

    public function actionGetVoucher(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $filter = $post['filter'];
            $getVoucher  = \app\models\GeneralVoucher::find()->where(['gv_num' => $filter['gv_num']])->asArray()->one();
            $voucherList = [];
            $success = false;
            if($getVoucher){
                $listFilter = ['voucher_id' => $getVoucher['id']];
                $voucherList = VoucherHelper::getVoucherList($listFilter, null);
                $success = true;
            }

            return [
                'success'   => $success,
                'voucher'   => $getVoucher,
                'list'      => $voucherList
            ];
        }
    }

    public function actionGetAllVoucher(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $getVouchers  = \app\models\GeneralVoucher::find()->limit(500)->asArray()->all();
        return [
            'data' => $getVouchers
        ];
    }
    
    public function actionViewSummary(){
    	$this->layout = 'main-vue';
    	//$voucherList = $this->getVoucherList(null, 100);
    	
    	return $this->render('view-summary', [
    			'voucherList'   => []
    	]);
    }
    
    public function actionGetParticulars() {
    	
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	return AccountParticulars::find()->select(['id', 'name'])->asArray()->all();
    	
    }
    
    public function actionGetVoucherSummaryPerParticulars() {
    	
    	try {
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		
    		$model = new VoucherDetails();
    		$post = \Yii::$app->getRequest()->getBodyParams();
    		
    		
    		
    		
    		//$modelx = $model->find()->joinWith(['voucher', 'particular'])->where(['particular_id'=>99])->andWhere(['like', 'general_voucher.name', 'MENDOZA,%', false])->asArray()->all();
    		
    		$modelx = $model->find()->joinWith(['voucher', 'particular'])->where(['particular_id'=>$post['particular_id']])->andWhere(['>=', 'general_voucher.date_transact', $post['date_from']])->andWhere(['<=', 'general_voucher.date_transact', $post['date_to']]);
    		
    		
    		
    		
    		if($post['name']!=='')
    			$modelx = $modelx->andWhere(['like', 'general_voucher.name', $post['name'].'%', false]);
    			
    			
    			
    			$modelx = $modelx->orderBy([
    					'general_voucher.date_transact' => SORT_ASC
    			])->asArray()->all();
    			
    			
    			return $modelx;
    	} catch (Exception $e) {
    		
    		return $e->getMessage();
    	}
    }
    

    public function actionPostVoucher($id)
    {
        VoucherHelper::postVoucher($id);
    }

    public function actionViewParticulars(){
        $this->layout = 'main-vue';

        $filter  = ['category' => ['LOAN', 'SAVINGS', 'SHARE', 'TIME_DEPOSIT', 'OTHERS']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);
        $memberList = MemberHelper::getMemberList(null, true);

        $pageData = [
            'particularList' => $getParticular,
            'memberList' => $memberList
        ];

        return $this->render('view-particular', [
            'pageData'    => $pageData
        ]);
    }

    public function actionGetVoucherParticular(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $filter = $post;
            $paymentRecordList = VoucherHelper::getVoucherParticular($filter);

            return ['data' => $paymentRecordList];
        }
    }

    //add permission on action

    public function actionCancelVoucher($id){

        VoucherHelper::unpostVoucher($id);

    }
}
