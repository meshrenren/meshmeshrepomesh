<?php

namespace app\helpers\member;

use Yii;
use app\models\Member;

class MemberHelper 
{
	public static function getMember($filter, $asArray = false){
        $getMember= Member::find()->where($filter);
        if($asArray){
            $getMember = $getMember->asArray();
        }
        $getMember = $getMember->one();

        return $getMember;
    }

    public static function getMemberList($filter = null, $asArray = false){
        $getMember= Member::find()->select(["member.*", "CONCAT(last_name,', ',first_name,' ',middle_name) fullname"]);
        if($filter){
        	$getMember = $getMember->where($filter);
        }

        if($asArray){
            $getMember = $getMember->asArray();
        }
        $getMember = $getMember->all();

        return $getMember;
    }
}