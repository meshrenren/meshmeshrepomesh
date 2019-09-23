<?php

namespace app\helpers;

use Yii;

use \app\models\Savingsproduct;

/**
 * Organization class holds static functions
 * that'll provide reusable methods to retrieve
 * orgs utility data such as List of employees
 * or departments.
 */
class GlobalHelper
{
	/**
	 * Get days count.
	 * @param $dateFrom string
	 * @param $dateTo string
	 * @return $countDays int
	 */
	public static function getDiffDays($dateFrom, $dateTo) 
	{
		$from = new \DateTime($dateFrom);
		$to = new \DateTime($dateTo);

		$diff = $from->diff($to);
		$diffDays = $diff->days;
		if($from > $to){
			$diffDays = $diffDays * -1;
		}

	   	return $diffDays;
	}

	/**
	 * Get default interest for savings account.
	 * @return $interest int
	 */
	public static function getSAInterest() 
	{
		$getProduct = Savingsproduct::findOne(1); //default product for SA
		$int_rate = $getProduct->int_rate;

	   	return $int_rate;
	}

}


?>