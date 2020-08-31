<?php

namespace app\helpers\accounts;

use Yii;
use app\models\LoanAccount;

class AccountHelper 
{
	public static function processOtherAccount($otherAccToProcess, $ref_num, $transaction_date){
		$success = true;
		if($otherAccToProcess && count($otherAccToProcess) > 0){
            foreach ($otherAccToProcess as $lnKey => $ln) {

                if($ln['type'] == "LOAN"){ // Loan Payment
                    if(isset($ln['amountToPay']) && floatval($ln['amountToPay']) > 0){
                        $amountToPay = floatval($ln['amountToPay']);

                        $otherLoanModel = LoanAccount::findOne($ln['account_no']);

                        $loanDetails = array();
                        $loanDetails['principal_pay'] = $amountToPay;
                        $loanDetails['prepaid_pay'] = 0;
                        $loanDetails['ref_num'] = $ref_num;
                        $loanDetails['product_id'] = $otherLoanModel->loan_id;
                        $loanDetails['transaction_date'] = $transaction_date;
                        $loanPayment = LoanHelper::loanPayment($ln['account_no'], $loanDetails);

                        if(!$loanPayment['success']){
                            $success = false;
                            break;
                        }
                    }
                }
                else if($ln['type'] == "SAVINGS"){ // Deposit

                    if(isset($ln['amountToPay']) && floatval($ln['amountToPay']) > 0){
                        $amountToPay = floatval($ln['amountToPay']);

                        $savingsDetails = array();
                        $savingsDetails['account_no'] = $ln['account_no'];
                        $savingsDetails['remarks'] = "Posted as deposit from ". $ref_num;
                        $savingsDetails['amount'] = $amountToPay;
                        $savingsDetails['ref_num'] = $ref_num;
                        $savingsDetails['transaction_date'] = $transaction_date;
                    	$savingsDetails['transaction_type'] = 'CASHDEP';

                        $depositSavings = SavingsHelper::transactionSavings($savingsDetails);
                        if(!$depositSavings['success']){
                            $success = false;
                            break;
                        }
                    }

                    if(isset($ln['amountToWithdraw']) && floatval($ln['amountToWithdraw']) > 0){
                        $amountToWithdraw = floatval($ln['amountToWithdraw']);

                        $savingsDetails = array();
                        $savingsDetails['account_no'] = $ln['account_no'];
                        $savingsDetails['remarks'] = "Posted as deposit from ". $ref_num;
                        $savingsDetails['amount'] = $amountToWithdraw;
                        $savingsDetails['ref_num'] = $ref_num;
                        $savingsDetails['transaction_date'] = $transaction_date;
                        $savingsDetails['transaction_type'] = 'WITHDRWL';

                        $depositSavings = SavingsHelper::transactionSavings($savingsDetails);
                        if(!$depositSavings['success']){
                            $success = false;
                            break;
                        }
                    }


                }
                else if($ln['type'] == "SHARE"){ // Deposit

                    if(isset($ln['amountToPay']) && floatval($ln['amountToPay']) > 0){
                        $amountToPay = floatval($ln['amountToPay']);

                        $shareDetails = array();
                        $shareDetails['account_no'] = $ln['account_no'];
                        $shareDetails['remarks'] = "Posted as deposit from ". $ref_num;
                        $shareDetails['amount'] = $amountToPay;
                        $shareDetails['ref_num'] = $ref_num;
                        $shareDetails['transaction_date'] = $transaction_date;
                        $shareDetails['transaction_type'] = "CASHDEP";

                        $depositShare = ShareHelper::transactionShare($shareDetails);
                        if(!$depositShare['success']){
                            $success = false;
                            break;
                        }
                    }

                    if(isset($ln['amountToWithdraw']) && floatval($ln['amountToWithdraw']) > 0){
                        $amountToWithdraw = floatval($ln['amountToWithdraw']);

                        $shareDetails = array();
                        $shareDetails['account_no'] = $ln['account_no'];
                        $shareDetails['remarks'] = "Posted as withdrawal from ". $ref_num;
                        $shareDetails['amount'] = $amountToWithdraw;
                        $shareDetails['ref_num'] = $ref_num;
                        $shareDetails['transaction_date'] = $transaction_date;
                        $shareDetails['transaction_type'] = "WITHDRWL";

                        $depositShare = ShareHelper::transactionShare($shareDetails);
                        if(!$depositShare['success']){
                            $success = false;
                            break;
                        }
                    }
                }
                
            }
        }

        return ['success' => $success];
	}
}