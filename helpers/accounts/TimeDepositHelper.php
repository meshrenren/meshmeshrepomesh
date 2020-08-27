<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\TimeDepositAccount;
use \app\models\TimeDepositTransaction;
use \app\models\TimeDepositRateTable;

use \app\helpers\GlobalHelper;
use \app\helpers\particulars\ParticularHelper;

class TimeDepositHelper 
{

    public static function getAccountTDInfo($member_id) {
        
        $accountList = TimeDepositAccount::find()->innerJoinWith(['product']);
        if(isset($filter['member_id'])){
            $accountList = $accountList->joinWith(['member'])->where(['member_id' => $filter['member_id']]);
        }
        else if(isset($filter['name'])){
            $accountList = $accountList->where(['account_name' => 'name', 'type' => 'Group']);
        }
        
        $accountList = $accountList->asArray()->all();
        
        return $accountList;
    }

    public static function getSavingsCalculation($tdAccount, $dateProcess = null){
        $tdAccount = (object) $tdAccount;

        $currentDate = ParticularHelper::getCurrentDay();
        $systemDate = date("Y-m-d", strtotime($currentDate));
        if($dateProcess == null){
            $dateProcess = $systemDate;
        }

        $diff_days = GlobalHelper::getDiffDays($tdAccount->maturity_date, $dateProcess);
        $sa_int_rate = GlobalHelper::getSAInterest();
        $days_in_year = static::daysInYear();

        $balance = $tdAccount->balance;
        $interest = 0;
        if($diff_days > 0 && $balance > 0){
            //Get savings interest
            $monthInDiffDays = $diff_days / $days_in_year;
            $monthInterest = $monthInDiffDays * $sa_int_rate;
            $interest = $balance * $monthInterest;
        }
        return round($interest, 2);
    }

    public static function daysInYear($year = null){
        if($year == null){
            $year = date("Y");
        }
        $days=0; 
        for($month=1;$month<=12;$month++){ 
            $days = $days + cal_days_in_month(CAL_GREGORIAN,$month,$year);
        }
        return $days;
    }

    public static function getInterestRate($term, $amount){
        //As of now Td only has one product
        $rateTable = TimeDepositRateTable::find()->where(['days' => $term])->all();
        $rate = 0;
        foreach ($rateTable as $rate) {
            if($amount >= $rate->min_amount && $amount <= $rate->max_amount){
                $rate = $rate->interest_rate;
                break;
            }
        }

        return $rate;
    }

    public static function printList($postData){
        $account_no = $postData['account_no'];
        $account_name = $postData['account_name'];
        $balance = $postData['balance'];
        $transaction = $postData['transaction'];
        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">Time Deposit Account</div></tr>
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
                <td style = "font-weight: bold;">Amount: </td> 
                <td>[amount]</td>
            </tr>
        </table>';
        $accountDetail = str_replace('[account_name]', $account_name, $accountDetail);
        $accountDetail = str_replace('[account_no]', $account_no, $accountDetail);
        $accountDetail = str_replace('[amount]', Yii::$app->view->formatNumber($amount), $accountDetail);

        $listTemplate .= $accountDetail;

        if(count($transaction) > 0){
            $transTable = '<table class = "table table-bordered mt-10" width = "100%">
                <tr>
                    <th>Transaction</th> 
                    <th>Amount</th> 
                    <th>Remarks</th> 
                    <th>Balance</th> 
                </tr>';
            foreach ($transaction as $trans) {
                $transDate = date('Y-m-d', strtotime($trans['transaction_date']));
                $amount = $trans['amount'] && floatval($trans['amount']) > 0 ? Yii::$app->view->formatNumber($trans['amount']) : "";
                $amount = $trans['balance'] && floatval($trans['balance']) > 0 ? Yii::$app->view->formatNumber($trans['balance']) : "";
                $transTable .= '<tr>
                    <td>'.$transDate.'</td> 
                    <td>'.$amount.'</td> 
                    <td>'.$trans['remarks'].'</td> 
                    <td>'.$balance.'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        $listTemplate .= $accountDetail;

        return $listTemplate;
    }
}