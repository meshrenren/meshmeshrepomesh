<?php

namespace app\helpers\payment;

use Yii;
use app\models\PaymentRecord;
use app\models\PaymentRecordList;

class PaymentHelper 
{
	public static function savePayment($data){
        $payment = new PaymentRecord;
        $payment->date_transact = $data['date_transact'];
        $payment->or_num = $data['or_num'];
        $payment->name = $data['name'];
        $payment->type = $data['type'];
        $payment->posting_code = $data['posting_code'];
        $payment->check_number = $data['check_number'];
        $payment->created_date = date('Y-m-d G:i:s');
        $payment->created_by = \Yii::$app->user->identity->id;

        if($payment->save()){
            return $payment;
        }
        /*else{
            var_dump($voucher->getErrors());
        }*/
        return null;
    }

    public static function insertAccount($list, $payment_record_id){
    	$success = true;
        foreach ($list as $key => $value) {
            $payment = new PaymentRecordList;
            $payment->payment_record_id = $payment_record_id;
            //$payment->type = $value['type'];
            $payment->amount = $value['amount'];
            $payment->entry_type = $value['entry_type'];
            $payment->member_id = $value['member_id'];
            if($value['type'] == "OTHERS"){
            	$payment->particular_id = $value['particular_id'];
            }
            else{
	            $payment->product_id = $value['product_id'];
	            $payment->account_no = $value['account_no'];
            }

            if(!$payment->save()){
            	$success = false;
            }
        }

        return $success;
	}

   
}