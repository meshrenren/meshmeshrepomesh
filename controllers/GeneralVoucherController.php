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

        $details = new \app\models\VoucherDetails;
        $detailsModel = $details->attributes();

        $getParticular = ParticularHelper::getParticulars();

        return $this->render('index', [
        	'voucherModel'      => $voucherModel,
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
            $post = \Yii::$app->getRequest()->getBodyParams();
            $voucherModel = $post['voucherModel'];
            $entryList = $post['entryList'];
            $success = false;
            $error = '';
            $data = null;

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
                $saveGV = VoucherHelper::saveVoucher($voucherModel);
                if($saveGV){
                    //Entries
                    VoucherHelper::insertEntries($entryList, $saveGV->id, 'OTHERS');
                    $success = true;
                }
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

        $getVouchers  = \app\models\GeneralVoucher::find()->asArray()->all();
        return [
            'data' => $getVouchers
        ];
    }

}
