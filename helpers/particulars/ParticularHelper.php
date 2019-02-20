<?php

namespace app\helpers\particulars;

use Yii;
use app\models\Particulars;
use app\models\PayrollParticulars;

class ParticularHelper 
{

	public static function getParticulars(){
		$getParticular = Particulars::find()->asArray()->all();
		return $getParticular;
	}

	public static function getPayrollParticulars(){
		$getParticular = PayrollParticulars::find()->asArray()->all();
		return $getParticular;		
	}
}