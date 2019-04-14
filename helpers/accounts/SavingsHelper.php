<?php

namespace app\helpers\accounts;

use Yii;
use app\models\SavingsAccount;
use app\models\SavingsTransaction;

class SavingsHelper 
{

	public static function getAccountSavingsInfo($filter = [], $asArray = true){

        $accountList = SavingsAccount::find()->innerJoinWith(['product']);
        if(isset($filter['member_id'])){
            $accountList = $accountList->where(['member_id' => $filter['member_id']]);
        }
        else if(isset($filter['name'])){
            $accountList = $accountList->where(['account_name' => 'name', 'type' => 'Group']);
        }else{
            $accountList = $accountList->joinWith(['member']);
        }
        
        if($asArray){
            return $accountList->asArray()->all();
        }
        return $accountList->all();
	}

    public static function getTransaction($fk_savings_id = null, $filter = null){
        $model = SavingsTransaction::find();
        if($fk_savings_id != null){
            $model = $model->where(['fk_savings_id' => $fk_savings_id]);
        }
        
        return $model->asArray()->all();
    }

    public static function saveSavingsTransaction($data){
        $model = new SavingsTransaction;
        $model->attributes = $data;
        $model->transaction_date = date('Y-m-d H:i:s');
        $model->transacted_by = \Yii::$app->user->identity->id;
        $model->ref_no=$data['reference_number'];

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
                    <td style = "border: 1px solid #000;">'.$trans['ref_no'].'</td> 
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