<?php

namespace app\helpers\voucher;

use Yii;
use \app\models\GeneralVoucher;
use \app\models\voucherDetails;

class VoucherHelper 
{
    public static function saveVoucher($data){
        $voucher = new GeneralVoucher;
        $voucher->gv_num = $data['gv_num'];
        $voucher->name = $data['name'];
        $voucher->type = $data['type'];
        $voucher->date_transact = $data['date_transact'];
        $voucher->created_date = isset(\Yii::$app->user) && isset(\Yii::$app->user->identity) ? \Yii::$app->user->identity->DateTimeNow : $data['date_transact'];
        $voucher->created_by = isset(\Yii::$app->user) && isset(\Yii::$app->user->identity) ? \Yii::$app->user->identity->id : 18;

        if($voucher->save()){
            return $voucher;
        }
        /*else{
            var_dump($voucher->getErrors());
        }*/
        return null;
    }

	public static function insertEntries($list, $voucher_id){
        $success = true;
        $voucherModel = GeneralVoucher::findOne($voucher_id);
        foreach ($list as $key => $value) {
            $voucher = new VoucherDetails;
            $voucher->voucher_id = $voucher_id;
            $voucher->member_id = $value['member_id'];
            $voucher->particular_id = $value['particular_id'];
            $voucher->debit = $value['debit'];
            $voucher->credit = $value['credit'];
            $voucher->gv_num = isset($value['gv_num']) ? $value['gv_num'] :  $voucherModel ? $voucherModel->gv_num : null;
            if(!$voucher->save()){
                $success = false;
            }
        }
        return $success;
	}

    public static function saveJournalVoucherBase($voucher_id){

        $voucher = GeneralVoucher::findOne($voucher_id);
        if($voucher){
            $journalHeader = new \app\models\JournalHeader;
            $journalHeaderData = $journalHeader->getAttributes();
            $journalHeaderData['reference_no'] = $voucher->gv_num;
            $journalHeaderData['posting_date'] = $voucher->date_transact;
            $journalHeaderData['total_amount'] = 0;
            $journalHeaderData['trans_type'] = 'GeneralVoucher';
            $journalHeaderData['remarks'] = '';

            $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);

            if($saveJournal){
                $voucherList = VoucherDetails::find()->where(['voucher_id' => $voucher_id])->all();
                if($voucherList && count($voucherList) > 0){
                    //Entries

                    $journalList = new \app\models\JournalDetails;
                    $journalListAttr = $journalList->getAttributes();
                    $lists = array();
                    $totalAmount = 0;
                    $totalCredit = 0;
                    $totalDebit = 0;
                    foreach ($voucherList as $list) {
                        $$acct = (array)$list;
                        if($acct['debit'] && (float)$acct['debit'] > 0){
                            $arr = $journalListAttr;
                            $arr['amount'] = $acct['debit'];
                            $arr['particular_id'] = $acct['particular_id'];
                            $arr['entry_type'] = "DEBIT";
                            array_push($lists, $arr);

                            $totalAmount += $acct['debit'];
                        }

                        if($acct['credit'] && (float)$acct['credit'] > 0){
                            $arr = $journalListAttr;
                            $arr['amount'] = $acct['credit'];
                            $arr['particular_id'] = $acct['particular_id'];
                            $arr['entry_type'] = "CREDIT";
                            array_push($lists, $arr);
                        }
                        
                    }

                    $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->reference_no);
                    if($insertSuccess){
                        $saveJournal->total_amount = $totalAmount;
                        $saveJournal->save();
                        
                        $success = true;
                    }
                    else{
                        $success = false;
                    }

                }
                
            }
            else{
                $success = false;
            }
        }
        
        $success = false;
    }

    public static function getVoucherList($filter = null, $limit = null){

        $voucherList = \app\models\VoucherDetails::find()->joinWith(['particular']);

        if($filter &&is_array($filter)){
            $where = [];
            foreach ($filter as $key => $value) {
                $where[$key] = $value;
            }

            if(count($where) > 0){
                $voucherList = $voucherList->where($where);
            }
        }

        if($limit){
            $voucherList = $voucherList->limit(100);
        }

        $voucherList = $voucherList->orderBy('id DESC')->asArray()->all();

        return $voucherList;

    }

    public static function getVoucherByGvNum($gv_num){

        $voucher= GeneralVoucher::find()->where(['gv_num' => $gv_num])->one();
        if($voucher){
            return $voucher;
        }
        return null;
    }


}