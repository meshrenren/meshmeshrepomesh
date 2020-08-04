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
}