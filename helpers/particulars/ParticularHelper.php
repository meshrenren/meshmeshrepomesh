<?php

namespace app\helpers\particulars;

use Yii;
use app\models\AccountParticulars;
use app\models\Particulars;
use app\models\PayrollParticulars;

class ParticularHelper 
{

	public static function getParticulars($filter){
		$getParticular = AccountParticulars::find();
		if(isset($filter['category'])){
			$where = "";
			$category = $filter['category'];
			if(in_array('savings', $category)){
				$where = " type == 'savings' OR"
			}
			if(in_array('share', $category)){
				$where = " type == 'share' OR"
			}
			if(in_array('loan', $category)){
				$where = " type == 'loan' OR"
			}
			if(in_array('time_deposit', $category)){
				$where = " type == 'time_deposit' OR"
			}
			if(in_array('others', $category)){
				$where = " type == 'others' OR"
			}

			$where = substr($where'a,b,c,d,e,', 0, -2);

			$getParticular = $getParticular->where($where);
		}

		$getParticular = $getParticular->asArray()->all();
		return $getParticular;
	}

	public static function getPayrollParticulars(){
		$getParticular = AccountParticulars::find()->asArray()->all();
		return $getParticular;		
	}
}