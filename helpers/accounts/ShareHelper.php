<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\Shareaccount;
use \app\models\ShareProduct;
use \app\models\ShareTransaction;

class ShareHelper 
{

	public static function getAccountShareInfo($member_id = null, $asArray = true, $isAll = true){
    
        $accountList = Shareaccount::find()->innerJoinWith(['product', 'member']);
        if($member_id != null){
        	$accountList = $accountList->where(['fk_memid' => $member_id]);
        }

        if($asArray){
            $accountList = $accountList->asArray();
        }
        
        if(!$isAll){
            return $accountList->one();
        }

        return $accountList->all();
	}

    public static function getTransaction($fk_share_id = null, $filter = null){
        $model = ShareTransaction::find();
        if($fk_share_id != null){
            $model = $model->where(['fk_share_id' => $fk_share_id]);
        }
        
        return $model->orderBy('posted_date')->asArray()->all();
    }


    public static function saveShareTransaction($data){
        $model = new ShareTransaction;
        $model->attributes = $data;
        $model->transaction_date = isset($data['transaction_date']) ? $data['transaction_date'] : \Yii::$app->user->identity->DateNow;
        $model->transacted_by = \Yii::$app->user->identity->id;
        $model->posted_date = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateNow));

        if($model->save()){
            return $model;
        }
        /*else{
        	return $model->getErrors();
        }*/
        return null;
                
    }

    public static function getProduct($filter, $asArray = false){
        $getProduct= ShareProduct::find()->where($filter);
        if($asArray){
            $getProduct = $getProduct->asArray();
        }
        $getProduct = $getProduct->one();

        return $getProduct;
    }

    public static function printList($postData){
        $account_no = $postData['account_no'];
        $account_name = $postData['account_name'];
        $balance = $postData['balance'];
        $transaction = $postData['transaction'];
        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">Share Account Transaction</div></tr>
        </table>';

        $accountDetail = '<table>
            <tr>
                <td style = "font-weight: bold;">Account Name: </td> 
                <td><span>[account_name]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Account Number: </td> 
                <td>[account_no] </td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">Balance: </td> 
                <td>[balance]</td>
            </tr>
        </table>';
        $accountDetail = str_replace('[account_name]', $account_name, $accountDetail);
        $accountDetail = str_replace('[account_no]', $account_no, $accountDetail);
        $accountDetail = str_replace('[balance]', Yii::$app->view->formatNumber($balance), $accountDetail);

        $listTemplate .= $accountDetail;

        if(count($transaction) > 0){
            $transTable = '<table class = "table table-bordered mt-10" width = "100%">
                <tr>
                    <th>Date Transact</th> 
                    <th>In</th> 
                    <th>Out</th> 
                    <th>Transaction Type</th> 
                    <th>Reference No</th> 
                    <th>Running Balance</th> 
                    <th >Remarks</th> 
                </tr>';
            foreach ($transaction as $trans) {
                $transDate = date('Y-m-d', strtotime($trans['transaction_date']));
                $amount_in = $trans['amount_in'] && floatval($trans['amount_in']) > 0 ? Yii::$app->view->formatNumber($trans['amount_in']) : "";
                $amount_out = $trans['amount_out'] && floatval($trans['amount_out']) > 0 ? Yii::$app->view->formatNumber($trans['amount_out']) : "";
                $transTable .= '<tr>
                    <td>'.$transDate.'</td> 
                    <td>'.$amount_in.'</td> 
                    <td>'.$amount_out.'</td> 
                    <td>'.$trans['transaction_type'].'</td> 
                    <td>'.$trans['reference_number'].'</td> 
                    <td>'.Yii::$app->view->formatNumber($trans['running_balance']).'</td> 
                    <td>'.$trans['remarks'].'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        $listTemplate .= $accountDetail;

        return $listTemplate;
    }

    public static function transactionShare($shareDetails){
        $success = false;
        $error = null;

        $account_no = $shareDetails['account_no'];
        $remarks = $shareDetails['remarks'];
        $ref_num = $shareDetails['ref_num'];
        $amount = $shareDetails['amount'];
        $transaction_date = $shareDetails['transaction_date'];
        $transaction_type = $shareDetails['transaction_type'];
        $posted_date = \Yii::$app->user->identity->DateNow;

        $shareaccount = Shareaccount::findOne($shareDetails['account_no']);

        $running_balance = $shareaccount->balance;
        if($transaction_type == "CASHDEP" || $transaction_type == "CANCASHDEP"){
            $running_balance = $shareaccount->balance + $amount;
        }
        else if($transaction_type == "WITHDRWL" || $transaction_type == "CANWITHDRWL"){
            $running_balance = $shareaccount->balance - $amount;
        }
    

        $sharetransaction = new ShareTransaction();
        $sharetransaction->fk_share_id = $account_no;
        $sharetransaction->amount = $amount;
        $sharetransaction->transaction_type = $transaction_type;
        $sharetransaction->transacted_by = \Yii::$app->user->identity->id;
        $sharetransaction->transaction_date = date('Y-m-d H:i:s', strtotime($transaction_date));
        $sharetransaction->posted_date = date('Y-m-d', strtotime($posted_date));
        $sharetransaction->running_balance = $running_balance;
        $sharetransaction->remarks = $remarks;
        $sharetransaction->reference_number = $ref_num;
        
        $shareaccount->balance = $running_balance;
        
        if($shareaccount->save() && $sharetransaction->save())
        {
            $success = true;
            
        }
        else
        {
            $error = $sharetransaction->errors;
            $success = false;
        }

        return ['success' => $success, 'error' => $error];
    }

    public static function cancelReference($ref_no, $cancelled_date){
        $transactionList = ShareTransaction::find()->where(['reference_number' => $ref_no])->all();
        $success = true;

        foreach ($transactionList as $transaction) {
            $shareDetails['account_no'] = $transaction->fk_share_id;
            $shareDetails['remarks'] = "posted as cancel from ".$transaction->reference_number;
            $shareDetails['ref_num'] = 'CAN'.$transaction->reference_number;
            $shareDetails['amount'] = $transaction->amount * -1;
            $shareDetails['transaction_date'] = \Yii::$app->user->identity->DateTimeNow;
            $shareDetails['transaction_type'] = 'CAN'.$transaction->transaction_type;

            $sharetransaction = static::transactionShare($shareDetails);
            if($sharetransaction['success']){
                $transaction->is_cancelled = 1;
                $transaction->save();
            }  
            else{
                $success = false;
            }
        }
        return ['success' => $success];
    }
}