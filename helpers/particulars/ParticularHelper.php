<?php

namespace app\helpers\particulars;

use Yii;
use \app\models\AccountParticulars;
use \app\models\Particulars;
use \app\models\PayrollParticulars;
use \app\models\Calendar;
use \app\models\BranchParameters;
use phpDocumentor\Reflection\Types\Static_;
use \app\models\SavingAccounts;
use \app\models\SavingsTransaction;
use \app\models\TdTransaction;
use \app\models\TimeDepositAccount;
use app\models\SavingsAccount;
use app\models\LoanAccount;

use app\helpers\GlobalHelper;

class ParticularHelper 
{

	public static function getCurrentDay(){
		$getDate = Calendar::find()->where(['is_current' => 1])->one();
		return $getDate->date;
	}

	public static function getParticulars($filter = null, $orderBy = null){
		$getParticular = AccountParticulars::find();
		if(isset($filter['category'])){
			$where = "";
			$category = $filter['category'];
			if(in_array('SAVINGS', $category)){
				$where .= " category = 'SAVINGS' OR";
			}
			if(in_array('SHARE', $category)){
				$where .= " category = 'SHARE' OR";
			}
			if(in_array('LOAN', $category)){
				$where .= " category = 'LOAN' OR";
			}
			if(in_array('TIME_DEPOSIT', $category)){
				$where .= " category = 'TIME_DEPOSIT' OR";
			}
			if(in_array('OTHERS', $category)){
				$where .= " category = 'OTHERS' OR";
			}

			$where = substr($where, 0, -2);

			$getParticular = $getParticular->where($where);
		}
		if($orderBy != null){
			$getParticular = $getParticular->orderBy($orderBy);
		}

		$getParticular = $getParticular->asArray()->all();
		return $getParticular;
	}

	public static function getParticular($filter, $asArray = false){
		$getParticular = AccountParticulars::find()->where($filter);
		if($asArray){
			$getParticular = $getParticular->asArray;
		}
		$getParticular = $getParticular->one();

		return $getParticular;
	}

	public static function getPayrollParticulars(){
		$getParticular = AccountParticulars::find()->asArray()->all();
		return $getParticular;		
	}
	
	
	public static function processBeginning()
	{
		
		$connection = Yii::$app->getDb();
		$command = $connection->createCommand("
			select * from calendar where date_id>=(SELECT date_id FROM `calendar` where is_current =1 limit 1)
			order by date limit 2");
		
		$result = $command->queryAll();
		
		$x = 0;
		$currentDay = array();
		$nextDay = array();
		foreach ($result as $row)
		{
			if($x==0)
				$currentDay = $row;
			else $nextDay = $row;
			
			$x++;
		}
		
		
		//if monthend, process interests
		if($nextDay['is_month_end']==1)
		{
			static::calculateSavingsInterest($nextDay);
			
		}
		
		
		//calculate td maturity here
		static::calculateMaturity($nextDay);
		
		
		if(!static::performMakeLoanCurrent())
		{
			return false;
		}
		
		//move to next day
		$calendar = Calendar::findOne(['date_id'=>$currentDay['date_id'], 'date'=>$currentDay['date']]);
		$calendar->is_current = 0;
		
		$tomcalendar = Calendar::findOne(['date_id'=>$nextDay['date_id'], 'date'=>$nextDay['date']]);
		$tomcalendar->is_current = 1;
		
		if($calendar->save() && $tomcalendar->save())
		{
			echo "success sir <br/>";
			return true;
			
		}
		
		return false;
		
	}
	
	
	
	/*
	 * update loan status from Released to Current
	 * this will happen as a certification that the loan application is now current.
	 */
	
	public static function performMakeLoanCurrent()
	{
		$loanaccount = LoanAccount::find(['status'=>'Released']);
		$loanaccount->status="Current";
		if($loanaccount->save())
		{
			return true;
		}
		
		else return false;
		
		
	}
	
	
	public static function calculateMaturity($nextday)
	{
		
		$result = TimeDepositAccount::find()->where("maturity_date <='".$nextday['date']."' AND account_status='ACTIVE'")->all();
		$currentDate = date('Y-m-d', strtotime($nextday['date']));
		
		foreach($result as $rows)
		{
			$transaction = \Yii::$app->db->beginTransaction();
	        $success = true;
	        try {
				$tdaccount = TimeDepositAccount::findOne($rows['accountnumber']);
				$interest = $tdaccount->balance * ($tdaccount->interest_rate/100);
				
				
				/*
				 * Automatically add to savings the interest of TD if balance is equal to max amount (500000)
				*/

				$isSaveToSaving = false;
				if($tdaccount->balance >= 500000){
					$isSaveToSaving = true;
				}
				$tdBalance = $tdaccount->balance + $interest;
				$maturedAmount = $tdaccount->balance + $interest;
				$tdTransRemarks = "Interest Earned";

				if($isSaveToSaving){
					$savingsAccount = SavingAccounts::findOne(['member_id'=>$tdaccount->member_id, 'saving_product_id'=>1, 'is_active'=>1]);
					$savingsAccount->balance = round($savingsAccount->balance + $interest, 2);

					if(!$savingsAccount->save()){
						$success = false;
					}
					
					$savingsTransaction = new SavingsTransaction();
					
					$savingsTransaction->fk_savings_id = $savingsAccount->account_no;
					$savingsTransaction->amount = round($interest, 2);
					$savingsTransaction->transaction_type = "TDINTEREST";
					$savingsTransaction->transacted_by = \Yii::$app->user->identity->id;
					$savingsTransaction->transaction_date = $currentDate;
					$savingsTransaction->running_balance = $savingsAccount->balance;
					$savingsTransaction->remarks = "TD Auto Interest Posting.".date("mdY", strtotime($nextday['date']));
					$savingsTransaction->ref_no = "TDIntPost.".date("mdY", strtotime($nextday['date']));
					if(!$savingsTransaction->save()){
						$success = false;
					}

					$tdTransRemarks .= ": Posted as Savings Deposit";
					$tdBalance = $tdaccount->balance;
				}
				
				$tdtransaction = new TdTransaction();
				$tdtransaction->fk_account_number = $rows['accountnumber'];
				$tdtransaction->transaction_type='TDINTEREST';
				$tdtransaction->amount = $interest;
				$tdtransaction->balance = $tdBalance;
				$tdtransaction->remarks = $tdTransRemarks;
				$tdtransaction->transaction_date = $nextday['date'];
				$tdtransaction->transacted_by = \Yii::$app->user->identity->id;
				if(!$tdtransaction->save()){
					$success = false;
				}
				
				$tdaccount->balance = $tdBalance;
				$tdaccount->amount_mature = $maturedAmount;
				$tdaccount->account_status = 'MATURED';
				
				if($tdaccount->save())
				{
					//entry account is called in afterSave function
				}
				else{
					$success = false;
				}

				if($success){
                    $transaction->commit();
                }
                else{
                    $transaction->rollBack();
                }
			} catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
		}
	}
	
	
	public static function calculateSavingsInterest($nextday)
	{
		
		echo "i am hit savingsInterest <br/>";
		//get the necessary savings accounts;
		
		$qry = "SELECT * FROM savingsaccount sa inner join savingsproduct sp on sa.saving_product_id = sp.id where sa.balance >= sp.deposit_to_interest and sa.is_active=1";
		
	//	$branch = BranchParameters::findOne(['branch_code'=>'001']);
		
		
		$connection = Yii::$app->getDb();
		$command = $connection->createCommand($qry);
		$result = $command->queryAll();
		$currentDate = date('Y-m-d', strtotime($nextday['date']));
		
		
		
		foreach($result as $rows)
		{
			$personalBalance = 0;
			
			//GETTING THE AVERAGE DAILY BALANCE
			$dayfirst = "2019-".date("m", strtotime($nextday['date']))."-01";
			$daylast = "2019-".date("m", strtotime($nextday['date']))."-".date("t", strtotime($nextday['date']));
			$dayfirst = strtotime($dayfirst);
			$daylast = strtotime($daylast);
			$daycount = 1;
			while($dayfirst < $daylast)
			{
				$dateselect = "2019-".date("m", strtotime($nextday['date']))."-".str_pad($daycount, 2, "0", STR_PAD_LEFT);
				//echo "2019-".date("m", strtotime($nextDay['date']))."-".str_pad($daycount, 2, "0", STR_PAD_LEFT)." <br/>";
				
				$daycount++;
				$dayfirst = strtotime("+1 day", $dayfirst);
				
				
				
				$connection = Yii::$app->getDb();
				$command = $connection->createCommand("
						select (runbal/totcount) as totaltran from (SELECT sum(running_balance) as runbal, count(*) as totcount
						FROM `savings_transaction` where fk_savings_id='".$rows['account_no']."' and date(transaction_date)='".$dateselect."'
						group by fk_savings_id)sdf");
				$result = $command->queryOne();
				
				if($result)
					$personalBalance = $personalBalance + $result["totaltran"];
				else $personalBalance = $personalBalance + $rows["last_interest_balance"];
				
			}
			echo "personal balance of ".$rows['account_no'].": ".$personalBalance." | daytotal: ".$daycount." <br/>";
			
			$personalBalance = $personalBalance/$daycount;
			
			$int_rate = GlobalHelper::getSAInterest();
			$interest = 0;
			$interest = $personalBalance * $int_rate;
			$totalBalance = $rows["balance"] + round($interest, 2);
			
			if($interest>0)
			{
				$mdlAccount = SavingAccounts::findOne(['account_no'=>$rows["account_no"]]);
				
				$mdlAccount->balance = $totalBalance;
				$mdlAccount->last_interest_balance = $totalBalance;
				$mdlAccount->update();
				
				
				$mdlTrans = new SavingsTransaction();
				
				$mdlTrans->fk_savings_id = $rows["account_no"];
				$mdlTrans->amount = round($interest, 2);
				$mdlTrans->transaction_type = "INTEREST";
				$mdlTrans->transacted_by = \Yii::$app->user->identity->id;
				$mdlTrans->transaction_date = $currentDate;
				$mdlTrans->running_balance = $totalBalance;
				$mdlTrans->remarks = "IntPost.".date("mdY", strtotime($nextday['date']));
				$mdlTrans->ref_no = "IntPost.".date("mdY", strtotime($nextday['date']));
				
				
				$mdlTrans->save();
			}
			
			echo "   ".$interest."<br/>";
			
		}
		
		return true;
		
	}
	
	
}