<?php

namespace app\helpers\accounts;

use Yii;
use app\models\SavingsAccount;
use app\models\SavingsTransaction;

class SavingsHelper 
{

	public static function getAccountSavingsInfo($filter){

        $accountList = SavingsAccount::find()->innerJoinWith(['product']);
        if(isset($filter['member_id'])){
            $accountList = $accountList->joinWith(['member'])->where(['member_id' => $filter['member_id']]);
        }
        else if(isset($filter['name'])){
            $accountList = $accountList->where(['account_name' => 'name', 'type' => 'Group']);
        }
        
        $accountList = $accountList->asArray()->all();
        
		return $accountList;
	}
}