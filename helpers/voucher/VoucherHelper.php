<?php

namespace app\helpers\voucher;

use Yii;
use app\models\GeneralVoucher;
use app\models\voucherDetails;

class VoucherHelper 
{
    public static function saveVoucher($data){
        $voucher = new GeneralVoucher;
        $voucher->gv_num = $data['gv_num'];
        $voucher->name = $data['name'];
        $voucher->type = $data['type'];
        $voucher->date_transact = $data['date_transact'];
        $voucher->created_date = date('Y-m-d G:i:s');
        $voucher->created_by = \Yii::$app->user->identity->id;

        if($voucher->save()){
            return $voucher;
        }
        /*else{
            var_dump($voucher->getErrors());
        }*/
        return null;
    }

	public static function insertEntries($list, $voucher_id, $category_type){
        foreach ($list as $key => $value) {
            $voucher = new VoucherDetails;
            $voucher->voucher_id = $voucher_id;
            $voucher->category_type = $category_type;
            $voucher->member_id = $value['member_id'];
            $voucher->particular_id = $value['particular_id'];
            $voucher->debit = $value['debit'];
            $voucher->credit = $value['credit'];

            $voucher->save();
        }
	}

    public static function getVoucherList($filter = null, $limit = null){

        $voucherList = \app\models\VoucherDetails::find()->joinWith(['particular']);

        if($filter &&is_array($filter)){
            $where = [];
            foreach ($filter as $key => $value) {
                $where[$key] = $value;
            }

            if(count($where) > 0){
                $voucherList = $voucherList->where($where);
            }
        }

        if($limit){
            $voucherList = $voucherList->limit(100);
        }

        $voucherList = $voucherList->orderBy('id DESC')->asArray()->all();

        return $voucherList;

    }
}