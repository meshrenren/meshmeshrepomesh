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
use \app\models\Shareaccount;
use \app\models\ShareTransaction;



use app\helpers\journal\JournalHelper;
use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\SavingsHelper;

class PaymentHelper 
{
	public $entry_type = ['SAVINGS' => 'CREDIT', 'SHARE' => 'CREDIT', 'OTHERS' => 'DEBIT',  'LOAN' => 'DEBIT', 'TIME_DEPOSIT' => 'CREDIT'];

	public static function savePayment($data){
        $payment = new PaymentRecord;

        if(isset($data['id'])){
        	$payment = PaymentRecord::findOne($data['id']);
        }
      	
        $payment->date_transact = $data['date_transact'];
        $payment->or_num = $data['or_num'];
        $payment->name = $data['name'];
        $payment->type = $data['type'];
        $payment->posting_code = $data['posting_code'];
        $payment->check_number = $data['check_number'];
        $payment->amount_paid = $data['amount_paid'];
        $payment->created_date = date('Y-m-d H:i:s');;
        $payment->created_by = isset(\Yii::$app->user) && isset(\Yii::$app->user->identity) ? \Yii::$app->user->identity->id : 18;
        //$payment->created_by = 18; //CINCO
        if(isset($data['posted_date'])){
        	$payment->posted_date = $data['posted_date'];
        }

        if($payment->save()){
            return $payment;
        }
        else{
            var_dump($voucher->getErrors());
        }
        //return $payment->getErrors();
        return false;
    }

    public static function insertAccount($list, $payment_record_id, $posted_date = null){
    	$success = true;
    	$paymentModel = PaymentRecord::findOne($payment_record_id);
        foreach ($list as $key => $value) {
            $payment = new PaymentRecordList;
            $payment->payment_record_id = $payment_record_id;
            $payment->or_num = isset($value['or_num']) ? $value['or_num'] :  $paymentModel ? $paymentModel->or_num : null;
            $payment->type = $value['type'];
            $payment->amount = $value['amount'];
            $payment->member_id = $value['member_id'];
            $payment->name = isset($value['name']) ? $value['name'] : "";
            if(isset($value['particular_id'])){
            	$payment->particular_id = $value['particular_id'];
            }

            if(isset($value['product_id'])){
            	$payment->product_id = $value['product_id'];
            }

            if(isset($value['account_no'])){
            	$payment->account_no = $value['account_no'];
            }

            if(isset($value['is_prepaid'])){
            	$payment->is_prepaid = $value['is_prepaid'] === 1|| $value['is_prepaid'] === "1" || $value['is_prepaid'] === true || $value['is_prepaid'] === "true" ? 1 : 0;
            }

            if(isset($value['remarks'])){
            	$payment->remarks = $value['remarks'];
            }

            if($posted_date){
                $voucher->posted_date = $posted_date;
            }

            if(!$payment->save()){
            	$success = false;
            }
        }
        return $success;
	}

	public static function getPrepaid($list, $payment){
		$getPrepaid = null;

		$getPrepaidList = array_filter( $list,
            function ($e) use ($payment) {
                return $e->is_prepaid === 1 && $e->account_no == $payment['account_no'];
            }
        );
        
        if(count($getPrepaidList) > 0){
        	foreach ($getPrepaidList as $pyt) {
				if($pyt->account_no == $payment['account_no']){
					$getPrepaid = $pyt;
				}
			}
        }

        return $getPrepaid;
		
	}
	
	//posting of payment
	public static function postPayment($ref_id)
	{
		try {

			
			$success = true;
			$transaction = \Yii::$app->db->beginTransaction();
			
			$dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
			$payments = PaymentRecordList::find()->joinWith(['member'])->where(['payment_record_id'=>$ref_id])->all();
			$paymentHeader = PaymentRecord::findOne(['id'=>$ref_id]);

			$coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);//Cash on Hand particular id
			$cashOnHandId = $coh_id->id;
			
			//2. prepare header for accounting entry
			$posted_date = $dateToday;
			$transaction_date = $paymentHeader->date_transact;

			$journalheader['reference_no'] = $paymentHeader->or_num;
			$journalheader['posting_date'] = $posted_date;
			$journalheader['total_amount'] = 0;
			$journalheader['remarks'] = "Payment made by ".$paymentHeader->name;
			$journalheader['trans_type'] = 'Payment';
			
			if($paymentHeader->posted_date){
				echo "Payment with OR Number " . $paymentHeader->or_num . ' for ' . $paymentHeader->name . ' is already posted.<br/>';
				echo "<h3> Please close the window </h3>";
				die;
			}
			
			
			$journaldetails = [];
			
			$debitCOH = 0;
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
				$memberName = $row['member'] ? $row['member']['fullname'] : "";
				
				echo "Posting " . $row['type'].' for ' . $memberName . ' ......<br/>';
				
				
				if($row['type']=='LOAN')
				{
					//If prepaid paid skip as it will be included on adding the loan
					if($row['is_prepaid'] === 1){
						continue;
					}

					$product = LoanProduct::findOne($row['product_id']);
					$account = LoanAccount::findOne($row['account_no']);

					$isNewLoanPolicy = false;
					//New policy was updates. Eg. No prepaid monthly for Applicance and interest earned calculcation
                	$calVersion = Yii::$app->view->getVersion($account['release_date']);
                	if($calVersion !== "1"){
                		$isNewLoanPolicy = true;
                	}
					
					
					//$prepaid_interest_pay = $product->prepaid_monthly_interest==1 ? $account->principal * ($product->prepaid_interest/100) : 0;
					
					
					$connection = Yii::$app->getDb();
					$command = $connection->createCommand("
				    select ifnull((select date_posted FROM `loan_transaction` where loan_account=:accountnumber and left(transaction_type, 3)='PAY' AND is_cancelled=0 order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber limit 1)) as lasttrandate", [':accountnumber' => $row['account_no']]);
					$lastTransaction = $command->queryOne();
					
					echo $lastTransaction['lasttrandate']." | <br/>-";
					$noOfDaysPassed = date_diff(date_create($dateToday), date_create($lastTransaction['lasttrandate']));
					
					$noOfDaysPassed = $noOfDaysPassed->format("%a");
					
					
					//0. identifying deductions or payment distributions, if loan is appliance or regular loan, mothly prepaid interest should be paid.
					$prepaidInterest = 0;
					$amount = $row['amount'];

					if($product->id == 1 || ($product->id == 2 && !$isNewLoanPolicy)) //No quiencena prepaid for appliance loan 
					{
						//Get prepaid from the payment
						$getPrepaid = static::getPrepaid($payments, $row);
						if($getPrepaid){
							$prepaidInterest = $getPrepaid['amount'] < 0 ? 0 : $getPrepaid['amount'];
							$amount += $prepaidInterest;
						}
						
						
					}
					echo "i am interest prepaid .. ".$prepaidInterest." | <br/>";
					$principal_pay = $row['amount']/* - $prepaidInterest*/;
					
					$interestEarned = 0;
					if($product->id == 1){//Regular loan base on diminishing amount
						$interestEarned = ($account->principal_balance * ($product->int_rate/100))/30;
						$interestEarned = $interestEarned * $noOfDaysPassed;
					}
					else{
						//New policy has no add in. So interest earned directly base on amount paid and interest rate
						if($isNewLoanPolicy){
							if($product->int_rate > 0){//Other policy will base on amount paid
								$interestEarned = ($row['amount'] * ($product->int_rate/100));
							}
						}
						else{
							if($product->int_rate > 0){//Other policy will base on amount paid
								$interestEarned = ($row['amount'] * ($product->int_rate/100));
							}
						}
						
					}
					
					
					//1. insert to payment transaction
					$loanTransaction = new LoanTransaction();
					$loanTransaction->loan_account = $row['account_no'];
					//$loanTransaction->loan_id = $product->id;
					//$loanTransaction->member_id = $account->member_id;
					echo "Principal Bal => " . $account->principal_balance . "<br>";
					$running_balance = round($account->principal_balance - $principal_pay, 2);
					$asSavings = 0;
					if($running_balance < 0){
						$asSavings = $principal_pay - $account->principal_balance;
						$running_balance = 0;
						$principal_pay = $account->principal_balance;
						$amount = $amount - $asSavings;
					}

					$loanTransaction->amount = round($amount, 2);
					$loanTransaction->transaction_type='PAYPARTIAL';
					$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
					$loanTransaction->transaction_date = $transaction_date;
					$loanTransaction->running_balance = $running_balance;
					$loanTransaction->remarks="payment thru payment facility";
					$loanTransaction->prepaid_intpaid = round($prepaidInterest, 2);
					$loanTransaction->interest_paid = 0;
					$loanTransaction->OR_no= $paymentHeader->or_num;
					$loanTransaction->principal_paid = round($principal_pay, 2);
					$loanTransaction->arrears_paid = 0;
					$loanTransaction->date_posted = $dateToday;
					$loanTransaction->interest_earned = round($interestEarned, 2);
					
					$account->principal_balance = $loanTransaction->running_balance;
					$account->interest_balance = $account->interest_balance + $interestEarned;
					$account->interest_accum = $account->interest_accum + $interestEarned;

					if($asSavings > 0){
						//Save as savings
						echo "Add As Savings => " . $asSavings . "<br>";
						echo "Principal Pay => " . $principal_pay . "<br>";
						$savingsaccount = SavingsHelper::getMemberSavings($row['member_id'], false);
						if($savingsaccount){
							$savingstransaction = new SavingsTransaction();
							$savingsproduct = Savingsproduct::findOne($savingsaccount->saving_product_id);
							
							$pro_name = "LOAN";
							$getLoanProd = LoanProduct::findOne($account->loan_id);
							if($getLoanProd){
								$pro_name = $getLoanProd->product_name;
							}

							$savingstransaction->fk_savings_id = $savingsaccount->account_no;
							$savingstransaction->amount = $asSavings;
							$savingstransaction->transaction_type = 'CASHDEP';
							$savingstransaction->transacted_by = \Yii::$app->user->identity->id;

							$savingstransaction->transaction_date = date('Y-m-d H:i:s', strtotime($transaction_date));
							$savingstransaction->posted_date = date('Y-m-d', strtotime($dateToday));

							$savingstransaction->running_balance = $savingsaccount->balance + $asSavings;
							$savingstransaction->remarks = "From " .$pro_name. " payment. Posted as Payment from ".$paymentHeader->or_num;
							$savingstransaction->ref_no = $paymentHeader->or_num;
							
							$savingsaccount->balance = $savingsaccount->balance + $asSavings;
							
							if($savingsaccount->save() && $savingstransaction->save())
							{
								$debitCOH +=  floatval($savingstransaction->amount);
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
						}else{
							echo "No savings account found" . "<br>";
							$success = false;
							break;
						}
						
					}
					
					//Account will close
					/*if($loanTransaction->running_balance<=0)
					{
						$success = false;
						$transaction->rollBack();

						echo "Achieved negative on prinicipal balance. Please check amount. ->" . $loanTransaction->running_balance;
						break;
						
					}*/
					
					//3. update loan balance
					if($loanTransaction->save() && $account->save())
					{

						//accounting entry goes here...
						
						//debit part
						/*array_push($journaldetails, [
								'amount' => $loanTransaction->prepaid_intpaid + $loanTransaction->principal_paid + $loanTransaction->arrears_paid + $loanTransaction->interest_paid,
								'entry_type'=>'DEBIT',
								'particular_id'=>$cashOnHandId //cash on hand
						]);*/
						
						$debitCOH +=  floatval($loanTransaction->amount);

						array_push($journaldetails, [
								'amount'=> $loanTransaction->principal_paid,
								'entry_type' => 'CREDIT',
								'particular_id' => $product->particular_id
						]);

						if($loanTransaction->prepaid_intpaid>0)
						{
							array_push($journaldetails, [
									'amount'=> $loanTransaction->prepaid_intpaid,
									'entry_type' => 'CREDIT',
									'particular_id' => $product->pi_particular_id
							]);
							
						}

						//credit part		
						/*if($loanTransaction->prepaid_intpaid>0)
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
							
						}*/
						
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
					
					$remarks = isset($row['remarks']) ? $row['remarks'] . ". " : "";
					$remarks .= "Posted as Payment from ".$paymentHeader->or_num;
					$savingstransaction->fk_savings_id = $row['account_no'];
					$savingstransaction->amount = $row['amount'];
					$savingstransaction->transaction_type = 'CASHDEP';
					$savingstransaction->transacted_by = \Yii::$app->user->identity->id;
					
					$savingstransaction->transaction_date = date('Y-m-d H:i:s', strtotime($transaction_date));
					$savingstransaction->posted_date = date('Y-m-d', strtotime($dateToday));

					$savingstransaction->running_balance = $savingsaccount->balance + $row['amount'];
					$savingstransaction->remarks = $remarks;
					$savingstransaction->ref_no = $paymentHeader->or_num;
					
					$savingsaccount->balance = $savingsaccount->balance + $row['amount'];
					
					if($savingsaccount->save() && $savingstransaction->save())
					{
						$debitCOH +=  floatval($savingstransaction->amount);
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

				else if($row['type']=='SHARE')
				{
					$shareaccount = Shareaccount::findOne(['accountnumber'=>$row['account_no']]);
					$sharetransaction = new ShareTransaction();
					$shareproduct = ShareProduct::findOne($shareaccount->fk_share_product);
					
					
					$sharetransaction->fk_share_id = $row['account_no'];
					$sharetransaction->amount = $row['amount'];
					$sharetransaction->transaction_type = 'CASHDEP';
					$sharetransaction->transacted_by = \Yii::$app->user->identity->id;
					$sharetransaction->transaction_date = date('Y-m-d H:i:s', strtotime($dateToday));
					$sharetransaction->running_balance = $shareaccount->balance + $row['amount'];
					$sharetransaction->remarks = "posted as Payment from ".$paymentHeader->or_num;
					$sharetransaction->reference_number = $paymentHeader->or_num;
					
					$shareaccount->balance = $shareaccount->balance + $row['amount'];
					
					if($shareaccount->save() && $sharetransaction->save())
					{
						/*array_push($journaldetails, [
								'amount'=> $sharetransaction->amount,
								'entry_type' => 'DEBIT',
								'particular_id' => $cashOnHandId
						]);*/
						
						
						$debitCOH +=  floatval($sharetransaction->amount);
						array_push($journaldetails, [
								'amount'=> $sharetransaction->amount,
								'entry_type' => 'CREDIT',
								'particular_id' => $shareproduct->particular_id
						]);
					}
					
					else
					{
						$success = false;
						break;
					}	 
					
					
				}
				else{
					/*array_push($journaldetails, [
							'amount'=> $row['amount'],
							'entry_type' => 'DEBIT',
							'particular_id' => $cashOnHandId
					]);*/
					
					$debitCOH +=  floatval($row['amount']);
					array_push($journaldetails, [
							'amount'=> $row['amount'],
							'entry_type' => 'CREDIT',
							'particular_id' => $row['particular_id'],
					]);
				}
				
				
			}

			if($success){
				//debit part
				array_push($journaldetails, [
						'amount' => $debitCOH,
						'entry_type'=>'DEBIT',
						'particular_id'=>$cashOnHandId //cash on hand
				]);
				$journalheader['total_amount'] = $debitCOH;
				
				//post to journal entry
				if(JournalHelper::saveJournalHeader($journalheader) != null && JournalHelper::insertJournal($journaldetails,$paymentHeader->or_num))
				{
					$success = true;
				}
				
				else $success = false;
			}


			if($success){
				$paymentHeader->posted_date = $posted_date;
				$paymentHeader->save();

				$transaction->commit();
				//$transaction->rollBack(); // Rollback for now
				echo "<br/><h3>Saved</h3>";
			}
			
			else {
				$transaction->rollBack();
				echo "<br/><h3>Unsaved. Please contact admin or the developer</h3>";
			}

			echo "<br/><h3>Close Window.</h3>";
			
			
		} catch (\Exception $e) {
			var_dump($e);
			echo $e->getMessage();
		}
		
		
				
	}
	
	public static function getPaymentList($payment_record_id){
		$accountList = PaymentRecordList::find()->joinWith(['member', 'particular']);
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

	
	
	public static function getCurrentInterest($accountnumber, $interest_rate)
	{
		try {
			$account = LoanAccount::findOne($accountnumber);
			
			
			$connection = Yii::$app->getDb();
			$command = $connection->createCommand("
				    select ifnull((select date_posted FROM `loan_transaction` where loan_account=:accountnumber and left(transaction_type, 3)='PAY' AND is_cancelled=0 order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber limit 1)) as lasttrandate", [':accountnumber' => $accountnumber]);
			$lastTransaction = $command->queryOne();
			
			
			$noOfDaysPassed = date_diff(date_create(date('Y-m-d')), date_create($lastTransaction['lasttrandate']));
			
			$noOfDaysPassed = $noOfDaysPassed->format("%a");
			
			
			
			$interestEarned = ($account->principal_balance * ($interest_rate/100))/30;
			$interestEarned = $interestEarned * $noOfDaysPassed;
			
			return [
					'interest_earned' => $interestEarned,
					'noOfDaysPassed' => $noOfDaysPassed
			];
		} catch (\Exception $e) {
			
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	public static function unpostPayment($ref_id)
	{
		//echo 'wow';
		try {
			
			$success = true;
			$transaction = \Yii::$app->db->beginTransaction();
			
			$dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
			$paymentHeader = PaymentRecord::findOne(['or_num'=>$ref_id, 'is_cancelled'=>0]);
			
			if(!$paymentHeader){
				echo "Payment with OR Number maybe already cancelled or not found";
				echo "<h3> Please close the window </h3>";
				die;
			}
			
			
			$payments = PaymentRecordList::find()->joinWith(['member'])->where(['payment_record_id'=>$paymentHeader->id])->all();
			
			
			
			
			$coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);//Cash on Hand particular id
			$cashOnHandId = $coh_id->id;
			
			//2. prepare header for accounting entry
			$posted_date = date('Y-m-d');
			$journalheader['reference_no'] = "CN-".$paymentHeader->or_num;
			$journalheader['posting_date'] = $posted_date;
			$journalheader['total_amount'] = 0;
			$journalheader['remarks'] = "Payment Cancellation of ".$paymentHeader->name;
			$journalheader['trans_type'] = 'CancelBatchPayment';
			
			$journaldetails = [];
			
			
			
			foreach ($payments as $row)
			{
				$memberName = $row['member'] ? $row['member']['fullname'] : "";
				
				echo "Cancelling " . $row['type'].' for ' . $memberName . ' ......<br/>';
				
				if($row['type']=='LOAN')
				{
					
					$product = LoanProduct::findOne($row['product_id']);
					$account = LoanAccount::findOne($row['account_no']);
					
					//skip all the computations and go straight ahead to cancellation
					//be sure to get the latest transaction, cancellation MUST FAIL if the "to be" cancelled payment is NOT ALREADY THE LATEST OR LAST TRANSACTION
					$lastTransactionId = \app\models\LoanTransaction::find()->select('MAX(id) AS idcount')->where([
							'loan_account'=>$row['account_no'], 'is_cancelled'=> 0
					])->andWhere(['in', 'LEFT(transaction_type,3)', 'PAY'])->asArray()->one();
					
					
					$loanTransaction = \app\models\LoanTransaction::find()->where(
							['OR_no'=>$paymentHeader->or_num, 'loan_account'=>$row['account_no'], 'is_cancelled'=> 0,
									'id'=> $lastTransactionId['idcount']
							]
							)->andWhere(['in', 'LEFT(transaction_type,3)', 'PAY'])->one();
							
					if(!$loanTransaction)
					{
						echo "Payment for Loan with OR Number is UNCANCELLABLE";
						echo "<h3> Please close the window </h3>";
						die;
					}
					
					$loanTransaction->is_cancelled = 1;
					
					if(!$loanTransaction->save())
					{
						echo var_dump($loanTransaction->errors);
						$success = false;
						die;
					}
					
					//insert cancellation to loanTransaction
					$newloanTransaction = new LoanTransaction();
					$newloanTransaction->loan_account = $row['account_no'];
					$newloanTransaction->amount = round($row['amount'], 2) * -1;
					$newloanTransaction->transaction_type='CANPAYPARTIAL';
					$newloanTransaction->transacted_by = \Yii::$app->user->identity->id;
					$newloanTransaction->transaction_date = date('Y-m-d');
					$newloanTransaction->running_balance = round($account->principal_balance + $loanTransaction->principal_paid, 2);
					$newloanTransaction->remarks="cancelled thru payment cancellation facility";
					$newloanTransaction->prepaid_intpaid = 0;
					$newloanTransaction->interest_paid = 0;
					$newloanTransaction->OR_no= "CN-".$paymentHeader->or_num;
					$newloanTransaction->principal_paid = 0;
					$newloanTransaction->arrears_paid = 0;
					$newloanTransaction->date_posted = date('Y-m-d');
					$newloanTransaction->interest_earned = 0;
					
					if(!$newloanTransaction->save())
					{
						echo var_dump($newloanTransaction->errors);
						$success = false;
						die;
					}
					
					$account->principal_balance = $newloanTransaction->running_balance;
					$account->interest_balance = round($account->interest_balance + $loanTransaction->interest_earned, 2);
					$account->interest_accum = round($account->interest_accum+ $loanTransaction->interest_earned, 2);
					
					if(!$account->save())
					{
						echo var_dump($account->errors);
						$success = false;
						die;
					}
					
					
					
					
					
					array_push($journaldetails, [
							'amount' => $loanTransaction->prepaid_intpaid + $loanTransaction->principal_paid + $loanTransaction->arrears_paid + $loanTransaction->interest_paid,
							'entry_type'=>'CREDIT',
							'particular_id'=>$cashOnHandId //cash on hand
					]);
					
					//credit part, reverse to debit
					if($loanTransaction->prepaid_intpaid>0)
					{
						
						array_push($journaldetails, [
								'amount'=> $loanTransaction->prepaid_intpaid,
								'entry_type' => 'DEBIT',
								'particular_id' => $product->pi_particular_id
						]);
						
					}
					
					if($loanTransaction->principal_paid>0)
					{
						
						array_push($journaldetails, [
								'amount'=> $loanTransaction->principal_paid,
								'entry_type' => 'DEBIT',
								'particular_id' => $product->particular_id
						]);
						
					}
					
					
					if($loanTransaction->interest_paid>0)
					{
						
						array_push($journaldetails, [
								'amount'=> $loanTransaction->interest_paid,
								'entry_type' => 'DEBIT',
								'particular_id' => $product->int_particular_id
						]);
						
					}
					

				}
				
				
				else if($row['type']=='SAVINGS')
				{
					$savingsaccount = SavingAccounts::findOne(['account_no'=>$row['account_no']]);
					$savingsproduct = Savingsproduct::findOne($savingsaccount->saving_product_id);
					
					$lastSavingsTransactionId = \app\models\SavingsTransaction::find()->select('MAX(id) as idcount')->where([
							'fk_savings_id' => $row['account_no'], 'is_cancelled'=> 0
					])->andWhere(['not in', 'LEFT(transaction_type,3)', 'CAN'])->asArray()->one();
					
					
					
					$savingstransaction = \app\models\SavingsTransaction::find()->where(['fk_savings_id' => $row['account_no'], 'is_cancelled'=> 0,
							'id' => $lastSavingsTransactionId['idcount']
					])->andWhere(['not in', 'LEFT(transaction_type,3)', 'CAN'])->one();
					
					if(!$savingstransaction)
					{
						echo "Deposit for this Savings with OR Number is UNCANCELLABLE. Make sure this is the latest deposit or transaction made.";
						echo "<h3> Please close the window </h3>";
						die;
					}
					
					
					$savingstransaction->is_cancelled = 1;
					
					if(!$savingstransaction->save())
					{
						echo var_dump($savingstransaction->errors);
						$success = false;
						die;
					}
					
					$newsavingstransaction = new SavingsTransaction();
					
					$newsavingstransaction->fk_savings_id = $row['account_no'];
					$newsavingstransaction->amount = $row['amount'] * -1;
					$newsavingstransaction->transaction_type = 'CAN'.$savingstransaction->transaction_type;
					$newsavingstransaction->transacted_by = \Yii::$app->user->identity->id;
					$newsavingstransaction->transaction_date = date('Y-m-d H:i:s');
					$newsavingstransaction->running_balance = $savingsaccount->balance - $row['amount'];
					$newsavingstransaction->remarks = "posted as cancel deposit from ".$paymentHeader->or_num;
					$newsavingstransaction->ref_no = 'CN-'.$paymentHeader->or_num;
					
					if(!$newsavingstransaction->save())
					{
						echo var_dump($newsavingstransaction->errors);
						$success = false;
						die;
					}
					
					$savingsaccount->balance = $savingsaccount->balance - $row['amount'];
					
					if(!$savingsaccount->save())
					{
						echo var_dump($savingsaccount->errors);
						$success = false;
						die;
					}
					
					
					array_push($journaldetails, [
							'amount'=> $savingstransaction->amount,
							'entry_type' => 'CREDIT',
							'particular_id' => $cashOnHandId
					]);
					
					
					array_push($journaldetails, [
							'amount'=> $savingstransaction->amount,
							'entry_type' => 'DEBIT',
							'particular_id' => $savingsproduct->particular_id
					]);
					
					
					
					
				}
				
				
				else if($row['type']=='SHARE')
				{
					$shareaccount = \app\models\Shareaccount::findOne(['accountnumber'=>$row['account_no']]);
					$shareproduct = \app\models\ShareProduct::findOne($shareaccount->fk_share_product);
					
					$lastSharesTransactionId = \app\models\ShareTransaction::find()->select('MAX(id) as idcount')->where([
							'fk_share_id' => $row['account_no'], 'is_cancelled' => 0
					])->andWhere(['not in', 'LEFT(transaction_type,3)', 'CAN'])->asArray()->one();
					
					$sharetransaction = \app\models\ShareTransaction::find()->where(['fk_share_id' => $row['account_no'], 'is_cancelled' => 0])
					->andWhere(['not in', 'LEFT(transaction_type,3)', 'CAN'])->one();
					
					$sharetransaction->is_cancelled = 0;
					
					if(!$sharetransaction->save())
					{
						echo var_dump($sharetransaction->errors);
						$success = false;
						die;
					}
					
					$newsharetransaction = new \app\models\ShareTransaction();
					$newsharetransaction->fk_share_id = $row['account_no'];
					$newsharetransaction->amount = $row['amount'] * -1;
					$newsharetransaction->transaction_type = 'CAN'.$sharetransaction->transaction_type;
					$newsharetransaction->transacted_by = \Yii::$app->user->identity->id;
					$newsharetransaction->transaction_date = date('Y-m-d H:i:s');
					$newsharetransaction->running_balance = $shareaccount->balance - $row['amount'];
					$newsharetransaction->remarks = "posted as cancel deposit from ".$paymentHeader->or_num;
					$newsharetransaction->reference_number = 'CN-'.$paymentHeader->or_num;
					
					if(!$newsharetransaction->save())
					{
						echo var_dump($sharetransaction->errors);
						$success = false;
						die;
					}
					
					
					$shareaccount->balance = $shareaccount->balance - $row['amount'];
					
					if(!$shareaccount->save())
					{
						echo var_dump($shareaccount->errors);
						$success = false;
						die;
					}
					
					
					array_push($journaldetails, [
							'amount'=> $sharetransaction->amount,
							'entry_type' => 'CREDIT',
							'particular_id' => $cashOnHandId
					]);
					
					
					array_push($journaldetails, [
							'amount'=> $sharetransaction->amount,
							'entry_type' => 'DEBIT',
							'particular_id' => $shareproduct->particular_id
					]);
					
				}
				
				
			}
			
			//post to journal entry
			if(JournalHelper::saveJournalHeader($journalheader) != null && JournalHelper::insertJournal($journaldetails,$paymentHeader->or_num))
			{
				$success = true;
			}
			
			
			else $success = false;
			
			if($success){
				$paymentHeader->date_cancelled = $posted_date;
				$paymentHeader->is_cancelled = 1;
				$paymentHeader->save();
				
				$transaction->commit();
				//$transaction->rollBack(); // Rollback for now
				echo "<br/><h3>Saved</h3>";
			}
			
			else {
				$transaction->rollBack();
				echo "<br/><h3>Unsaved. Please contact admin or the developer</h3>";
			}
			
			echo "<br/><h3>Close Window.</h3>";
			
			
		} catch (\Exception $e) {
			var_dump($e);
			echo $e->getMessage();
		}
		
	}
	

	public static function getPayments($account_no, $type){
		$payment_record = PaymentRecordList::find()->innerJoinWith(['paymentRecord'])
			->select([ 'payment_record_list.*',
				'payment_record.posted_date'
			])
			->where(['account_no' => $account_no, 'payment_record_list.type' => $type])
			->andWhere('payment_record.posted_date IS NOT NULL')
			->orderBy('payment_record.posted_date')
			->asArray()->all();
        
        return $payment_record;
	}
}
	