<?php

namespace app\helpers\payment;

use Yii;
use app\models\PaymentRecord;
use app\models\PaymentRecordList;

class PaymentHelper 
{
	public $entry_type = ['SAVINGS' => 'CREDIT', 'SHARE' => 'CREDIT', 'OTHERS' => 'DEBIT',  'LOAN' => 'DEBIT', 'TIME_DEPOSIT' => 'DEBIT'];
	public static function savePayment($data){
        $payment = new PaymentRecord;
      
        $payment->date_transact = $data['date_transact'];
        $payment->or_num = $data['or_num'];
        $payment->name = $data['name'];
        $payment->type = $data['type'];
        $payment->posting_code = $data['posting_code'];
        $payment->check_number = $data['check_number'];
        $payment->created_date = date('Y-m-d H:i:s');
        $payment->created_by = \Yii::$app->user->identity->id;

        if($payment->save()){
            return $payment;
        }
        /*else{
            var_dump($voucher->getErrors());
        }*/
        return $payment->errors;
    }

    public static function insertAccount($list, $payment_record_id){
    	$success = true;
        foreach ($list as $key => $value) {
            $payment = new PaymentRecordList;
            $payment->payment_record_id = $payment_record_id;
            $payment->type = $value['type'];
            $payment->amount = $value['amount'];
            $payment->entry_type = "CREDIT";
            $payment->member_id = $value['member_id'];
            if($value['type'] == "OTHERS"){
            	$payment->particular_id = $value['particular_id'];
            }
            else{
	            $payment->product_id = $value['product_id'];
	            $payment->account_no = $value['account_no'];
            }

            if(!$payment->save()){
            	return $payment->errors;
            }
        }

        return $success;
	}
	
	//posting of payment
	public static function postPayment($ref_id)
	{
		$success = false;
		//$transaction = \Yii::$app->db->beginTransaction();
		
		$dateToday = date('Y-m-d');
		$payments = PaymentRecordList::findAll(['payment_record_id'=>$ref_id]);
		
		foreach ($payments as $row)
		{
			/*
			 * rules for payment
			 * 1. insert to payment transaction
			 * 		->portion of payment due to P.I (Prepaid Interest)
			 * 		->identify accumulated interest from last transaction, disbursed date if none.
			 * 2. insert transaction to accounting entry
			 * 3. update loan balance
			 */
			
			
			//processing rule no. 1, identify loan product parameters
			
			
			echo $row['type'].'<br/>';
			
			
			
			
			
			
		}
		
		
				
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

   
}