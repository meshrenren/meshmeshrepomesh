<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\LoanAccount;
use \app\models\LoanTransaction;
use app\models\LoanProduct;
use app\models\LoanCutoff;


use app\helpers\payment\PaymentHelper;
use app\helpers\particulars\ParticularHelper;
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

                    //Get rebates
                    $accArr['rebates'] = 0;
                    if($acc['principal_balance'] <= 0){
                        $getRebate = static::calculateRebate($acc['account_no']);
                        $accArr['rebates'] = $getRebate['rebateAmount'];
                    }
                    //Check LoanController -> actionGetAccountLoanInfo for updates
                }

                array_push($accountList, $accArr);
            }
        }
		return $accountList;
	}

    public static function getLatestLoan($member_id, $orderBy = "release_date DESC"){

        $accountList = \app\models\LoanAccount::find()
                    ->innerJoinWith(['product'])
                    ->where(['loanaccount.member_id' => $member_id])
                    ->andWhere('status != "Cancel" AND status != "Verified"')
                    ->andWhere('principal_balance > 0')
                    ->orderBy($orderBy)
                    ->asArray()->all();
                    
        return $accountList;
    }

    public static function getMemberLoan($member_id, $loan_id, $asArray = true, $joinWith = null, $isAll = false, $orderBy = 'release_date DESC'){
        $acc = \app\models\LoanAccount::find()
            ->innerJoinWith(['product'])
            ->where(['loanaccount.member_id' => $member_id, 'loanaccount.loan_id' =>  $loan_id])
            ->andWhere('status != "Cancel" AND status != "Verified"')
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



    public static function getActiveLoans(){
        $loanAccount = \app\models\LoanAccount::find()
            ->select(['loanaccount.id', 'account_no', 'loan_id', 'member_id', 'release_date', 'principal', 'principal_balance', 'term', 'principal_amortization_quincena'])
            ->innerJoinWith(['member' => function ($query) {
                    $query->select(['member.id', "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname", "station_id" ]);
                }
            ])
            ->where('status != "Cancel" AND status != "Verified" ')
            ->andWhere('principal_balance > 0')
            ->orderBy('member.last_name')
            ->asArray()->all();

        return $loanAccount;
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

    public static function getLastFullPayment($loan_account){
        $accTrans = LoanTransaction::find()->where(['loan_account' => $loan_account])->andWhere("transaction_type = 'PAYPARTIAL' OR transaction_type = 'PAYCLOSE'")->andWhere('running_balance <= 0')->orderBy('date_posted ASC')->one();
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

    public static function getProductList($filter, $asArray = false){
        $getProduct= LoanProduct::find()->where($filter);
        if($asArray){
            $getProduct = $getProduct->asArray();
        }
        $getProduct = $getProduct->all();

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

        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
        $listTemplate = str_replace('[account_name]', $details['fullname'], $listTemplate);
        $listTemplate = str_replace('[account_station]', $details['station'], $listTemplate);
        $listTemplate = str_replace('[total_principal]', Yii::$app->view->formatNumber($details['totalPrincipal']), $listTemplate);
        $listTemplate = str_replace('[total_balance]', Yii::$app->view->formatNumber($details['totalBalance']), $listTemplate);
        $listTemplate = str_replace('[summry_as_of]', $dateToday, $listTemplate);

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
                    <th style = "font-weight: bold; border: 1px solid #000;">Payment per Quincena</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Arrears</th> 
                </tr>';
            foreach ($loanList as $trans) {
                $arrears = "";
                if(isset($trans['arrears']) && $trans['arrears'] && floatval($trans['arrears']) > 0){
                    $total_arrears += floatval($trans['arrears']);
                    $arrears = Yii::$app->view->formatNumber($trans['arrears']);
                }
                $per_quincena = "";
                if(isset($trans['principal_amortization_quincena']) && $trans['principal_amortization_quincena'] && floatval($trans['principal_amortization_quincena']) > 0){
                    $per_quincena = Yii::$app->view->formatNumber($trans['principal_amortization_quincena']);
                }

                if(isset($trans['prepaid_amortization_quincena']) && $trans['prepaid_amortization_quincena'] && floatval($trans['prepaid_amortization_quincena']) > 0){
                    $per_quincena .= " / " . Yii::$app->view->formatNumber($trans['prepaid_amortization_quincena']);
                }

                $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">'.$trans['product']['product_name'].'</td> 
                    <td style = "border: 1px solid #000;">'.Yii::$app->view->formatNumber($trans['principal']).'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['release_date'].'</td> 
                    <td style = "border: 1px solid #000;">'.Yii::$app->view->formatNumber($trans['principal_balance']).'</td>
                    <td style = "border: 1px solid #000;">'.$trans['account_last_payment'].'</td>
                    <td style = "border: 1px solid #000;">'.$per_quincena.'</td>
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

    public static function getLoanArrears(){
        $activeLoan = static::getActiveLoans();
        $accountList = array();
        foreach ($activeLoan as $key => $loan) {
            $getArrear = static::getArrears($loan['account_no']);
            if($getArrear['arrearAmount'] > 0){
                $loan['arrears'] = $getArrear['arrearAmount'];

                array_push($accountList, $loan);
            }
        }

        return $accountList;

    }

    /*
    To get arrear, count the month that the member hasn't paid yet
    eg; Loan: 100,000; 5,000/mo
    Last Payment: 2019-12; balance as of that date is 50,000
    Current Date: 2020-04; 
    Arrears: 15,000; Member hasn't paid for 3 month (5,000 x 3)
    */
    public static function getArrears($account_no, $loanAccount = null){
        if($loanAccount == null){
            $loanAccount = static::getLoanAccount($account_no);
        }
        
        $arrearAmount = 0;
        if($loanAccount && $loanAccount->principal_balance > 0){
            $principal = floatval($loanAccount->principal);
            $principal_balance = floatval($loanAccount->principal_balance);
            $principal_paid = $principal - $principal_balance;
            $release_date = $loanAccount->release_date;
            //get payment
            //$getPayments = PaymentHelper::getPayments($loanAccount->account_no, 'LOAN');

            $getLoanMaturity = GlobalHelper::addDate($release_date, $loanAccount->term, 'month', 'Y-m-d');

            $curSystemDate = date("Y-m-d", strtotime(\Yii::$app->user->identity->getDateNow()));
            if(strtotime($getLoanMaturity) < strtotime($curSystemDate)){ //If lampas na sa maturity date
                $curSystemDate = $getLoanMaturity;
                $arrearAmount = $principal - $principal_paid;

            }
            else{
                $diffDays = GlobalHelper::getDiffDays($release_date, $curSystemDate);

                //$moPrin = ($diffDays - 30) / 30; //deduct 1 mo then divide to get the months;
                $moPrin = $diffDays / 30; 
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
        $loanTransaction->loan_id = $product->id;
        $loanTransaction->member_id = $account->member_id;

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

                $savingsaccount = SavingsHelper::getMemberSavings($account->member_id, false);
                if($savingsaccount){
                    $pro_name = "LOAN";
                    $getLoanProd = LoanProduct::findOne($account->loan_id);
                    if($getLoanProd){
                        $pro_name = $getLoanProd->product_name;
                    }

                    $savingsDetails = array();
                    $savingsDetails['account_no'] = $savingsaccount->account_no;
                    $savingsDetails['remarks'] = "From " .$pro_name. " payment. Posted as Payment from ". $ref_num;
                    $savingsDetails['amount'] = $asSavings;
                    $savingsDetails['transaction_date'] = $transaction_date;
                    $savingsDetails['transaction_type'] = 'CASHDEP';

                    $depositSavings = SavingsHelper::transactionSavings($savingsDetails);
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

    public static function loanRebates($account_no, $loanDetails){
        $success = false;
        $error = null;

        $amount = $loanDetails['amount'];
        $ref_num = $loanDetails['ref_num'];
        $transaction_date = $loanDetails['transaction_date'];
        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));

        $account = LoanAccount::findOne($account_no);

        /*$principal_pay = floatval($amount) * -1;
        $running_balance = round($account->principal_balance - $principal_pay, 2);*/
        
        $running_balance = $account->principal_balance;
        $transaction_type="REBATES";
        /*if($running_balance == 0){
            $account->status='Closed';
        }*/

        $loanTransaction = new LoanTransaction();
        $loanTransaction->loan_account = $account_no;
        $loanTransaction->amount = round($amount, 2);
        $loanTransaction->transaction_type= $transaction_type;
        $loanTransaction->transacted_by = \Yii::$app->user->identity->id;
        $loanTransaction->transaction_date = $transaction_date;
        $loanTransaction->running_balance = $running_balance;
        $loanTransaction->remarks="rebates";
        $loanTransaction->prepaid_intpaid = 0;
        $loanTransaction->interest_paid = 0;
        $loanTransaction->OR_no= $ref_num;
        $loanTransaction->principal_paid = 0;
        $loanTransaction->arrears_paid = 0;
        $loanTransaction->date_posted = $dateToday;
        $loanTransaction->interest_earned = 0;
        
        //$account->principal_balance = $loanTransaction->running_balance;

        if( $loanTransaction->save())
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

    public static function cancelLoanRelease($account_no){
        $account = LoanAccount::findOne($account_no);

        //check if have loan payment
        $loanPayment = LoanTransaction::find()->where(['loan_account' => $account_no, 'transaction_type' => 'PAYPARTIAL'])->count();
        if($loanPayment > 0){
            return ['success' => false, 'error' => 'release_has_payment'];
        }
        $success = true;

        $loanRelease = LoanTransaction::find()->where(['loan_account' => $account_no, 'transaction_type' => 'RELEASE'])->one();
        if($loanRelease){

            $amount = $loanRelease->amount;
            $ref_num = 'CAN'.$loanRelease->OR_no;
            $transaction_date = \Yii::$app->user->identity->DateTimeNow;
            $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));

            $loanTransaction = new LoanTransaction();
            $loanTransaction->loan_account = $account_no;
            $loanTransaction->amount = round($amount, 2) * -1;
            $loanTransaction->transaction_type= 'CNRELEASE';
            $loanTransaction->transacted_by = \Yii::$app->user->identity->id;
            $loanTransaction->transaction_date = $transaction_date;
            $loanTransaction->running_balance = 0;
            $loanTransaction->remarks="cancelled loan release";
            $loanTransaction->prepaid_intpaid = 0;
            $loanTransaction->interest_paid = 0;
            $loanTransaction->OR_no= $ref_num;
            $loanTransaction->principal_paid = 0;
            $loanTransaction->arrears_paid = 0;
            $loanTransaction->date_posted = $dateToday;
            $loanTransaction->interest_earned = 0;

            if(!$loanTransaction->save())
            {
                $success = false;
            }

            $account->principal_balance = $loanTransaction->running_balance;
            $account->status = 'CANCEL';

            if(!$account->save())
            {
                $success = false;
            }

            if($success){
                //Cancel other payment of the release
            }
        }

        return ['success' => $success];;
    }

    public static function loanTransaction($trans){
        $success = false;
        $amount = $trans['amount'];
        $ref_num = $trans['ref_num'];
        $transaction_date = $trans['transaction_date'];
        $transaction_type = $trans['transaction_type'];
        $remarks = $trans['remarks'];

        $running_balance = isset($trans['running_balance']) ? $trans['running_balance'] : 0;
        $prepaid_intpaid = isset($trans['prepaid_intpaid']) ? $trans['prepaid_intpaid'] : 0; 
        $principal_paid = isset($trans['principal_paid']) ? $trans['principal_paid'] : 0;
        $interest_earned = isset($trans['interest_earned']) ? $trans['interest_earned'] : 0;

        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));

        $loanTransaction = new LoanTransaction();
        $loanTransaction->loan_account = $account_no;
        $loanTransaction->amount = round($amount, 2) * -1;
        $loanTransaction->transaction_type= $transaction_type;
        $loanTransaction->transacted_by = \Yii::$app->user->identity->id;
        $loanTransaction->transaction_date = $transaction_date;
        $loanTransaction->running_balance = $running_balance;
        $loanTransaction->remarks=$remarks;
        $loanTransaction->prepaid_intpaid = $prepaid_intpaid;
        $loanTransaction->interest_paid = 0;
        $loanTransaction->OR_no= $ref_num;
        $loanTransaction->principal_paid = $principal_paid;
        $loanTransaction->arrears_paid = 0;
        $loanTransaction->date_posted = $dateToday;
        $loanTransaction->interest_earned = $interest_earned;

        if($loanTransaction->save()){
            $success = false;
        }

        return $success;
    }

    public static function cancelPayment($ref_num){

    }

    public static function getRebates($member_id){

        //Get latest loan for each product.
        $query = new \yii\db\Query;
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
                    ->where(['loanaccount.member_id' => $member_id, 'loan_id' =>  $loan['loan_id']])
                    ->andWhere('status != "Cancel" AND status != "Verified" ')
                    ->orderBy('release_date DESC')
                    ->asArray()->one();

                $accArr = array();
                if($acc){
                    $accArr = $acc;

                    if($acc['principal_balance'] <= 0){
                        $getRebate = static::calculateRebate($acc['account_no']);
                        $accArr['rebates'] = $getRebate['rebateAmount'];
                        $accArr['account_last_payment'] = $getRebate['last_payment'];
                    }

                    if(!isset($accArr['account_last_payment']) || $accArr['account_last_payment'] == null){
                        //Last Payment
                        $accArr['account_last_payment'] = "";
                        $lastPayment = static::getLastPayment($acc['account_no']);
                        if($lastPayment){
                            $accArr['account_last_payment'] = $lastPayment->date_posted;
                        }
                    }

                    array_push($accountList, $accArr);

                }

            }
        }

        return $accountList;
    }

    public static function calculateRebate($account_no){
        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
        $toRebates = 0;
        $lastPaymentDate = null;

        $acc = \app\models\LoanAccount::find()->innerJoinWith(['product'])->where(['account_no' => $account_no])->one();

        //Do not include Regular Loan and if interest type = 2 (interest nga dili na ibalik like misc loan ug calamity loan)
        if($acc && $acc->prepaid && floatval($acc->prepaid) > 0 &&
            $acc->principal_balance <= 0 && $acc->loan_id !== 2 && 
            $acc->product->interest_type_id != 2){ 

            //get rebates transaction
            $transRebates = LoanTransaction::find()->where(['loan_account' => $acc->account_no, 'transaction_type' => 'REBATES'])->one();
            if(!$transRebates){   
                $lastPayment = static::getLastFullPayment($account_no);                 
                if($lastPayment){
                    $lastPaymentDate = $lastPayment->date_posted; 
                    $noOfDaysPassed = date_diff(date_create($lastPaymentDate), date_create($acc['release_date']));

                    //rebates calculation
                    $pi = $acc->prepaid / $acc->term;
                    $monthsPaid = ($noOfDaysPassed->format("%y") * 12) + $noOfDaysPassed->format("%m");
                    $pi_paid = $pi * $monthsPaid;

                    $toRebates = $acc->prepaid - $pi_paid;
                }
            }
        }

        return ['rebateAmount' => $toRebates, 'last_payment' => $lastPaymentDate];
    }

    public static function printRebates($dataLoan){
        $details = $dataLoan['details'];
        $loanList = isset($dataLoan['loanList']) ? $dataLoan['loanList'] : null;
        $member = isset($dataLoan['member']) && $dataLoan['member'] ? $dataLoan['member'] : null;

        $listTemplate = Yii::$app->params['formTemplate']['header_layout'];

        $listTemplate .= '<table class = "no-border mt-20">
            <tr>
                <td style = "font-weight: bold;">NAME: </td> 
                <td><span>[account_name]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Date: </td> 
                <td>[date] </td>
            </tr> 
        </table>';

        $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
        $listTemplate = str_replace('[account_name]', $details['fullname'], $listTemplate);
        $listTemplate = str_replace('[date]', $dateToday, $listTemplate);

        if($loanList == null){
            $loanList = [];
            //get list here
            $loanList = static::getRebates($member['id']);
        }

        $transTable = "";
        if(count($loanList) > 0){
            $total_rebates = 0;
            $transTable = '<table width = "100%" style = "margin-top: 20px;">
                <tr>
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Type</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Rebates</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Principal Loan</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Balance</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Loan Date</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Last Payment</th> 
                </tr>';
            foreach ($loanList as $trans) {
                $rebates = 0;
                if(isset($trans['rebates']) && floatval($trans['rebates']) > 0){
                    $total_rebates += floatval($trans['rebates']);
                    $rebates = floatval($trans['rebates']);
                }
                $rebates = Yii::$app->view->formatNumber($rebates);

                $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">'.$trans['product']['product_name'].'</td> 
                    <td style = "border: 1px solid #000; font-weight: bold;">'.$rebates.'</td> 
                    <td style = "border: 1px solid #000;">'.Yii::$app->view->formatNumber($trans['principal']).'</td> 
                    <td style = "border: 1px solid #000;">'.Yii::$app->view->formatNumber($trans['principal_balance']).'</td>
                    <td style = "border: 1px solid #000;">'.$trans['release_date'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['account_last_payment'].'</td>
                </tr>';

            }

            $total_rebates_dis = $total_rebates && floatval($total_rebates) > 0 ? Yii::$app->view->formatNumber($total_rebates) : "";
            $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">TOTAL</td> 
                    <td style = "border: 1px solid #000; font-weight: bold;">'.$total_rebates_dis.'</td> 
                    <td style = "border: 1px solid #000;"></td> 
                    <td style = "border: 1px solid #000;"></td>
                    <td style = "border: 1px solid #000;"></td> 
                    <td style = "border: 1px solid #000;"></td>
                </tr>';

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }

    //This are fo those account that already has loan for this year. Kay nalate ko ug parun nag renew n sila nga wala pay cutoff year T_T
    public static function calculateYearlyCutOffWithCurrentLoan($loan_id){

        $query = new \yii\db\Query;
        $query->select('member_id')
            ->from('loanaccount la')
            ->where("loan_id = " . $loan_id) //Appliance Loan and RL.
            ->groupBy('member_id');
        $loanMember = $query->all();
        
        $currentDate = ParticularHelper::getCurrentDay();
        $systemDate = date("Y-m-d", strtotime($currentDate));
        $systemDateYear = date("Y", strtotime($currentDate));
        $cutOffPrev = Yii::$app->view->getCutOffPrevYear();
        $cutOff = Yii::$app->view->getCutOff();

        /*var_dump($cutOffPrev);
        var_dump($systemDateYear);*/

        $accountList = array();
        foreach ($loanMember as $member) {
            //Check if has cutoff
            /*if($member['member_id'] == 55){
                var_dump("expression");
        die;
            }*/

            //Get regular Loan
            $acc = \app\models\LoanAccount::find()
                ->innerJoinWith(['product', 'member'])
                ->where(['loanaccount.member_id' => $member['member_id'], 'loanaccount.loan_id' => $loan_id])
                ->andWhere('loanaccount.account_no = "2-000896" OR loanaccount.account_no = "2-001327" 
                    OR loanaccount.account_no = "2-002481" OR loanaccount.account_no = "2-002919" 
                    OR loanaccount.account_no = "2-002046" OR loanaccount.account_no = "2-002734" 
                    OR loanaccount.account_no = "2-002376" OR loanaccount.account_no = "2-002213" 
                    OR loanaccount.account_no = "2-002355" OR loanaccount.account_no = "2-002770"')
                ->andWhere('status != "Cancel" AND status != "Verified"')
                ->orderBy('release_date DESC')
                ->asArray()->one();
            /*if($member['member_id'] == 55){
                var_dump($acc);
                die;
            }*/

            //Test 2019 cutoff
            if($acc){
                $calVersion = Yii::$app->view->getVersion($acc['release_date']);

                //For appliance loan. Check if old version
                if($acc['loan_id'] == 1 && $calVersion != "1"){
                    continue;
                }

                $release_date = $acc['release_date'];
                $prepaid_interest = 0;
                $interest_accum = 0;
                $last_tran_date = $acc['release_date'];
                $total_amount_paid = 0;
                $balance_after_cutoff = 0;
                $balance_after_cutoff = $acc['principal'];
                $amount_balance = $acc['principal_balance'];

                

                $lastPayment = date('Y-m-d', strtotime($cutOffPrev . ' +1 day'));
                $lastRunningBal = $acc['principal'];
                if($release_date > $lastPayment){
                   $lastPayment =  $release_date;
                }
                
                $firstTransaction = null;
                $getTransactions = LoanTransaction::find()->where(['loan_account' => $acc['account_no'], 'is_cancelled' => "0"])
                    ->andWhere('transaction_type = "RELEASE" OR transaction_type = "PAYPARTIAL" OR transaction_type = "PAYCLOSE" OR transaction_type = "EMERGENCY"')
                    ->andWhere('transaction_date > "' . $cutOffPrev . '"')
                    ->andWhere('transaction_date < "' . $systemDateYear . '-01-01' . '"')
                    ->orderBy('date_posted')
                    ->asArray()->all();
                $transLength = count($getTransactions);

                foreach ($getTransactions as $transKey => $transaction) {

                    $last_tran_date = $transaction['transaction_date'];

                    if($transaction['transaction_date'] <= $cutOffPrev){
                        $balance_after_cutoff = $transaction['running_balance'];
                        $lastRunningBal = $transaction['running_balance'];
                        continue;
                    }


                    if($transaction['transaction_type'] == "PAYPARTIAL"){
                        if($transaction['amount'] > 0){
                            $total_amount_paid = $total_amount_paid + $transaction['amount'];
                        }
                    }

                    if($transaction['prepaid_intpaid'] && floatval($transaction['prepaid_intpaid']) > 0){
                        $prepaid_interest = $prepaid_interest + $transaction['prepaid_intpaid'];
                    }

                    //Recalculate interest earned. Dili magsalig sa DB
                    $interestEarned = LoanHelper::getInterest($lastPayment, $transaction['transaction_date'], $lastRunningBal, $acc['int_rate']);
                    $interest_accum = $interest_accum + $interestEarned;
                    //var_dump( $interestEarned . " - " . $lastPayment . " - "  . $transaction['date_posted'] . " - " . $lastRunningBal . " - " . $acc['int_rate'] );

                    //If at the last transaction
                    if($transLength == $transKey+1){
                        $interestEarnedLast = LoanHelper::getInterest($transaction['transaction_date'], $cutOff, $transaction['running_balance'], $acc['int_rate']);
                        $interest_accum = $interest_accum + $interestEarnedLast;
                    }

                    $lastPayment = $transaction['transaction_date'];
                    $lastRunningBal = $transaction['running_balance'];
                    $amount_balance = $transaction['running_balance'];

                }

                //CUT OF PI AND INTEREST
                if($acc['loan_id'] == 2 || ($acc['loan_id'] == 1 && $calVersion == "1") ) {// Regular loan
                    //If loan exist before cut off. Get Cut Off Data
                    if($release_date <= $cutOffPrev){
                        $cutOffYear = date('Y', strtotime($cutOffPrev));
                        $getCutOff = LoanHelper::getCutOff($cutOffYear, $acc['loan_id'], $acc['member_id']);

                        if($getCutOff){
                            $prepaid_interest += $getCutOff['finalPi'];
                            $interest_accum += $getCutOff['finalInt'];
                        }
                    }
                    
                }
                $prepaid_interest = round($prepaid_interest, 2);
                $interest_accum = round($interest_accum, 2);
                $acc['cutoff_pi'] =  $prepaid_interest; // In case of adjustment in the vue, add orig var
                $acc['cutoff_int'] =  $interest_accum; // In case of adjustment in the vue, add orig var
                $acc['cutoff_pi_orig'] =  $prepaid_interest;
                $acc['cutoff_int_orig'] =  $interest_accum;

                if($prepaid_interest > 0 || $interest_accum > 0){
                    array_push($accountList, $acc);
                }

            }
        }
        return $accountList;
    }

    public static function calculateYearlyCutOff($loan_id){

        $query = new \yii\db\Query;
        $query->select('member_id')
            ->from('loanaccount la')
            ->where("loan_id = " . $loan_id) //Appliance Loan and RL.
            ->groupBy('member_id');
        $loanMember = $query->all();
        
        $currentDate = ParticularHelper::getCurrentDay();
        $systemDate = date("Y-m-d", strtotime($currentDate));
        $systemDateYear = date("Y", strtotime($currentDate));
        $cutOffPrev = Yii::$app->view->getCutOffPrevYear();
        $cutOff = Yii::$app->view->getCutOff();

        $accountList = array();
        foreach ($loanMember as $member) {
            //Check if has cutoff

            //Get regular Loan
            $acc = \app\models\LoanAccount::find()
                ->innerJoinWith(['product', 'member'])
                ->where(['loanaccount.member_id' => $member['member_id'], 'loanaccount.loan_id' => $loan_id])
                ->andWhere('status != "Cancel" AND status != "Verified"')
                ->orderBy('release_date DESC')
                ->asArray()->one();

            //Test 2019 cutoff
            if($acc){
                $calVersion = Yii::$app->view->getVersion($acc['release_date']);

                //For appliance loan. Check if old version
                if($acc['loan_id'] == 1 && $calVersion != "1"){
                    continue;
                }

                $release_date = $acc['release_date'];
                $prepaid_interest = 0;
                $interest_accum = 0;
                $last_tran_date = $acc['release_date'];
                $total_amount_paid = 0;
                $balance_after_cutoff = 0;
                $balance_after_cutoff = $acc['principal'];
                $amount_balance = $acc['principal_balance'];

                

                $lastPayment = date('Y-m-d', strtotime($cutOffPrev . ' +1 day'));
                $lastRunningBal = $acc['principal'];
                if($release_date > $lastPayment){
                   $lastPayment =  $release_date;
                }
                
                $firstTransaction = null;
                $getTransactions = LoanTransaction::find()->where(['loan_account' => $acc['account_no'], 'is_cancelled' => "0"])
                    ->andWhere('transaction_type = "RELEASE" OR transaction_type = "PAYPARTIAL" OR transaction_type = "PAYCLOSE" OR transaction_type = "EMERGENCY"')
                    ->andWhere('transaction_date > "' . $cutOffPrev . '"')
                    ->andWhere('transaction_date < "' . $systemDateYear . '-01-01' . '"')
                    ->orderBy('date_posted')
                    ->asArray()->all();
                $transLength = count($getTransactions);

                foreach ($getTransactions as $transKey => $transaction) {

                    $last_tran_date = $transaction['transaction_date'];

                    if($transaction['transaction_date'] <= $cutOffPrev){
                        $balance_after_cutoff = $transaction['running_balance'];
                        $lastRunningBal = $transaction['running_balance'];
                        continue;
                    }


                    if($transaction['transaction_type'] == "PAYPARTIAL"){
                        if($transaction['amount'] > 0){
                            $total_amount_paid = $total_amount_paid + $transaction['amount'];
                        }
                    }

                    if($transaction['prepaid_intpaid'] && floatval($transaction['prepaid_intpaid']) > 0){
                        $prepaid_interest = $prepaid_interest + $transaction['prepaid_intpaid'];
                    }

                    //Recalculate interest earned. Dili magsalig sa DB
                    $interestEarned = LoanHelper::getInterest($lastPayment, $transaction['transaction_date'], $lastRunningBal, $acc['int_rate']);
                    $interest_accum = $interest_accum + $interestEarned;
                    //var_dump( $interestEarned . " - " . $lastPayment . " - "  . $transaction['date_posted'] . " - " . $lastRunningBal . " - " . $acc['int_rate'] );

                    //If at the last transaction
                    if($transLength == $transKey+1){
                        $interestEarnedLast = LoanHelper::getInterest($transaction['transaction_date'], $cutOff, $transaction['running_balance'], $acc['int_rate']);
                        $interest_accum = $interest_accum + $interestEarnedLast;
                    }

                    $lastPayment = $transaction['transaction_date'];
                    $lastRunningBal = $transaction['running_balance'];
                    $amount_balance = $transaction['running_balance'];

                }
                //CUT OF PI AND INTEREST
                if($acc['loan_id'] == 2 || ($acc['loan_id'] == 1 && $calVersion == "1") ) {// Regular loan
                    //If loan exist before cut off. Get Cut Off Data
                    if($release_date <= $cutOffPrev){
                        $cutOffYear = date('Y', strtotime($cutOffPrev));
                        $getCutOff = LoanHelper::getCutOff($cutOffYear, $acc['loan_id'], $acc['member_id']);

                        if($getCutOff){
                            $prepaid_interest += $getCutOff['finalPi'];
                            $interest_accum += $getCutOff['finalInt'];
                        }
                    }
                    
                }
                $prepaid_interest = round($prepaid_interest, 2);
                $interest_accum = round($interest_accum, 2);
                $acc['cutoff_pi'] =  $prepaid_interest; // In case of adjustment in the vue, add orig var
                $acc['cutoff_int'] =  $interest_accum; // In case of adjustment in the vue, add orig var
                $acc['cutoff_pi_orig'] =  $prepaid_interest;
                $acc['cutoff_int_orig'] =  $interest_accum;

                if($prepaid_interest > 0 || $interest_accum > 0){
                    array_push($accountList, $acc);
                }

            }
        }
        return $accountList;
    }

    //This is use for dividen ang refund
    public static function getLoanInterestEarned($loans, $year){

        $currentDate = ParticularHelper::getCurrentDay();
        $systemDate = date("Y-m-d", strtotime($currentDate));
        $systemDateYear = date("Y", strtotime($currentDate));
        $cutOffPrev = Yii::$app->view->getCutOffPrevYear();
        $yearStart = Yii::$app->view->getCutOff();

        $accountList = array();
        $totalLoanInterest = 0;
        if(count($loans) >= 1){
            foreach ($loans as $loan) {
                $arr = $loan;
                $totalInterestEarned = 0;
                //Get interest for loan other than regular
                if($loan['loan_id'] != 2){
                    //Get loan transaction for that year
                    $getTransactions = LoanTransaction::find()
                        ->select(['loan_transaction.*', 'la.release_date as loan_release_date', 'la.loan_id as loan_loan_id', 'la.prepaid_int as loan_prepaid_int', 'la.account_no as loan_account_no'])
                        ->innerJoin('loanaccount la', 'la.account_no = loan_transaction.loan_account')
                        ->where(['la.loan_id' => $loan['loan_id'], 'la.member_id' => $loan['member_id'], 'is_cancelled' => "0"])
                        ->andWhere('loan_transaction.transaction_type = "PAYPARTIAL"')
                        ->andWhere("DATE_FORMAT(loan_transaction.transaction_date, '%Y') = '$year'")
                        ->orderBy('loan_transaction.date_posted')
                        ->asArray()->all();

                    $transLength = count($getTransactions);

                    if($transLength > 0){

                        $account_no = null;
                        foreach ($getTransactions as $transKey => $trans) {
                            $calVersion = Yii::$app->view->getVersion($trans['loan_release_date']);
                            //Do not include appliance loan old calculation
                            if(($calVersion == "1" && $trans['loan_loan_id'] == 1)){
                                continue;
                            }
                            
                            if($trans['running_balance'] == 0){
                                continue;
                            }
                            $getInterestLoan = static::getInterestLoan($trans['amount'], $trans['loan_release_date'], $trans['loan_prepaid_int'], $trans['loan_loan_id']);
                            $totalInterestEarned += floatval($getInterestLoan);

                            $account_no = $trans['loan_account_no'];
                        }
                        $arr['transaction'] = $getTransactions;
                    }
                }
                
                if($loan['loan_id'] == 2 || $loan['loan_id'] == 1){
                    $getInterestRegularAppliance = static::getInterestRegularAppliance($loan['member_id'], $loan['loan_id'], 2019);
                    if($loan['loan_id'] == 1){
                        $totalInterestEarned += $getInterestRegularAppliance['totalInterestEarned'];
                    }
                    else{
                        $totalInterestEarned = $getInterestRegularAppliance['totalInterestEarned'];
                    }

                }

                if($totalInterestEarned > 0){
                    $arr['totalInterestEarned'] = round($totalInterestEarned, 2);
                    array_push($accountList, $arr);
                    $totalLoanInterest += $totalInterestEarned;
                }
                
            }
        }

        return ['accountList' => $accountList, 'totalLoanInterest' => $totalLoanInterest];
    }

    //This is use for cut off only
    public static function getInterestRegularAppliance($member_id, $loan_id, $year){
        $totalInterestEarned = 0;
        //Get loan transaction for that year
        $getTransactions = LoanTransaction::find()
            ->select(['loan_transaction.*', 'la.release_date as loan_release_date', 'la.loan_id as loan_loan_id', 'la.prepaid_int as loan_prepaid_int', 'la.account_no as loan_account_no', 'la.int_rate as loan_int_rate'])
            ->innerJoin('loanaccount la', 'la.account_no = loan_transaction.loan_account')
            ->where(['la.loan_id' => $loan_id, 'la.member_id' => $member_id, 'is_cancelled' => "0"])
            ->andWhere('loan_transaction.transaction_type = "RELEASE" OR loan_transaction.transaction_type = "PAYPARTIAL" OR loan_transaction.transaction_type = "PAYCLOSE" OR loan_transaction.transaction_type = "EMERGENCY"')
            ->andWhere("DATE_FORMAT(loan_transaction.transaction_date, '%Y') = '$year'")
            ->orderBy('loan_transaction.date_posted, loan_transaction.id')
            ->asArray()->all();

        $transLength = count($getTransactions);
        $startYear = $year . "-01-01";
        $endYear = $year . "-12-31";

        if($transLength > 0){
            $account_no = null;
            $count = 0;
            //Get last running balance
            $getLastTransactions = LoanTransaction::find()
            ->innerJoin('loanaccount la', 'la.account_no = loan_transaction.loan_account')
            ->where(['la.loan_id' => $loan_id, 'la.member_id' => $member_id, 'is_cancelled' => "0"])
            ->andWhere('loan_transaction.transaction_type = "RELEASE" OR loan_transaction.transaction_type = "PAYPARTIAL" OR loan_transaction.transaction_type = "PAYCLOSE" OR loan_transaction.transaction_type = "EMERGENCY"')
            ->andWhere("loan_transaction.transaction_date < '$startYear'")
            ->orderBy('loan_transaction.date_posted DESC')
            ->asArray()->one();
            $lastRunningBal = $getLastTransactions ? $getLastTransactions['running_balance'] : 0;
            $lastPayment = $startYear;


            foreach ($getTransactions as $transKey => $trans) {
                $calVersion = Yii::$app->view->getVersion($trans['loan_release_date']);
                //If appliance and new release. Continue
                if($calVersion != "1" && $trans['loan_loan_id'] == 1){
                    continue;
                }
                
                $interestEarned = LoanHelper::getInterest($lastPayment, $trans['transaction_date'], $lastRunningBal, $trans['loan_int_rate']);
                $totalInterestEarned = $totalInterestEarned + $interestEarned;

                //If at the last transaction
                if($transLength == $transKey+1){
                    $interestEarnedLast = LoanHelper::getInterest($trans['transaction_date'], $endYear, $trans['running_balance'], $trans['loan_int_rate']);
                    $totalInterestEarned = $totalInterestEarned + $interestEarnedLast;
                }

                $lastPayment = $trans['transaction_date'];
                $lastRunningBal = $trans['running_balance'];
            }
            $arr['transaction'] = $getTransactions;
        }

        return ['transaction' => $getTransactions, 'totalInterestEarned' => $totalInterestEarned];
    }

    public static function getInterestLoan($amount, $release_date, $prepaid_int, $product_id){
        $amt = 0;
        $calVersion = Yii::$app->view->getVersion($release_date);

        if($calVersion == '1-2020.08'){ // //New policy update from August 2020
            $amt = $amount * floatval($prepaid_int);
        }
        else{
            if(in_array(intval($product_id), [3,5,6,8,12,14]) >= 0){
                $amt = $amount * floatval($prepaid_int) / (1 + floatval($prepaid_int));
            }
            else{
                $amt = $amount * floatval($prepaid_int);
            }
        }

        return $amt;
        
    }

    public static function getInterestByAccount($account){

        $currentDate = ParticularHelper::getCurrentDay();
        $systemDate = date("Y-m-d", strtotime($currentDate));
        $cutOff = Yii::$app->view->getCutOff();
        $release_date = $account['release_date'];

        $getTransactions = LoanTransaction::find()->where(['loan_account' => $account['account_no'], 'is_cancelled' => "0"])
                    ->andWhere('transaction_type = "RELEASE" OR transaction_type = "PAYPARTIAL" OR transaction_type = "PAYCLOSE" OR transaction_type = "EMERGENCY"')
                    ->andWhere('transaction_date <= "' . $systemDate . '"')
                    ->orderBy('date_posted')
                    ->asArray()->all();
        $transLength = count($getTransactions);

        $lastPayment = date('Y-m-d', strtotime($cutOff . ' +1 day'));
        $lastRunningBal = $account['principal'];
        if($release_date > $lastPayment){
           $lastPayment =  $release_date;
        }
        $interest_accum = 0;
        $total_amount_paid = 0;
        $prepaid_interest = 0;

        foreach ($getTransactions as $transKey => $transaction) {

            $last_tran_date = $transaction['transaction_date'];

            if($transaction['transaction_date'] <= $cutOff){
                $balance_after_cutoff = $transaction['running_balance'];
                $lastRunningBal = $transaction['running_balance'];
                continue;
            }

            if($transaction['transaction_type'] == "PAYPARTIAL"){
                if($transaction['amount'] > 0){
                    $total_amount_paid = $total_amount_paid + $transaction['amount'];
                }
            }

            if($transaction['prepaid_intpaid'] && floatval($transaction['prepaid_intpaid']) > 0){
                $prepaid_interest = $prepaid_interest + $transaction['prepaid_intpaid'];
            }

            //Recalculate interest earned. Dili magsalig sa DB
            $interestEarned = LoanHelper::getInterest($lastPayment, $transaction['transaction_date'], $lastRunningBal, $account['int_rate']);
            $interest_accum = $interest_accum + $interestEarned;
            //var_dump($interestEarned);

            //If at the last transaction
            if($transLength == $transKey+1){
                $interestEarnedLast = LoanHelper::getInterest($transaction['transaction_date'], $systemDate, $transaction['running_balance'], $account['int_rate']);
                $interest_accum = $interest_accum + $interestEarnedLast;
                //var_dump($interestEarned);
            }

            $lastPayment = $transaction['transaction_date'];
            $lastRunningBal = $transaction['running_balance'];
            $amount_balance = $transaction['running_balance'];
        }

        return ['interest_earned' => $interest_accum];
    }

}