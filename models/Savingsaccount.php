<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savingsaccount".
 *
 * @property string $account_no
 * @property integer $saving_product_id
 * @property integer $member_id
 * @property double $balance
 * @property integer $is_active
 * @property string $date_created
 * @property integer $transacted_date
 * @property string $deleted_date
 */
class SavingsAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'savingsaccount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_no', 'saving_product_id', 'member_id', 'balance', 'is_active', 'date_created', 'transacted_date'], 'required'],
            [['saving_product_id', 'member_id', 'is_active'], 'integer'],
            [['balance'], 'number'],
            [['date_created', 'deleted_date', 'transacted_date'], 'safe'],
            [['account_no'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_no' => 'Account No',
            'saving_product_id' => 'Saving ID',
            'member_id' => 'Member ID',
            'balance' => 'Balance',
            'is_active' => 'Is Active',
            'date_created' => 'Date Created',
            'transacted_date' => 'Transacted Date',
            'deleted_date' => 'Deleted Date',
        ];
    }


    public function getProduct() {
        return $this->hasOne(SavingsProduct::className(), [ 'id' => 'saving_product_id' ] );
    }

    public function getMember() {
        return $this->hasOne(Member::className(), [ 'id' => 'member_id' ] )->select(["member.*", "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname"]);
    }

    public function getAccountList($name) {
        $retval = $this->find()->innerJoinWith(['product', 'member'])->where("CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) like '".$name."%'")->asArray()->all();
        
        return $retval;
    }

    public function getTransaction() {
        return $this->hasMany(SavingsTransaction::className(), [ 'fk_savings_id' => 'account_no' ] )->orderBy('id DESC');
    }

    public function getLastTransaction() {
        return $this->hasOne(SavingsTransaction::className(), [ 'fk_savings_id' => 'account_no' ] )->orderBy('id DESC');
    }
    
    
    public function calculateSavingsInterest()
    {
    	//get the necessary savings accounts;
    	
    	$qry = "SELECT * FROM savingsaccount sa inner join savingsproduct sp on sa.saving_product_id = sp.id where sa.balance >= sp.deposit_to_interest and sa.is_active=1";
    	
    	$branch = BranchParameters::findOne(['branch_code'=>'001']);
    	
    	
    	$connection = Yii::$app->getDb();
    	$command = $connection->createCommand($qry);
    	
    	$result = $command->queryAll();
    	
    	
    	
    	
    	foreach($result as $rows)
    	{
    		
    		$lastMonthEnd = date('Y-m-d', strtotime($branch->last_monthend));
    		$currentDate = date('Y-m-d', strtotime($branch->currentdate));
    		$monthEnd = date("Y-m-t", strtotime($branch->currentdate));
    		$interest = 0;
    		$pointerDate = date('Y-m-d', strtotime($lastMonthEnd. ' +1 day'));
    		$averageBalance = 0;
    		$totalBalance = 0;
    		
    		
    		while ($pointerDate<=$monthEnd) {
    			//get the average balance of member
    			$qry2 = "select ifnull((select ifnull(st.running_balance, 0) as running_balance from savings_transaction st where st.fk_savings_id='".$rows["account_no"]."' and st.transaction_date like '".$pointerDate."%' order by id desc limit 1),0)running_balance";
    			$command = $connection->createCommand($qry2);
    			
    			$result3 = $command->queryOne();
    			
    			//var_dump($result3);
    			
    			if($result3['running_balance']==0)
    				$averageBalance = $averageBalance + $averageBalance;
    			else
    				$averageBalance = $averageBalance + $result3['running_balance'];
    		
    		
    			
    			$pointerDate = date('Y-m-d', strtotime($pointerDate. ' +1 day')); 
    		}
    		
    		
    		if($averageBalance>0)
    		{
    			$interest = ($averageBalance / 30) * 0.05;
    			$totalBalance = $rows["balance"] + $interest;
    		}
    		
    		else if($averageBalance==0)
    		{
    			$interest = ($result3['balance'] / 30) * 0.05;
    			$totalBalance = $result3['balance'] + $interest;
    		}
    		
    		
    		
    		
    	if($interest>0)
    	{
    		$mdlAccount = SavingsAccount::findOne(['account_no'=>$rows["account_no"]]);
    		
    		$mdlAccount->balance = $totalBalance;
    		
    		$mdlAccount->update();
    		
    		
    		$mdlTrans = new SavingsTransaction();
    		
    		$mdlTrans->fk_savings_id = $rows["account_no"];
    		$mdlTrans->amount = $interest;
    		$mdlTrans->transaction_type = "INTEREST";
    		$mdlTrans->transacted_by = 1;
    		$mdlTrans->transaction_date = $currentDate;
    		$mdlTrans->running_balance = $totalBalance;
    		$mdlTrans->remarks = "IntPost.";
    		
    		
    		$mdlTrans->save();
    	}
    		
    		
    		
    		
    	
    		
    		
    		
    	}
    	
    	return true;
    	
    }
    
    
    
    
    
    

}
