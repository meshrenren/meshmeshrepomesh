<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\LoanAccount;
use \app\models\LoanTransaction;
use app\models\LoanProduct;
use app\models\LoanCutoff;


use app\helpers\payment\PaymentHelper;
use app\helpers\GlobalHelper;

class LoanHelper 
{

    /* Get latest each loan type accounts for the member */
	public static function getAccountLoanInfo($member_id){

		$query = new \yii\db\Query;
		//$query->select('DISTINCT(loan_id) as loan_id') -->giusab sa nako ren para maview tanang loans
        $query->select('*')
            ->from('loanaccount la')
            ->where('la.member_id = '. $member_id)
            ->groupBy('la.loan_id');
        $loanAccounts = $query->all();
        $accountList = array();
        if(count($loanAccounts) >= 1){
            foreach ($loanAccounts as $loan) {
                $acc = \app\models\LoanAccount::find()
                    ->innerJoinWith(['product'])
                    //->where(['member_id' => $member_id, 'loan_id' =>  $loan['loan_id']]) -->giusab sa nako ni ren para maview tanang loans. :)
                    ->where(['loanaccount.member_id' => $member_id, 'loan_id' =>  $loan['loan_id']])
                     ->andWhere('status != "Cancel" AND status != "Verified" ')
                    ->orderBy('release_date DESC')
                    ->asArray()->one();

                $accArr = array();
                if($acc){
                    $accArr = $acc;
                    //Getarrear
                    $getArrear = static::getArrears($acc['account_no']);
                    $accArr['arrears'] = $getArrear['arrearAmount'];

                    //Last Payment
                    $accArr['account_last_payment'] = "";
                    $lastPayment = static::getLastPayment($acc['account_no']);
                    if($lastPayment){
                        $accArr['account_last_payment'] = $lastPayment->date_posted;
                    }
                    //Check LoanController -> actionGetAccountLoanInfo for updates
                }

                array_push($accountList, $accArr);
            }
        }
		return $accountList;
	}

    public static function getMemberLoan($member_id, $loan_id, $asArray = true, $joinWith = null, $isAll = false, $orderBy = 'release_date DESC'){
        $acc = \app\models\LoanAccount::find()
            ->innerJoinWith(['product'])
            ->where(['loanaccount.member_id' => $member_id, 'loanaccount.loan_id' =>  $loan_id])
            ->andWhere('status != "Cancel"')
            ->orderBy($orderBy);
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

    public static function getLoanAccount($account_no){
        $acc = \app\models\LoanAccount::find()
            ->innerJoinWith(['product'])
            ->where(['account_no' => $account_no]);

        return $acc->one();
    }


    public static function getLoanTransaction($loan_account, $filter=null, $orderBy = null){
        $accountList = LoanTransaction::find();
        if($loan_account != null){
            $accountList = $accountList->where(['loan_account' => $loan_account]);
        }

        if($orderBy != null){
            $accountList = $accountList->orderBy($orderBy);
        }
        
        return $accountList->asArray()->all();
    }

    public static function getLastPayment($loan_account){
        $accTrans = LoanTransaction::find()->where(['loan_account' => $loan_account, 'transaction_type' => "PAYPARTIAL"])->orderBy('date_posted DESC')->one();
        return $accTrans;
    }

    public static function getProduct($filter, $asArray = false){
        $getProduct= LoanProduct::find()->where($filter);
        if($asArray){
            $getProduct = $getProduct->asArray();
        }
        $getProduct = $getProduct->one();

        return $getProduct;
    }
    
    
    public static function closeAccountDueToRenewal($loanDetails, $transaction_date)
    {
    	//start of hell
    	
    	$product = LoanProduct::findOne($loanDetails['product_id']);
    	$account = LoanAccount::findOne($loanDetails['accountnumber']);
        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
    	

    	
    	$prepaidInterest = $loanDetails['prepaid_int_pay']; //multiply to -1 to negate the prepaid interest
    	$principal_pay = $loanDetails['principal_pay'];
    	

    	$interestEarned = 0; 
    	
    	$totalToPay =  $loanDetails['principal_pay'] +  $loanDetails['prepaid_int_pay'] + $loanDetails['interest_pay'];

        $running_balance = round($account->principal_balance - $principal_pay, 2);
        $asInitialPayment = 0;
        if($running_balance < 0){
            $asInitialPayment = $principal_pay - $account->principal_balance;
            $running_balance = 0;
            $principal_pay = $account->principal_balance;
            $totalToPay = $totalToPay - $asInitialPayment;
        }
    	
    	
    	//1. insert to payment transaction
    	$loanTransaction = new LoanTransaction();
    	$loanTransaction->loan_account = $loanDetails['accountnumber'];
    	$loanTransaction->amount = round($totalToPay, 2);
    	$loanTransaction->transaction_type='PAYCLOSE';
    	$loanTransaction->transacted_by = \Yii::$app->user->identity->id;
    	$loanTransaction->transaction_date = $transaction_date;
    	$loanTransaction->running_balance = round($running_balance, 2);
    	$loanTransaction->remarks="payment thru payment facility";
    	$loanTransaction->prepaid_intpaid = round($prepaidInterest, 2);
    	$loanTransaction->interest_paid = $loanDetails['interest_pay'];
    	$loanTransaction->OR_no= $loanDetails['reference'];
    	$loanTransaction->principal_paid = round($principal_pay, 2);
    	$loanTransaction->arrears_paid = 0;
    	$loanTransaction->date_posted = $dateToday;
    	$loanTransaction->interest_earned = round($interestEarned, 2);
    	
    	$account->principal_balance = $loanTransaction->running_balance;
    	$account->interest_balance = $account->interest_balance - $loanDetails['interest_pay'];
    	$account->status = "Closed";

    	//3. update loan balance
    	if($loanTransaction->save() && $account->save())
    	{
    		return ['success' => true, 'asInitialPayment' => $asInitialPayment];
    	}
    	
    	else
    	{
    		return [
                'success'   => false,
    			'lnTransaction' => $loanTransaction->errors,
    			'lnAccount' => $account->errors
    		];
    		
    		
    	}
    	//end of start of hell
    }
    

    public static function printLoanSummary($dataLoan){
        $details = $dataLoan['details'];
        $loanList = $dataLoan['loanList'];
        $member = isset($dataLoan['member']) && $dataLoan['member'] ? $dataLoan['member'] : null;

        $listTemplate = Yii::$app->params['formTemplate']['header_layout'];

        $listTemplate .= '<table class = "no-border mt-20">
            <tr>
                <td style = "font-weight: bold;">NAME: </td> 
                <td><span>[account_name]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Station/Province: </td> 
                <td>[account_station] </td>
            </tr> 
        </table>';
        if($member){
            $share_deposit = $member && isset($member['share_capital']) ? floatval($member['share_capital']) : 0;
            $max = $share_deposit * 4;
            $listTemplate .= '<table class = "no-border mt-20">
                <tr>
                    <td style = "font-weight: bold;">Share Deposit: </td> 
                    <td><span>'.Yii::$app->view->formatNumber($share_deposit).'</span></td>
                </tr> 
                <tr>
                    <td style = "font-weight: bold;">Maximum Loan Amount: </td> 
                    <td>'.Yii::$app->view->formatNumber($max).' </td>
                </tr> 
            </table>';
        }
        
        $listTemplate .= '<table class = "no-border" style = "margin-top:10px;">
            <tr>
                <td style = "font-weight: bold;">TOTAL AMOUNT OF LOANS:</td> 
                <td>[total_principal]</td>
                <td></td>

                <td style = "font-weight: bold;">TOTAL LOAN ARREAR:</td> 
                <td>[total_arrears]</td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">TOTAL AMOUNT OF LOAN BALANCES:</td> 
                <td><span>[total_balance]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">LOAN SUMMARY AS OF:</td> 
                <td><span>[summry_as_of]</span></td>
            </tr> 
        </table>';
        $listTemplate = str_replace('[account_name]', $details['fullname'], $listTemplate);
        $listTemplate = str_replace('[account_station]', $details['station'], $listTemplate);
        $listTemplate = str_replace('[total_principal]', Yii::$app->view->formatNumber($details['totalPrincipal']), $listTemplate);
        $listTemplate = str_replace('[total_balance]', Yii::$app->view->formatNumber($details['totalBalance']), $listTemplate);
        $listTemplate = str_replace('[summry_as_of]', Yii::$app->view->getCutOff(), $listTemplate);

        $transTable = "";
        if(count($loanList) > 0){
            $total_arrears = 0;
            $transTable = '<table width = "100%" style = "margin-top: 20px;">
                <tr>
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Type</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Principal Loan</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Date of Loan</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Balance</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Last Payment</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Arrears</th> 
                </tr>';
            foreach ($loanList as $trans) {
                $arrears = "";
                if(isset($trans['arrears']) && $trans['arrears'] && floatval($trans['arrears']) > 0){
                    $total_arrears += floatval($trans['arrears']);
                    $arrears = Yii::$app->view->formatNumber($trans['arrears']);
                }
                $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">'.$trans['product']['product_name'].'</td> 
                    <td style = "border: 1px solid #000;">'.Yii::$app->view->formatNumber($trans['principal']).'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['release_date'].'</td> 
                    <td style = "border: 1px solid #000;">'.Yii::$app->view->formatNumber($trans['principal_balance']).'</td>
                    <td style = "border: 1px solid #000;">'.$trans['account_last_payment'].'</td>
                    <td style = "border: 1px solid #000;">'.$arrears.'</td> 
                </tr>';


            }

            $transTable .= '</table>';

            $total_arrears_dis = $total_arrears && floatval($total_arrears) > 0 ? Yii::$app->view->formatNumber($total_arrears) : "";
            $listTemplate = str_replace('[total_arrears]', $total_arrears_dis, $listTemplate);
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }

    public static function printLoanLedger($dataLoan){
        $details = $dataLoan['details'];
        $loanledger = $dataLoan['loanledger'];
        $member = isset($dataLoan['member']) && $dataLoan['member'] ? $dataLoan['member'] : null;

        $listTemplate = Yii::$app->params['formTemplate']['header_layout'];

        $listTemplate .= '<table class = "no-border mt-20">
            <tr>
                <td style = "font-weight: bold;">Loan Type: </td> 
                <td>[loan_type] </td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">NAME: </td> 
                <td><span>[account_name]</span></td>
            </tr> 
        </table>';
        $listTemplate = str_replace('[account_name]', isset($details['fullname']) ? $details['fullname'] : "", $listTemplate);
        $listTemplate = str_replace('[account_station]', isset($details['loan_type']) ? $details['loan_type'] : "", $listTemplate);

        $transTable = "";
        if(count($loanledger) > 0){
            $total_arrears = 0;
            $transTable = '<table width = "100%" style = "margin-top: 20px;">
                <tr>
                    <th style = "font-weight: bold; border: 1px solid #000;">Date</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">GVORNum</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Principal</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Amount Paid</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Balance</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Prepaid</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Interest</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">InHouse</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">ServChg</th> 
                </tr>';
            foreach ($loanledger as $trans) {
                $principal = "";
                $InHouse = "";
                $ServChg = "";
                if($trans['transaction_type'] == "RELEASE"){
                    $principal = Yii::$app->view->formatNumber($trans['amount']);
                    if(isset($trans['loan_account'])){
                        $InHouse = $trans['loan_account']['redemption_insurance'] ? Yii::$app->view->formatNumber($trans['loan_account']['redemption_insurance']) : "" ;
                        $ServChg = $trans['loan_account']['service_charge'] ? Yii::$app->view->formatNumber($trans['loan_account']['service_charge']) : "" ;
                    }
                }
                $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">'. $trans['date_posted'] .'</td> 
                    <td style = "border: 1px solid #000;">'. $trans['OR_no'] .'</td> 
                    <td style = "border: 1px solid #000;">'. $principal .'</td> 
                    <td style = "border: 1px solid #000;">'. Yii::$app->view->formatNumber($trans['principal_paid']) .'</td>
                    <td style = "border: 1px solid #000;">'. Yii::$app->view->formatNumber($trans['running_balance']) .'</td>
                    <td style = "border: 1px solid #000;">'. Yii::$app->view->formatNumber($trans['prepaid_intpaid']) .'</td>
                    <td style = "border: 1px solid #000;">'. Yii::$app->view->formatNumber($trans['interest_earned']) .'</td>
                    <td style = "border: 1px solid #000;">'. $InHouse .'</td>
                    <td style = "border: 1px solid #000;">'. $ServChg .'</td>
                </tr>';


            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }


    /*
    To get arrear, count the month that the member hasn't paid yet
    eg; Loan: 100,000; 5,000/mo
    Last Payment: 2019-12; balance as of that date is 50,000
    Current Date: 2020-04; 
    Arrears: 15,000; Member hasn't paid for 3 month (5,000 x 3)
    */
    public static function getArrears($account_no){
        $loanAccount = static::getLoanAccount($account_no);
        $arrearAmount = 0;
        if($loanAccount && $loanAccount->principal_balance > 0){
            $principal = floatval($loanAccount->principal);
            $principal_balance = floatval($loanAccount->principal_balance);
            $principal_paid = $principal - $principal_balance;
            $release_date = $loanAccount->release_date;
            //get payment
            $getPayments = PaymentHelper::getPayments($loanAccount->account_no, 'LOAN');

            $getLoanMaturity = GlobalHelper::addDate($release_date, $loanAccount->term, 'month', 'Y-m-d');

            $curSystemDate = date("Y-m-d", strtotime(\Yii::$app->user->identity->getDateNow()));
            if(strtotime($getLoanMaturity) < strtotime($curSystemDate)){ //If lampas na sa maturity date
                $curSystemDate = $getLoanMaturity;
                $arrearAmount = $principal - $principal_paid;
            }
            else{
                $diffDays = GlobalHelper::getDiffDays($release_date, $curSystemDate);

                $moPrin = ($diffDays - 30) / 30; //deduct 1 mo then divide to get the months;
                $moPrin = intval($moPrin);

                $principal_pay = (floatval($loanAccount->principal_amortization_quincena) * 2) * $moPrin;

                $arrearAmount = $principal_pay - $principal_paid;

            }
            if($arrearAmount <= 0){
                $arrearAmount = 0;
            }
        }

        $return = ['arrearAmount' => $arrearAmount];

        return $return;
    }

    public static function getInterest($currentDate, $lastDate, $balance, $int_rate){
        $currentDate = date("Y-m-d", strtotime($currentDate));

        $noOfDaysPassed = date_diff(date_create($currentDate), date_create($lastDate));
        
        $noOfDaysPassed = $noOfDaysPassed->format("%a");
        
        $interestEarned = (floatval($balance) * ($int_rate/100))/30;
        $interestEarned = round($interestEarned * $noOfDaysPassed, 2);
        return $interestEarned;
    }


    public static function calculateBeforeCutOff($loan_account){
        $getCutOff = Yii::$app->view->getCutOff();
        $account = LoanAccount::findOne($loan_account);
        $accountList = LoanTransaction::find()->where(['loan_account' => $loan_account, 'is_cancelled' => "0"])
            ->andWhere('date_posted <= "' . $getCutOff.'"')
            ->andWhere('transaction_type = "RELEASE" OR transaction_type = "PAYPARTIAL"')
            ->orderBy('date_posted')
            ->asArray()->all();
        $totalPi = 0;
        $totalInt = 0;
        $length = count($accountList);
        $lastPayment = $account['release_date'];
        $lastRunningBal = $account['principal'];

        foreach ($accountList as $accKey => $transaction) {
            if(floatval($transaction['prepaid_intpaid']) > 0){
                $totalPi += floatval($transaction['prepaid_intpaid']);
            }
            //Recalculate Interest Earned. Dili magsalig sa DB
            $interestEarned = LoanHelper::getInterest($lastPayment, $transaction['date_posted'], $lastRunningBal, $account->int_rate);
            $totalInt = $totalInt + $interestEarned;

            //If at the last transaction
            if($length == $accKey+1){
                $lastInterest = static::getInterest($getCutOff, $transaction['date_posted'], $transaction['running_balance'], $account->int_rate);
                $totalInt += floatval($lastInterest);
            }

            $lastPayment = $transaction['date_posted'];
            $lastRunningBal = $transaction['running_balance'];
        }

        $cutOffPi = 0;
        $cutOffInt = 0;
        if($totalPi > $totalInt){
            $cutOffPi = $totalPi - $totalInt;
        }
        else{
            $cutOffInt = $totalInt - $totalPi;
        }

        return [
            'totalPi'   => $totalPi,
            'totalInt'  => $totalInt,
            'cutOffPi'  => $cutOffPi,
            'cutOffInt' => $cutOffInt
        ];
    }

    public static function getCutOff($year, $loan_id, $member_id){
        $cutOffPi = 0;
        $cutOffInt = 0;
        $finalPi = 0;
        $finalInt = 0;
        $cutOff = LoanCutoff::find()->where(['loan_id' => $loan_id, 'member_id' => $member_id, 'year' => $year])->one();
        if($cutOff){
            $cutOffPi = $cutOff->prepaid_interest;
            $cutOffInt = $cutOff->interest_earned;
        }

        if($cutOffPi > $cutOffInt){
            $finalPi = $cutOffPi - $cutOffInt;
        }
        else{
            $finalInt = $cutOffInt - $cutOffPi;
        }

        return [
            'cutOffPi'  => $cutOffPi,
            'cutOffInt' => $cutOffInt,
            'finalPi'   => $finalPi,
            'finalInt'  => $finalInt
        ];
    }

    public static function loanPayment($account_no, $loanDetails){
        $success = false;
        $error = null;

        $principal_pay = $loanDetails['principal_pay'];
        $prepaidInterest = isset($loanDetails['prepaid_pay']) && $loanDetails['prepaid_pay'] ? $loanDetails['prepaid_pay'] : 0;
        $ref_num = $loanDetails['ref_num'];
        $product_id = $loanDetails['product_id'];
        $transaction_date = $loanDetails['transaction_date'];
        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));


        $product = LoanProduct::findOne($loanDetails['product_id']);
        $account = LoanAccount::findOne($account_no);

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("
        select ifnull((select date_posted FROM `loan_transaction` where loan_account=:accountnumber and left(transaction_type, 3)='PAY' AND is_cancelled=0 order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber limit 1)) as lasttrandate", [':accountnumber' => $account_no]);
        $lastTransaction = $command->queryOne();
        $noOfDaysPassed = date_diff(date_create($dateToday), date_create($lastTransaction['lasttrandate']));
        $noOfDaysPassed = $noOfDaysPassed->format("%a");

        $interestEarned = 0;
        if($product->id == 1){//Regular loan base on diminishing amount
            $interestEarned = ($account->principal_balance * ($account->int_rate/100))/30;
            $interestEarned = $interestEarned * $noOfDaysPassed;
        }

        //1. insert to payment transaction
        $loanTransaction = new LoanTransaction();
        $loanTransaction->loan_account = $account_no;
        //$loanTransaction->loan_id = $product->id;
        //$loanTransaction->member_id = $account->member_id;

        $amount = floatval($principal_pay) + floatval($prepaidInterest);

        $running_balance = round($account->principal_balance - $principal_pay, 2);
        $asSavings = 0;
        if($running_balance < 0){
            $asSavings = $principal_pay - $account->principal_balance;
            $running_balance = 0;
            $principal_pay = $account->principal_balance;
            $amount = $amount - $asSavings;
        }

        $transaction_type = 'PAYPARTIAL';
        if($running_balance == 0){
            $account->status='Closed';
            $transaction_type="PAYCLOSE";
        }

        $loanTransaction->amount = round($amount, 2);
        $loanTransaction->transaction_type= $transaction_type;
        $loanTransaction->transacted_by = \Yii::$app->user->identity->id;
        $loanTransaction->transaction_date = $transaction_date;
        $loanTransaction->running_balance = $running_balance;
        $loanTransaction->remarks="payment thru payment facility";
        $loanTransaction->prepaid_intpaid = round($prepaidInterest, 2);
        $loanTransaction->interest_paid = 0;
        $loanTransaction->OR_no= $ref_num;
        $loanTransaction->principal_paid = round($principal_pay, 2);
        $loanTransaction->arrears_paid = 0;
        $loanTransaction->date_posted = $dateToday;
        $loanTransaction->interest_earned = round($interestEarned, 2);
        
        $account->principal_balance = $loanTransaction->running_balance;
        $account->interest_balance = $account->interest_balance + $interestEarned;
        $account->interest_accum = $account->interest_accum + $interestEarned;

        if($account->save() && $loanTransaction->save())
        {

            //Calculate Rebate
            if($running_balance == 0){

            }
            
            if($asSavings > 0){

                $savingsaccount = SavingsHelper::getMemberSavings($member_id, false);
                if($savingsaccount){
                    $pro_name = "LOAN";
                    $getLoanProd = LoanProduct::findOne($account->loan_id);
                    if($getLoanProd){
                        $pro_name = $getLoanProd->product_name;
                    }

                    $savingsDetails = array();
                    $savingsDetails['account_no'] = $savingsaccount->account_no;
                    $savingsDetails['remarks'] = "From " .$pro_name. " payment. Posted as Payment from ". $$ref_num;
                    $amount['amount'] = $asSavings;
                    $amount['transaction_date'] = $transaction_date;

                    $depositSavings = SavingsHelper::depositSavings($savingsDetails);
                }
                
            }
            $success = true;    
        }
        else{
            $success = false;
            $error = $loanTransaction->errors;
        }

        return ['success' => $success, 'error' => $error];

    }

    public static function loanRefund($account_no, $loanDetails){
        $success = false;
        $error = null;

        $amount = $loanDetails['amount'];
        $ref_num = $loanDetails['ref_num'];
        $transaction_date = $loanDetails['transaction_date'];
        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));

        $account = LoanAccount::findOne($account_no);

        $principal_pay = floatval($amount) * -1;
        $running_balance = round($account->principal_balance - $principal_pay, 2);
       
        $transaction_type="REFUND";
        if($running_balance == 0){
            $account->status='Closed';
        }

        $loanTransaction = new LoanTransaction();
        $loanTransaction->loan_account = $account_no;
        $loanTransaction->amount = round($amount, 2);
        $loanTransaction->transaction_type= $transaction_type;
        $loanTransaction->transacted_by = \Yii::$app->user->identity->id;
        $loanTransaction->transaction_date = $transaction_date;
        $loanTransaction->running_balance = $running_balance;
        $loanTransaction->remarks="refund";
        $loanTransaction->prepaid_intpaid = 0;
        $loanTransaction->interest_paid = 0;
        $loanTransaction->OR_no= $ref_num;
        $loanTransaction->principal_paid = round($principal_pay, 2);
        $loanTransaction->arrears_paid = 0;
        $loanTransaction->date_posted = $dateToday;
        $loanTransaction->interest_earned = 0;
        
        $account->principal_balance = $loanTransaction->running_balance;

        if($account->save() && $loanTransaction->save())
        {
            //Calculate Rebate
            if($running_balance == 0){

            }

            $success = true;    
        }
        else{
            $success = false;
            $error = $loanTransaction->errors;
        }

        return ['success' => $success, 'error' => $error];

    }

}