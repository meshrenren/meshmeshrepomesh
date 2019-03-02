<?php

namespace app\controllers;

use app\helpers\particulars\ParticularHelper;
use app\helpers\voucher\VoucherHelper;

class GeneralVoucherController extends \yii\web\Controller
{
    public function actionIndex()
    {
    	$this->layout = 'main-vue';
    	$voucher = new \app\models\GeneralVoucher;
        $voucherModel = $voucher->attributes();

        $filter  = ['category' => ['savings', 'share', 'time_deposit', 'others']];
        $getParticular = ParticularHelper::getParticulars($filter);

        return $this->render('index', [
        	'model'         => $voucherModel,
            'particularList'   => $getParticular
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

            $post = \Yii::$app->getRequest()->getBodyParams();
            $entryList = $post['voucherList'];
            $gvNumber = $post['gvNumber'];
            $forceAdd = $post['forceAdd'];
            $voucherIds = [];
            $hasError = false;
            if(!$forceAdd){
                $getVoucher  = \app\models\GeneralVoucher::find()->where(['gv_num' => $gvNumber])->one();
                if($getVoucher){
                     return [
                        'error'     => 'has_gvnum',
                        'hasError'  => true
                    ];
                }
               
            }
            $error = [];
            foreach ($entryList as $entry) {
                $createEntry  = new \app\models\GeneralVoucher;
                if(isset($entry['name_id'])){
                    unset($entry['name_id']);
                }
                $createEntry->attributes = $entry;
                if($createEntry->save()){
                    array_push($voucherIds, $createEntry->id);
                }
                else{
                    array_push($error, $createEntry->getErrors()) ;
                    $hasError = true;
                }
            }

            return [
                'voucherIds'    => $voucherIds,
                'hasError'      => $hasError,
                'error'         => $error
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

        $getVouchers  = \app\models\GeneralVoucher::find()->asArray()->all();
        return [
            'data' => $getVouchers
        ];
    }

}
