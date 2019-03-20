<?php

namespace app\helpers\accounts;

use Yii;
use app\models\Shareaccount;
use app\models\ShareTransaction;

class ShareHelper 
{

	public static function getAccountShareInfo($member_id = null){
    
        $accountList = Shareaccount::find()->innerJoinWith(['product', 'member']);
        if($member_id != null){
        	$accountList = $accountList->where(['fk_memid' => $member_id]);
        }
        
        return $accountList->asArray()->all();
	}

    public static function getTransaction($fk_share_id = null, $filter = null){
        $model = ShareTransaction::find();
        if($fk_share_id != null){
            $model = $model->where(['fk_share_id' => $fk_share_id]);
        }
        
        return $model->asArray()->all();
    }


    public static function saveShareTransaction($data){
        $model = new ShareTransaction;
        $model->attributes = $data;
        $model->transaction_date = date('Y-m-d H:i:s');
        $model->transacted_by = \Yii::$app->user->identity->id;

        if($model->save()){
            return $model;
        }
        else{
        	var_dump($model->getErrors());
        }
        return null;
                
    }
}