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

    public static function getSavingsCalculation($tdAccount){
        $tdAccount = (object) $tdAccount;

        $currentDate = ParticularHelper::getCurrentDay();
        $systemDate = date("Y-m-d", strtotime($currentDate));

        $diff_days = GlobalHelper::getDiffDays($tdAccount->maturity_date, $systemDate);
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
}