<?php

namespace app\helpers\payment;

use Yii;
use \app\models\PaymentRecord;
use \app\models\PaymentRecordList;
use \app\models\LoanProduct;
use \app\models\LoanAccount;
use \app\models\LoanTransaction;
use \app\models\JournalHeader;
use \app\models\SavingAccounts;
use \app\models\SavingsTransaction;
use \app\models\Savingsproduct;
use \app\models\ShareProduct;
use \app\models\TimeDepositProduct;
use \app\models\AccountParticulars;

use app\helpers\journal\JournalHelper;

class PaymentHelper 
{
	public $entry_type = ['SAVINGS' => 'CREDIT', 'SHARE' => 'CREDIT', 'OTHERS' => 'DEBIT',  'LOAN' => 'DEBIT', 'TIME_DEPOSIT' => 'CREDIT'];

	public static function savePayment($data){
        $payment = new PaymentRecord;
      
        $payment->date_transact = $data['date_transact'];
        $payment->or_num = $data['or_num'];
        $payment->name = $data['name'];
        $payment->type = $data['type'];
        $payment->posting_code = $data['posting_code'];
        $payment->check_number = $data['check_number'];
        $payment->amount_paid = $data['amount_paid'];
        $payment->created_date = \Yii::$app->user->identity->DateTimeNow;
        $payment->created_by = \Yii::$app->user->identity->id;
        //$payment->created_by = 18; //CINCO

        if($payment->save()){
            return $payment;
        }
        /*else{
            var_dump($voucher->getErrors());
        }*/
        //return $payment->getErrors();
        return false;
    }

    public static function insertAccount($list, $payment_record_id){
    	$success = true;
        foreach ($list as $key => $value) {
            $payment = new PaymentRecordList;
            $payment->payment_record_id = $payment_record_id;
            $payment->type = $value['type'];
            $payment->amount = $value['amount'];
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
	
	//posting of payment
	public static function postPayment($ref_id)
	{
		try {
			
			$success = true;
			$transaction = \Yii::$app->db->beginTransaction();
			
			$dateToday = date('Y-m-d');
			$payments = PaymentRecordList::findAll(['payment_record_id'=>$ref_id]);
			$paymentHeader = PaymentRecord::findOne(['id'=>$ref_id]);
			
			
			//2. prepare header for accounting entry
			$journalheader['reference_no'] = $paymentHeader->or_num;
			$journalheader['posting_date'] = date('Y-m-d');
			$journalheader['total_amount'] = 0;
			$journalheader['remarks'] = "Payment made by ".$paymentHeader->name;
			$journalheader['trans_type'] = 'Payment';
			
			
			
			$journaldetails = [];
			
			foreach ($payments as $row)
			{
				/*
				 * rules for payment
				 * 0. identify deductions
				 * 	0.1 identify interest
				 * 1. insert to payment transaction
				 * 		->portion of payment due to P.I (Prepaid Interest)
				 * 		->identify accumulated interest from last transaction, disbursed date if none.
				 * 2. insert transaction to accounting entry
				 * 3. update loan balance
				 */
				
				
				//processing rule no. 1, identify loan product parameters
				
				echo $row['type'].'<br/>';
				
				
				if($row['type']=='LOAN')
				{
					$product = LoanProduct::findOne($row['product_id']);
					$account = LoanAccount::findOne($row['account_no']);
					
					
					//$prepaid_interest_pay = $product->prepaid_monthly_interest==1 ? $account->principal * ($product->prepaid_interest/100) : 0;
					
					
					$connection = Yii::$app->getDb();
					$command = $connection->createCommand("
				    select ifnull((select date_posted FROM `loan_transaction` where loan_account=:accountnumber and left(transaction_type, 3)='PAY' AND is_cancelled=0 order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber limit 1)) as lasttrandate", [':accountnumber' => $row['account_no']]);
					$lastTransaction = $command->queryOne();
					
					echo $lastTransaction['lasttrandate']." | <br/>-";
					$noOfDaysPassed = date_diff(date_create(date('Y-m-d')), date_create($lastTransaction['lasttrandate']));
					
					$noOfDaysPassed = $noOfDaysPassed->format("%a");
					
					
					//0. identifying deductions or payment distributions, if loan is appliance or regular loan, mothly prepaid interest should be paid.
					$prepaidInterest = 0;
					
					if($product->id == 1 || $product->id == 2)
					{
						$command = $connection->createCommand("SELECT ifnull((SELECT sum(prepaid_intpaid) FROM `loan_transaction` where
							loan_account=:accountnumber and left(transaction_type,3)='PAY'), 0) - ifnull((SELECT sum(prepaid_intpaid) FROM `loan_transaction` where
							loan_account=:accountnumber and left(transaction_type,2)='CN'), 0) AS totalPrepaidPaid", [':accountnumber' => $row['account_no']]);
						$totalPrepaidPaid = $command->queryOne();
						
						$PIMustPay = ($noOfDaysPassed / 15) * $account->prepaid_amortization_quincena;
						
						$accumulatedPrepaid = $PIMustPay - $totalPrepaidPaid['totalPrepaidPaid'];
						$prepaidInterest= $accumulatedPrepaid < 0 ? 0 : $accumulatedPrepaid;
						
						
					}
					
					echo "i am interest prepaid .. ".$prepaidInterest." | <br/>";
					
					
					$principal_pay = $row['amount'] - $prepaidInterest;
					
					
					
					$interestEarned = ($account->principal_balance * ($product->int_rate/100))/30;
					$interestEarned = $interestEarned * $noOfDaysPassed;
					
					
					
					//1. insert to payment transaction
					$loanTransaction = new LoanTransaction();
					$loanTransaction->loan_account = $row['account_no'];
					$loanTransaction->amount = round($row['amount'], 2);
					$loanTransaction->transaction_type='PAYPARTIAL';
					$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
					$loanTransaction->transaction_date = date('Y-m-d');
					$loanTransaction->running_balance = round($account->principal_balance - $principal_pay, 2);
					$loanTransaction->remarks="payment thru payment facility";
					$loanTransaction->prepaid_intpaid = round($prepaidInterest, 2);
					$loanTransaction->interest_paid = 0;
					$loanTransaction->OR_no= $paymentHeader->or_num;
					$loanTransaction->principal_paid = round($principal_pay, 2);
					$loanTransaction->arrears_paid = 0;
					$loanTransaction->date_posted = date('Y-m-d');
					$loanTransaction->interest_earned = round($interestEarned, 2);
					
					$account->principal_balance = $loanTransaction->running_balance;
					$account->interest_balance = $account->interest_balance + $interestEarned;
					
					if($loanTransaction->running_balance<=0)
					{
						$success = false;
						$transaction->rollBack();
						return "achieved negative. might want to proceed to close payment.";
						break;
						
					}
					
					//3. update loan balance
					if($loanTransaction->save() && $account->save())
					{

						//accounting entry goes here...
						
						//debit part
						array_push($journaldetails, [
								'amount' => $loanTransaction->prepaid_intpaid + $loanTransaction->principal_paid + $loanTransaction->arrears_paid + $loanTransaction->interest_paid,
								'entry_type'=>'DEBIT',
								'particular_id'=>67 //67 is cash on hand
						]);
						
				
						//credit part		
						if($loanTransaction->prepaid_intpaid>0)
						{
							
							array_push($journaldetails, [
									'amount'=> $loanTransaction->prepaid_intpaid,
									'entry_type' => 'CREDIT',
									'particular_id' => $product->pi_particular_id
							]);
							
						}
						
						if($loanTransaction->principal_paid>0)
						{
							
							array_push($journaldetails, [
									'amount'=> $loanTransaction->principal_paid,
									'entry_type' => 'CREDIT',
									'particular_id' => $product->particular_id
							]);
							
						}
						
						
						if($loanTransaction->interest_paid>0)
						{
							
							array_push($journaldetails, [
									'amount'=> $loanTransaction->interest_paid,
									'entry_type' => 'CREDIT',
									'particular_id' => $product->int_particular_id
							]);
							
						}
						
						
						
						
					}
					
					else
					{
						echo var_dump($loanTransaction->errors);
						$success = false;
						break;
					}
					
					
					
					
				}
				
				
				else if($row['type']=='SAVINGS')
				{
					$savingsaccount = SavingAccounts::findOne(['account_no'=>$row['account_no']]);
					$savingstransaction = new SavingsTransaction();
					$savingsproduct = Savingsproduct::findOne($savingsaccount->saving_product_id);
					
					
					$savingstransaction->fk_savings_id = $row['account_no'];
					$savingstransaction->amount = $row['amount'];
					$savingstransaction->transaction_type = 'CASHDEP';
					$savingstransaction->transacted_by = \Yii::$app->user->identity->id;
					$savingstransaction->transaction_date = date('Y-m-d H:i:s');
					$savingstransaction->running_balance = $savingsaccount->balance + $row['amount'];
					$savingstransaction->remarks = "posted as Payment from ".$paymentHeader->or_num;
					$savingstransaction->ref_no = $paymentHeader->or_num;
					
					$savingsaccount->balance = $savingsaccount->balance + $row['amount'];
					
					if($savingsaccount->save() && $savingstransaction->save())
					{
						array_push($journaldetails, [
								'amount'=> $savingstransaction->amount,
								'entry_type' => 'DEBIT',
								'particular_id' => 67
						]);
						
						
						array_push($journaldetails, [
								'amount'=> $savingstransaction->amount,
								'entry_type' => 'CREDIT',
								'particular_id' => $savingsproduct->particular_id
						]);
						
						
					}
					
					else
					{
						$success = false;
						break;
					}
				 
					
					
				}
				
				
				
				
				
				
			}
			
			//post to journal entry
			if(JournalHelper::saveJournalHeader($journalheader) != null && JournalHelper::insertJournal($journaldetails,$paymentHeader->or_num))
			{
				
			}
			
			else $success = false;
			
			
			
			
			
			if($success)
			{
				$transaction->commit();
				echo "<br/>saved";
			}
			
			else {$transaction->rollBack();
			echo "<br/>UNsaved";
			}
			
			
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
		
		
				
	}
	
	public static function getPaymentList($payment_record_id){
		$accountList = PaymentRecordList::find()->joinWith(['member']);
        if($payment_record_id != null){
            $accountList = $accountList->where(['payment_record_id' => $payment_record_id]);
        }
        
        return $accountList->asArray()->all();
	}

	public static function getPaymentProduct($type, $id){
		$product = [];
		if($type == "SAVINGS"){
			$getModel = Savingsproduct::findOne($id);
			if($getModel){
				$product['id'] = $getModel->id;
				$product['name'] = $getModel->description;
			}
		}

		else if($type == "SHARE"){
			$getModel = ShareProduct::findOne($id);
			if($getModel){
				$product['id'] = $getModel->id;
				$product['name'] = $getModel->name;
			}
		}

		else if($type == "TIME_DEPOSIT"){
			$getModel = TimeDepositProduct::findOne($id);
			if($getModel){
				$product['id'] = $getModel->id;
				$product['name'] = $getModel->description;
			}
		}

		else if($type == "LOAN"){
			$getModel = LoanProduct::findOne($id);
			if($getModel){
				$product['id'] = $getModel->id;
				$product['name'] = $getModel->product_name;
			}
		}

		else if($type == "OTHERS"){
			$getModel = AccountParticulars::findOne($id);
			if($getModel){
				$product['id'] = $getModel->id;
				$product['name'] = $getModel->name;
			}
		}

		return $product;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

   
}