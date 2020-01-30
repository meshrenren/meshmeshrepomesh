<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\LoanAccount;
use \app\models\LoanTransaction;
use app\models\LoanProduct;

class LoanHelper 
{

    /* Get latest each loan type accounts for the member */
	public static function getAccountLoanInfo($member_id){

		$query = new \yii\db\Query;
		//$query->select('DISTINCT(loan_id) as loan_id') -->giusab sa nako ren para maview tanang loans
        $query->select('account_no')
            ->from('loanaccount la')
            ->where('member_id = '. $member_id);
        $loanAccounts = $query->all();
        $accountList = array();
        if(count($loanAccounts) >= 1){
            foreach ($loanAccounts as $loan) {
                $acc = \app\models\LoanAccount::find()
                    ->innerJoinWith(['product'])
                    //->where(['member_id' => $member_id, 'loan_id' =>  $loan['loan_id']]) -->giusab sa nako ni ren para maview tanang loans. :)
               		 ->where(['member_id' => $member_id, 'account_no' => $loan['account_no']])
                     ->andWhere('status != "Cancel" AND status != "Verified" ')
                    ->orderBy('release_date DESC')
                    ->asArray()->one();
                array_push($accountList, $acc);
            }
        }
		return $accountList;
	}

    public static function getMemberLoan($member_id, $loan_id, $asArray = true, $joinWith = nul, $isAll = false){
        $acc = \app\models\LoanAccount::find()
            ->innerJoinWith(['product'])
            ->where(['member_id' => $member_id, 'loan_id' =>  $loan_id])
            ->andWhere('status != "Cancel"')
            ->orderBy('release_date DESC');
        if($joinWith){
            $acc->joinWith($joinWith);
        }

        if($asArray){
            $acc->asArray();
        }

        if($isAll){
            return $acc->all();
        }
        return $acc->one();
    }


    public static function getLoanTransaction($loan_account, $filter=null){
        $accountList = LoanTransaction::find();
        if($loan_account != null){
            $accountList = $accountList->where(['loan_account' => $loan_account]);
        }
        
        return $accountList->asArray()->all();
    }
    
    
    
    
    
    
    public static function closeAccountDueToRenewal($loanDetails)
    {
    	//start of hell
    	
    	$product = LoanProduct::findOne($loanDetails['product_id']);
    	$account = LoanAccount::findOne($loanDetails['accountnumber']);
    	

    	
    	$prepaidInterest = $loanDetails['prepaid_int_pay']; //multiply to -1 to negate the prepaid interest
    	$principal_pay = $loanDetails['principal_pay'];
    	
    	
    	

    	$interestEarned = 0; 
    	
    	$totalToPay =  $loanDetails['principal_pay'] +  $loanDetails['prepaid_int_pay'] + $loanDetails['interest_pay'];
    	
    	
    	//1. insert to payment transaction
    	$loanTransaction = new LoanTransaction();
    	$loanTransaction->loan_account = $loanDetails['accountnumber'];
    	$loanTransaction->amount = round($totalToPay, 2);
    	$loanTransaction->transaction_type='PAYCLOSE';
    	$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    	$loanTransaction->transaction_date = date('Y-m-d');
    	$loanTransaction->running_balance = round($account->principal_balance - $principal_pay, 2);
    	$loanTransaction->remarks="payment thru payment facility";
    	$loanTransaction->prepaid_intpaid = round($prepaidInterest, 2);
    	$loanTransaction->interest_paid = $loanDetails['interest_pay'];
    	$loanTransaction->OR_no= $loanDetails['reference'];
    	$loanTransaction->principal_paid = round($principal_pay, 2);
    	$loanTransaction->arrears_paid = 0;
    	$loanTransaction->date_posted = date('Y-m-d');
    	$loanTransaction->interest_earned = round($interestEarned, 2);
    	
    	$account->principal_balance = $loanTransaction->running_balance;
    	$account->interest_balance = $account->interest_balance - $loanDetails['interest_pay'];
    	$account->status = "Closed";
    	
    	if($loanTransaction->running_balance<0)
    	{
    		
   
    		return "achieved negative. might want to proceed to close payment.";

    		
    	}
    	
    	//3. update loan balance
    	if($loanTransaction->save() && $account->save())
    	{
    		return "success";
    		
    	}
    	
    	else
    	{
    		return [
    				'lnTransaction' => $loanTransaction->errors,
    				'lnAccount' => $account->errors
    		];
    		
    		
    	}
    	//end of start of hell
    }
    
    
    
    
    
    
    
    
    
    

    public static function printLoanSummary($dataLoan){
        $details = $dataLoan['details'];
        $loanList = $dataLoan['loanList'];

        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">Loan Summary</div></tr>
        </table>';

        $listTemplate .= '<table>
            <tr>
                <td style = "font-weight: bold;">NAME: </td> 
                <td><span>[account_name]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Station/Province: </td> 
                <td>[account_station] </td>
            </tr> 
        </table>';
        $listTemplate .= '<table style = "margin-top:10px;">
            <tr>
                <td style = "font-weight: bold;">TOTAL AMOUNT OF LOANS:</td> 
                <td>[total_principal]</td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">TOTAL AMOUNT OF LOAN BALANCES:</td> 
                <td><span>[total_balance]</span></td>
            </tr> 
        </table>';
        $listTemplate = str_replace('[account_name]', $details['fullname'], $listTemplate);
        $listTemplate = str_replace('[account_station]', $details['station'], $listTemplate);
        $listTemplate = str_replace('[total_principal]', $details['totalPrincipal'], $listTemplate);
        $listTemplate = str_replace('[total_balance]', $details['totalBalance'], $listTemplate);

        if(count($loanList) > 0){
            $transTable = '<table width = "100%" style = "margin-top: 20px; border-collapse: collapse !important:">
                <tr>
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Type</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Principal Loan</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Balance Type</th> 
                </tr>';
            foreach ($loanList as $trans) {
                $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">'.$trans['product']['product_name'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['principal'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['principal_balance'].'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }

}