<?php

namespace app\helpers\particulars;

use Yii;
use app\models\AccountParticulars;
use app\models\Particulars;
use app\models\PayrollParticulars;
use app\models\Calendar;
use app\models\BranchParameters;
use phpDocumentor\Reflection\Types\Static_;
use app\models\SavingsAccount;
use app\models\SavingsTransaction;
use app\models\TdTransaction;



class ParticularHelper 
{

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
		
		
		
		//move to next day
		$calendar = Calendar::findOne(['date_id'=>$currentDay['date_id'], 'date'=>$currentDay['date']]);
		$calendar->is_current = 0;
		
		$tomcalendar = Calendar::findOne(['date_id'=>$nextDay['date_id'], 'date'=>$nextDay['date']]);
		$tomcalendar->is_current = 1;
		
		if($calendar->save() && $tomcalendar->save())
		{
			echo "success sir <br/>";
			
		}
		
		else echo "bad sir <br/>";
		echo "end hit. <br/>";
		
	}
	
	
	public static function calculateMaturity($nextday)
	{
		
		$qry = "select * from td_account where maturity_date ='".$nextday['date']."' and account_status='ACTIVE'";
		$connection = Yii::$app->getDb();
		$command = $connection->createCommand($qry);
		$result = $command->queryAll();
		
		foreach($result as $rows)
		{
			$tdtransaction = new TdTransaction();
			
			
			
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
			
			
		
			
			
			
			$interest = 0;
			$interest = $personalBalance * 0.005;
			$totalBalance = $rows["last_interest_balance"] + round($interest, 2);
			
			
			
			if($interest>0)
			{
				$mdlAccount = SavingsAccount::findOne(['account_no'=>$rows["account_no"]]);
				
				$mdlAccount->balance = $totalBalance;
				$mdlAccount->last_interest_balance = $totalBalance;
				$mdlAccount->update();
				
				
				$mdlTrans = new SavingsTransaction();
				
				$mdlTrans->fk_savings_id = $rows["account_no"];
				$mdlTrans->amount = round($interest, 2);
				$mdlTrans->transaction_type = "INTEREST";
				$mdlTrans->transacted_by = 1;
				$mdlTrans->transaction_date = $currentDate;
				$mdlTrans->running_balance = $totalBalance;
				$mdlTrans->remarks = "IntPost.".date("mdY", strtotime($nextday['date']));
				
				
				$mdlTrans->save();
			}
			
			echo "   ".$interest."<br/>";
			
		}
		
		return true;
		
	}
	
	
}