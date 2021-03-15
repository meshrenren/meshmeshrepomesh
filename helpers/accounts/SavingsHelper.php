<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\SavingAccounts;
use \app\models\Savingsproduct;
use \app\models\SavingsTransaction;

use app\helpers\GlobalHelper;

class SavingsHelper 
{
    public static function getProduct($filter, $asArray = false){
        $getProduct= Savingsproduct::find()->where($filter);
        if($asArray){
            $getProduct = $getProduct->asArray();
        }
        $getProduct = $getProduct->one();

        return $getProduct;
    }

    public static function getMemberSavings($member_id, $asArray = true){
        $account = SavingAccounts::find()->innerJoinWith(['product', 'member'])
            ->where(['member_id' => $member_id]);

        if($asArray){
            return $account->asArray()->one();
        }
        return $account->one();
    }

	public static function getAccountSavingsInfo($filter = [], $asArray = true){

        $accountList = SavingAccounts::find()->innerJoinWith(['product']);
        if(isset($filter['member_id'])){
            $accountList = $accountList->where(['member_id' => $filter['member_id']]);
        }
        else if(isset($filter['name'])){
            $accountList = $accountList->where(['account_name' => 'name', 'type' => 'Group']);
        }else{
            $accountList = $accountList->joinWith(['member']);
        }
        
        if($asArray){
            return $accountList->asArray()->all();
        }
        return $accountList->all();
	}

    public static function getTransaction($fk_savings_id = null, $filter = null){
        $model = SavingsTransaction::find();
        if($fk_savings_id != null){
            $model = $model->where(['fk_savings_id' => $fk_savings_id]);
        }
        
        return $model->orderBy('posted_date, id')->asArray()->all();
    }

    public static function saveSavingsTransaction($data){
        $model = new SavingsTransaction;
        $model->attributes = $data;
        $model->transaction_date = isset($data['transaction_date']) ? $data['transaction_date'] : \Yii::$app->user->identity->DateNow;
        $model->posted_date = \Yii::$app->user->identity->DateNow;
        $model->transacted_by = \Yii::$app->user->identity->id;
        if(isset($data['reference_number'])){
            $model->ref_no=$data['reference_number'];
        }
        else if(isset($data['ref_no'])){
            $model->ref_no=$data['ref_no'];
        }

        if($model->save()){
            return $model;
        }

        else{
            var_dump($model->getErrors());
        }
        return null;

                
    }

    public static function printList($postData){
        $account_no = $postData['account_no'];
        $account_name = $postData['account_name'];
        $balance = $postData['balance'];
        $transaction = $postData['transaction'];
        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">Savings Account Transaction</div></tr>
        </table>';

        $accountDetail = '<table>
            <tr>
                <td style = "font-weight: bold;">Account Name: </td> 
                <td><span>[account_name]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Account Number: </td> 
                <td>[account_no] </td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Balance: </td> 
                <td>[balance]</td>
            </tr>
        </table>';
        $accountDetail = str_replace('[account_name]', $account_name, $accountDetail);
        $accountDetail = str_replace('[account_no]', $account_no, $accountDetail);
        $accountDetail = str_replace('[balance]', Yii::$app->view->formatNumber($balance), $accountDetail);

        $listTemplate .= $accountDetail;

        if(count($transaction) > 0){
            $transTable = '<table class = "table table-bordered mt-10" width = "100%">
                <tr>
                    <th>Date Transact</th> 
                    <th>In</th> 
                    <th>Out</th> 
                    <th>Transaction Type</th> 
                    <th>Reference No</th> 
                    <th>Running Balance</th> 
                    <th>Remarks</th> 
                </tr>';
            foreach ($transaction as $trans) {
                $transDate = date('Y-m-d', strtotime($trans['transaction_date']));
                $amount_in = $trans['amount_in'] && floatval($trans['amount_in']) > 0 ? Yii::$app->view->formatNumber($trans['amount_in']) : "";
                $amount_out = $trans['amount_out'] && floatval($trans['amount_out']) > 0 ? Yii::$app->view->formatNumber($trans['amount_out']) : "";
                $transTable .= '<tr>
                    <td>'.$transDate.'</td> 
                    <td>'.$amount_in.'</td> 
                    <td>'.$amount_out.'</td> 
                    <td>'.$trans['transaction_type'].'</td> 
                    <td>'.$trans['ref_no'].'</td> 
                    <td>'.Yii::$app->view->formatNumber($trans['running_balance']).'</td> 
                    <td>'.$trans['remarks'].'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        $listTemplate .= $accountDetail;

        return $listTemplate;
    }

    public static function transactionSavings($savingsDetails){
        $success = false;
        $error = null;

        $account_no = $savingsDetails['account_no'];
        $remarks = $savingsDetails['remarks'];
        $ref_num = $savingsDetails['ref_num'];
        $amount = $savingsDetails['amount'];
        $transaction_date = $savingsDetails['transaction_date'];
        $transaction_type = $savingsDetails['transaction_type'];
        $posted_date = \Yii::$app->user->identity->DateNow;

        $savingsaccount = SavingAccounts::findOne($savingsDetails['account_no']);
        $savingsproduct = Savingsproduct::findOne($savingsaccount->saving_product_id);

        $running_balance = $savingsaccount->balance;
        if($transaction_type == "CASHDEP" || $transaction_type == "CANCASHDEP"){
            $running_balance = $savingsaccount->balance + $amount;
        }
        else if($transaction_type == "WITHDRWL" || $transaction_type == "CANWITHDRWL"){
            $running_balance = $savingsaccount->balance - $amount;
        }
        

        $savingstransaction = new SavingsTransaction();
        $savingstransaction->fk_savings_id = $account_no;
        $savingstransaction->amount = $amount;
        $savingstransaction->transaction_type = $transaction_type;
        $savingstransaction->transacted_by = \Yii::$app->user->identity->id;
        $savingstransaction->transaction_date = date('Y-m-d H:i:s', strtotime($transaction_date));
        $savingstransaction->posted_date = date('Y-m-d', strtotime($posted_date));
        $savingstransaction->running_balance = $running_balance;
        $savingstransaction->remarks = $remarks;
        $savingstransaction->ref_no = $ref_num;
        
        $savingsaccount->balance = $running_balance;
        
        if($savingsaccount->save() && $savingstransaction->save())
        {
            $success = true;
            
        }
        else
        {
            $error = $savingstransaction->errors;
            $success = false;
        }

        return ['success' => $success, 'error' => $error];
    }

    public static function cancelReference($ref_no, $cancelled_date){
        $transactionList = SavingsTransaction::find()->where(['ref_no' => $ref_no])->all();
        $success = true;

        foreach ($transactionList as $transaction) {
            $savingsDetails['account_no'] = $transaction->fk_share_id;
            $savingsDetails['remarks'] = "posted as cancel from ".$transaction->ref_no;
            $savingsDetails['ref_num'] = 'CAN'.$transaction->ref_no;
            $savingsDetails['amount'] = $transaction->amount * -1;
            $savingsDetails['transaction_date'] = \Yii::$app->user->identity->DateTimeNow;
            $savingsDetails['transaction_type'] = 'CAN'.$transaction->transaction_type;

            $savingsTransaction = static::transactionSavings($savingsDetails);
            if($savingsTransaction['success']){
                $transaction->is_cancelled = 1;
                $transaction->save();
            } 
            else{
                $success = false;
            }
        }
        return ['success' => $success];
    }

    //Cut off interest earned within the year
    public static function calculateCutOffInterest($year){

        $savingsaccounts = SavingAccounts::find()->joinWith(['member'])->where('savingsaccount.balance >= 0 AND savingsaccount.is_active = 1')->asArray()->all();

        $int_rate = GlobalHelper::getSAInterest() / 365;
        $accountList = [];

        foreach ($savingsaccounts as $acc) {

            //Get last balance from last year
            $lastTrans = SavingsTransaction::find()->where(['fk_savings_id' => $acc['account_no']])->andWhere("DATE_FORMAT(transaction_date, '%Y') < '".$year."'")->orderBy('transaction_date DESC, id DESC')->one();

            $lastDate = date('Y', strtotime($acc['date_created'])) == $year ? date('Y-m-d', strtotime($acc['date_created'])) : $year . "-01-01";
            $lastBalance = 0;
            if($lastTrans){
                //$lastDate = $year . "-01-01";
                $lastDate = $lastTrans->transaction_date;
                $lastBalance = $lastTrans->running_balance;

            }

            $totalInterest = 0;

            //get all transaction within the year and calculate interest
            $transactionList = SavingsTransaction::find()->where(['fk_savings_id' => $acc['account_no']])->andWhere("DATE_FORMAT(transaction_date, '%Y') = '$year'")->orderBy('transaction_date ASC')->asArray()->all();
            if(count($transactionList) > 0){
                foreach ($transactionList as $trans) {

                    $noOfDaysPassed = date_diff(date_create($lastDate), date_create($trans['transaction_date']));
                    $noOfDaysPassed = $noOfDaysPassed->format("%a");
                    //var_dump($noOfDaysPassed . " - " . $lastBalance);
                    //To calculate interest
                    $interest = ($int_rate * floatval($lastBalance)) * $noOfDaysPassed;
                    if($interest > 0){
                        $totalInterest += $interest;
                    }

                    //var_dump($lastDate . " - " . $interest);

                    $lastDate = $trans['transaction_date'];
                    $lastBalance = $trans['running_balance'];

                }
            }
            else{
                $lastBalance = $acc['balance'];
            }

            //Calculate last interest up to end of the year
            $noOfDaysPassed = date_diff(date_create($lastDate), date_create($year . "-12-31"));
            $noOfDaysPassed = $noOfDaysPassed->format("%a");
            //To calculate interest
            $interest = ($int_rate * floatval($lastBalance)) * $noOfDaysPassed;
            if($interest > 0){
                $totalInterest += $interest;
            } 

            if($totalInterest > 0){
                $acc['total_interest'] = $totalInterest;
                $acc['total_interest']= number_format($acc['total_interest'], 2, '.', '');
                array_push($accountList, $acc);
            }
            
        }

        return $accountList;
    }

    public static function printCutOff($postData){
        $totalInterestEarned = $postData['totalInterestEarned'];
        $transaction = $postData['transaction'];
        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">Savings Interest Earned</div></tr>
        </table>';

        $accountDetail = '<table>
            <tr>
                <td style = "font-weight: bold;">Total Interest Earned: </td> 
                <td><span>[total_interest]</span></td>
            </tr> 
        </table>';

        $accountDetail = str_replace('[total_interest]', Yii::$app->view->formatNumber($totalInterestEarned), $accountDetail);

        $listTemplate .= $accountDetail;
        $totalBalance = 0;
        $totalInterest = 0;

        if(count($transaction) > 0){
            $transTable = '<table class = "table table-bordered mt-10" width = "100%">
                <tr>
                    <th>ID</th> 
                    <th>Name</th> 
                    <th>Balance</th> 
                    <th>Interest Earned</th> 
                </tr>';
            foreach ($transaction as $trans) {
                $fullName = $trans['account_name'];
                if(!$fullName){
                    $fullName = $trans['member']['fullname'];
                }
                $totalBalance += $trans['balance'];
                $totalInterest += $trans['total_interest'];
                $transTable .= '<tr>
                    <td>'.$trans['account_no'].'</td> 
                    <td>'.$fullName.'</td> 
                    <td>'.Yii::$app->view->formatNumber($trans['balance']).'</td> 
                    <td>'.Yii::$app->view->formatNumber($trans['total_interest']).'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }

        $listTemplate = $listTemplate . $transTable;

        $accountDetail2 = '<table>
            <tr>
                <td style = "font-weight: bold;">Total Balance: </td> 
                <td><span>[total_balance]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Total Interest Earned: </td> 
                <td><span>[total_interest]</span></td>
            </tr>
        </table>';

        $accountDetail2 = str_replace('[total_balance]', Yii::$app->view->formatNumber($totalBalance), $accountDetail2);
        $accountDetail2 = str_replace('[total_interest]', Yii::$app->view->formatNumber($totalInterest), $accountDetail2);

        $listTemplate .= $accountDetail2;

        return $listTemplate;
    }
}