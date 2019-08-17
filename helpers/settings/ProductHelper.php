<?php

namespace app\helpers\settings;

use Yii;

/**
 * Organization class holds static functions
 * that'll provide reusable methods to retrieve
 * orgs utility data such as List of employees
 * or departments.
 */
class ProductHelper
{
	/**
	 * Retrieve active loan product.
	 * Be default it'll only get id and name db fields
	 * overide this by passing the ``$select`` param.
	 * @param $select string|array
	 * @return $products array
	 */
	public static function getActiveLoanProducts($select = null) 
	{
		$products = \app\models\LoanProduct::find()->where(['is_active' => 1]);
	    if ($select) {
	    	$products->select($select);
	    }

	   	return $products->asArray()->all();
	}
}


?>