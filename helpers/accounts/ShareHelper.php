<?php

namespace app\helpers\accounts;

use Yii;
use app\models\Shareaccount;
use app\models\ShareTransaction;

class ShareHelper 
{

	public static function getAccountShareInfo($member_id = null){
    
        $accountList = Shareaccount::find()->innerJoinWith(['product', 'member']);
        if($member_id != null){
        	$accountList = $accountList->where(['fk_memid' => $member_id]);
        }
        
        return $accountList->asArray()->all();
	}

    public static function getTransaction($fk_share_id = null, $filter = null){
        $model = ShareTransaction::find();
        if($fk_share_id != null){
            $model = $model->where(['fk_share_id' => $fk_share_id]);
        }
        
        return $model->asArray()->all();
    }


    public static function saveShareTransaction($data){
        $model = new ShareTransaction;
        $model->attributes = $data;
        $model->transaction_date = date('Y-m-d H:i:s');
        $model->transacted_by = \Yii::$app->user->identity->id;

        if($model->save()){
            return $model;
        }
        else{
        	var_dump($model->getErrors());
        }
        return null;
                
    }

    public static function printList($postData){
        $account_no = $postData['account_no'];
        $account_name = $postData['account_name'];
        $balance = $postData['balance'];
        $transaction = $postData['transaction'];

        $listTemplate = '<table>
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
        $listTemplate = str_replace('[account_name]', $account_name, $listTemplate);
        $listTemplate = str_replace('[account_no]', $account_no, $listTemplate);
        $listTemplate = str_replace('[balance]', $balance, $listTemplate);

        if(count($transaction) > 0){
            $transTable = '<table style = "margin-top: 20px; border-collapse: collapse !important:">
                <tr>
                    <th style = "font-weight: bold; border: 1px solid #000;">Date Transact</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Amount</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Transaction Type</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Reference No</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Running Balance</th> 
                    <th style = "font-weight: bold; border: 1px solid #000;">Remarks</th> 
                </tr>';
            foreach ($transaction as $trans) {
                $transDate = date('Y-m-d', strtotime($trans['transaction_date']));
                $transTable .= '<tr>
                    <td style = "border: 1px solid #000;">'.$transDate.'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['amount'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['transaction_type'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['reference_num'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['running_balance'].'</td> 
                    <td style = "border: 1px solid #000;">'.$trans['remarks'].'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }
}