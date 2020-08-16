<?php

namespace app\helpers\accounts;

use Yii;
use \app\models\SavingAccounts;
use \app\models\Savingsproduct;
use \app\models\SavingsTransaction;

class SavingsHelper 
{
    public static function getProduct($filter, $asArray = false){
        $getProduct= Savingsproduct::find()->where($filter);
        if($asArray){
            $getProduct = $getProduct->asArray();
        }
        $getProduct = $getProduct->one();

        return $getProduct;
    }

    public static function getMemberSavings($member_id, $asArray = true){
        $account = SavingAccounts::find()->innerJoinWith(['product', 'member'])
            ->where(['member_id' => $member_id]);

        if($asArray){
            return $account->asArray()->one();
        }
        return $account->one();
    }

	public static function getAccountSavingsInfo($filter = [], $asArray = true){

        $accountList = SavingAccounts::find()->innerJoinWith(['product']);
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
        
        return $model->orderBy('transaction_date DESC')->asArray()->all();
    }

    public static function saveSavingsTransaction($data){
        $model = new SavingsTransaction;
        $model->attributes = $data;
        $model->transaction_date = date('Y-m-d H:i:s');
        $model->transacted_by = \Yii::$app->user->identity->id;
        if(isset($data['reference_number'])){
            $model->ref_no=$data['reference_number'];
        }
        else if(isset($data['ref_no'])){
            $model->ref_no=$data['ref_no'];
        }

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
        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">Savings Account Transaction</div></tr>
        </table>';

        $listTemplate .= '<table>
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
        $listTemplate = str_replace('[balance]', Yii::$app->view->formatNumber($balance), $listTemplate);

        if(count($transaction) > 0){
            $transTable = '<table class = "table table-bordered mt-10" width = "100%">
                <tr>
                    <th>Date Transact</th> 
                    <th>In</th> 
                    <th>Out</th> 
                    <th>Transaction Type</th> 
                    <th>Reference No</th> 
                    <th>Running Balance</th> 
                    <th>Remarks</th> 
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
                    <td>'.$trans['ref_no'].'</td> 
                    <td>'.Yii::$app->view->formatNumber($trans['running_balance']).'</td> 
                    <td>'.$trans['remarks'].'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }
}