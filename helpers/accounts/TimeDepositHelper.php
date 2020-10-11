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
        $days_in_year = 365;//static::daysInYear();
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
        $amount = $postData['amount'];
        $transaction = $postData['transaction'];

        $account = $postData['account'];
        $open_date = $account['open_date'];
        $maturity_date = $account['maturity_date'];
        $listTemplate = '';
        $listTemplate .= Yii::$app->params['formTemplate']['header_layout'];

        $accountDetail = '<table>
            <tr>
                <td style = "font-weight: bold;">Account Name: </td> 
                <td><span>[account_name]</span></td>
                <td width = "100px"></td>

                <td style = "font-weight: bold;">Open Date: </td> 
                <td><span>[open_date]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Account Number: </td> 
                <td>[account_no] </td>
                <td></td>

                <td style = "font-weight: bold;">Mature Date: </td> 
                <td><span>[maturity_date]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Amount: </td> 
                <td>[amount]</td>
            </tr>
        </table>';
        $accountDetail = str_replace('[account_name]', $account_name, $accountDetail);
        $accountDetail = str_replace('[account_no]', $account_no, $accountDetail);
        $accountDetail = str_replace('[amount]', Yii::$app->view->formatNumber($amount), $accountDetail);
        $accountDetail = str_replace('[open_date]', $open_date, $accountDetail);
        $accountDetail = str_replace('[maturity_date]', $maturity_date, $accountDetail);

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
                //$transDate = date('Y-m-d', strtotime($trans['transaction_date']));
                $amount = $trans['amount'] && floatval($trans['amount']) > 0 ? Yii::$app->view->formatNumber($trans['amount']) : "";
                $balance = $trans['balance'] && floatval($trans['balance']) > 0 ? Yii::$app->view->formatNumber($trans['balance']) : "";
                $transTable .= '<tr>
                    <td>'.$trans['transaction_type'].'</td> 
                    <td>'.$amount.'</td> 
                    <td>'.$trans['remarks'].'</td> 
                    <td>'.$balance.'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }

    public static function listingPrint($postData){
        $status = $postData['status'];
        $transaction = $postData['transaction'];

        $statusStr = ucfirst(strtolower($status));
        $title = "List of " . $statusStr . " Time Deposit Accounts (Computation with Compound Interest)";
        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">'.$title.'</div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">As Of '.date("Y-m-d").'</div></tr>
        </table>';

        $accountDetail = '<table class = "mt-20">
            <tr>
                <td style = "font-weight: bold;">Total Principal Amount: </td> 
                <td><span>[total_principal_amount]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Total Interest Expense: </td> 
                <td>[total_interest_expense] </td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Total Amount Matured: </td> 
                <td>[total_amount_matured]</td>
            </tr>
        </table>';

        $total_principal_amount = 0;
        $total_interest_expense = 0;
        $total_amount_matured = 0;
        if(count($transaction) > 0){
            $transTable = '<table class = "table table-bordered mt-10" width = "100%">
                <tr>
                    <th>Account Name</th> 
                    <th>Account Number</th> 
                    <th>Date Open</th> 
                    <th>Principal Amount</th> 
                    <th>Interest Expense</th> 
                    <th>Amount Matured</th> 
                </tr>';

            foreach ($transaction as $trans) {
                $fullname = $trans['member'] ? $trans['member']['fullname'] : $trans['account_name'];
                $open_date = date('Y-m-d', strtotime($trans['open_date']));
                $amount = $trans['amount'] && floatval($trans['amount']) > 0 ? floatval($trans['amount']) : 0;
                $amount_mature = $trans['amount_mature'] && floatval($trans['amount_mature']) > 0 ? floatval($trans['amount_mature']) : 0;
                $interest_expense = $amount_mature - $amount;

                if(count($trans['transactions']) > 0){
                    $interest_expense = 0;
                    foreach ($trans['transactions'] as $tdTrans) {
                        if($tdTrans['transaction_type'] == 'TDINTEREST'){
                            $interest_expense = floatval($tdTrans['amount']);
                        }
                    }
                    $amount_mature = $amount + $interest_expense;
                }

                $total_principal_amount += $amount;
                $total_interest_expense += $interest_expense;
                $total_amount_matured += $amount_mature;
                
                $amount_str = $amount > 0 ? Yii::$app->view->formatNumber($amount) : "";
                $amount_mature_str = $amount_mature > 0 ? Yii::$app->view->formatNumber($amount_mature) : "";
                $interest_expense_str = $interest_expense > 0 ? Yii::$app->view->formatNumber($interest_expense) : "";

                $transTable .= '<tr>
                    <td>'.$fullname.'</td> 
                    <td>'.$trans['account_no'].'</td> 
                    <td>'.$open_date.'</td> 
                    <td>'.$amount_str.'</td> 
                    <td>'.$interest_expense_str.'</td> 
                    <td>'.$amount_mature_str.'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $accountDetail = str_replace('[total_principal_amount]', Yii::$app->view->formatNumber($total_principal_amount), $accountDetail);
        $accountDetail = str_replace('[total_interest_expense]', Yii::$app->view->formatNumber($total_interest_expense), $accountDetail);
        $accountDetail = str_replace('[total_amount_matured]', Yii::$app->view->formatNumber($total_amount_matured), $accountDetail);

        $listTemplate .= $accountDetail;

        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }
}