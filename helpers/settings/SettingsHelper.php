<?php

namespace app\helpers\settings;

use Yii;

class SettingsHelper
{
	public static function getStation($select = null, $joinWith = null) 
	{
		$list = \app\models\Station::find();
	    if ($select) {
	    	$list = $list->select($select);
	    }

	    if($joinWith){
	    	$list = $list->joinWith($joinWith);
	    }

	   	return $list->asArray()->all();
	}
}