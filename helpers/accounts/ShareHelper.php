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
        
        return $model->orderBy('transaction_date')->asArray()->all();
    }


    public static function saveShareTransaction($data){
        $model = new ShareTransaction;
        $model->attributes = $data;
        $model->transaction_date = date('Y-m-d H:i:s');
        $model->transacted_by = \Yii::$app->user->identity->id;

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
}