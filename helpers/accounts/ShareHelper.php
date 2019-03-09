<?php

namespace app\helpers\accounts;

use Yii;
use app\models\Shareaccount;

class ShareHelper 
{

	public static function getAccountShareInfo($member_id){
    
        $accountList = Shareaccount::find()->innerJoinWith(['product', 'member'])->where(['fk_memid' => $member_id])->asArray()->all();
        
        return $accountList;
	}
}