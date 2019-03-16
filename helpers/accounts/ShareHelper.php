<?php

namespace app\helpers\accounts;

use Yii;
use app\models\Shareaccount;

class ShareHelper 
{

	public static function getAccountShareInfo($member_id = null){
    
        $accountList = Shareaccount::find()->innerJoinWith(['product', 'member']);
        if($member_id != null){
        	$accountList = $accountList->where(['fk_memid' => $member_id]);
        }
        
        return $accountList->asArray()->all();
	}
}