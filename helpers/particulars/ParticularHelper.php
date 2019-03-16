<?php

namespace app\helpers\particulars;

use Yii;
use app\models\AccountParticulars;
use app\models\Particulars;
use app\models\PayrollParticulars;

class ParticularHelper 
{

	public static function getParticulars($filter = null, $orderBy = null){
		$getParticular = AccountParticulars::find();
		if(isset($filter['category'])){
			$where = "";
			$category = $filter['category'];
			if(in_array('SAVINGS', $category)){
				$where .= " category = 'SAVINGS' OR";
			}
			if(in_array('SHARE', $category)){
				$where .= " category = 'SHARE' OR";
			}
			if(in_array('LOAN', $category)){
				$where .= " category = 'LOAN' OR";
			}
			if(in_array('TIME_DEPOSIT', $category)){
				$where .= " category = 'TIME_DEPOSIT' OR";
			}
			if(in_array('OTHERS', $category)){
				$where .= " category = 'OTHERS' OR";
			}

			$where = substr($where, 0, -2);

			$getParticular = $getParticular->where($where);
		}
		if($orderBy != null){
			$getParticular = $getParticular->orderBy($orderBy);
		}

		$getParticular = $getParticular->asArray()->all();
		return $getParticular;
	}

	public static function getParticular($filter, $asArray = false){
		$getParticular = AccountParticulars::find()->where($filter);
		if($arArray){
			$getParticular = $getParticular->asArray;
		}
		$getParticular = $getParticular->one();

		return $getParticular;
	}

	public static function getPayrollParticulars(){
		$getParticular = AccountParticulars::find()->asArray()->all();
		return $getParticular;		
	}

}