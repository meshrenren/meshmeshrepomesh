<?php

namespace app\helpers\accounts;

use Yii;
use app\models\LoanAccount;
use app\models\LoanTransaction;

class LoanHelper 
{

	public static function getAccountLoanInfo($member_id){

		$query = new \yii\db\Query;
        $query->select('DISTINCT(loan_id) as loan_id')
            ->from('loanaccount la')
            ->where('member_id = '. $member_id);
        $loanAccounts = $query->all();
        $accountList = array();
        if(count($loanAccounts) >= 1){
            foreach ($loanAccounts as $loan) {
                $acc = \app\models\LoanAccount::find()
                    ->innerJoinWith(['product'])
                    ->where(['member_id' => $member_id, 'loan_id' =>  $loan['loan_id']])
                    ->orderBy('release_date DESC')
                    ->asArray()->one();
                array_push($accountList, $acc);
            }
        }
		return $accountList;
	}

    public static function getLoanTransaction($loan_account, $filter=null){
        $accountList = LoanTransaction::find();
        if($loan_account != null){
            $accountList = $accountList->where(['loan_account' => $loan_account]);
        }
        
        return $accountList->asArray()->all();
    }
}