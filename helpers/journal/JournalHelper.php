<?php

namespace app\helpers\journal;

use Yii;
use app\models\JournalHeader;
use app\models\JournalDetails;

class JournalHelper 
{
	public function saveJournalHeader(){
		$journal = new JournalHeader;
        $journal->reference_no = $data['reference_no'];
        $journal->posting_date = $data['posting_date'];
        $journal->posting_date = $data['total_amount'];
        $journal->posting_date = $data['trans_type'];
        $journal->remarks = $data['remarks'];
        $journal->transated_date = date('Y-m-d G:i:s');
        $journal->transated_by = \Yii::$app->user->identity->id;

        if($journal->save()){
            return $journal;
        }
        return null;
	}

    public static function insertJournal($list, $fk_reference_no){
    	$success = true;
        foreach ($list as $key => $value) {
            $journal = new PaymentRecordList;
            $journal->fk_reference_no = $fk_reference_no;
            $journal->amount = $value['amount'];
            $journal->entry_type = $value['entry_type'];
            $journal->particular_id = $value['particular_id'];

            if(!$journal->save()){
            	$success = false;
            }
        }

        return $success;
	}

}