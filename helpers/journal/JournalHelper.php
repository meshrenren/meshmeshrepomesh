<?php

namespace app\helpers\journal;

use Yii;
use app\models\JournalHeader;
use app\models\JournalDetails;

class JournalHelper 
{
	public static function saveJournalHeader($data){
		$journal = new JournalHeader;
        $journal->reference_no = $data['reference_no'];
        $journal->posting_date = $data['posting_date'];
        $journal->total_amount = $data['total_amount'];
        $journal->trans_type = $data['trans_type'];
        $journal->remarks = $data['remarks'];
        $journal->transacted_date = date('Y-m-d H:i:s');
        $journal->transacted_by = \Yii::$app->user->identity->id;

        if($journal->save()){
            return $journal;
        }
        return null;
	}

    public static function insertJournal($list, $fk_reference_no){
    	$success = true;
    
    	if(count($list)<1)
    		return false;
    	
        foreach ($list as $key => $value) {
            $journal = new JournalDetails;
            $journal->fk_reference_no = $fk_reference_no;
            $journal->amount = $value['amount'];
            $journal->entry_type = $value['entry_type'];
            $journal->particular_id = $value['particular_id'];

            if(!$journal->save()){
            	echo var_dump($journal->errors);
            	return false;
            }
            
            	
        }

        return $success;
	}

}