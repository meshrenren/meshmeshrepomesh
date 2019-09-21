<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "td_account".
 *
 * @property string $accountnumber
 * @property integer $fk_mem_id
 * @property integer $fk_td_product
 * @property integer $term
 * @property double $amount
 * @property double $balance
 * @property string $maturity_date
 * @property integer $created_by
 * @property string $cancelled_date
 * @property string $date_created
 * @property string $interest_rate
 * @property string $account_status
 */
class TimeDepositAccount extends \yii\db\ActiveRecord
{
	

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'td_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountnumber', 'member_id', 'fk_td_product', 'term', 'amount', 'balance', 'maturity_date', 'date_created', 'interest_rate'], 'required'],
            [['member_id', 'fk_td_product', 'term', 'created_by'], 'integer'],
            [['amount', 'balance', 'interest_rate'], 'number'],
            [['maturity_date', 'cancelled_date', 'date_created'], 'safe'],
            [['accountnumber'], 'string', 'max' => 25],
            [['account_status'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'accountnumber' => 'Accountnumber',
            'member_id' => 'Member ID',
            'fk_td_product' => 'Fk Td Product',
            'term' => 'Term',
            'amount' => 'Amount',
            'balance' => 'Balance',
            'maturity_date' => 'Maturity Date',
            'created_by' => 'Created By',
            'cancelled_date' => 'Cancelled Date',
            'date_created' => 'Date Created',
            'interest_rate' => 'Interest Rate',
            'account_status' => 'Account Status'
        ];
    }

    public function getProduct() {
        return $this->hasOne(TimeDepositProduct::className(), [ 'id' => 'fk_td_product' ] );
    }

    public function getMember() {
        return $this->hasOne(Member::className(), [ 'id' => 'member_id' ] )->select(["member.*", "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname"]);
    }

    public function getAccountListByMemberID($member_id) {
        $retval = $this->find()->innerJoinWith(['product', 'member'])->where(['member_id' => $member_id])->asArray()->all();
        
        return $retval;
    }

    //Get the maturity amount of the account;
    public function getMatureDays($maturity_date, $amount, $interest_rate, $term){
        $curDate = date('Y-m-d');
        $getDays = strtotime($curDate) - strtotime($maturity_date);
        $getRate = $amount * ($interest_rate/100);
        $getInterestPerDay = $getRate/$term;
        $interestAmount = $getInterestPerDay * $get_days;

        return $interestAmount;

    }
    
    public function checkMaturity()
    {
    	$today = date("Y-m-d");
    	//get td to check maturity
    	$timeDeposits = TimeDepositAccount::find()
    	->where(["AND", "maturity_date<='".$today."'", "account_status='ACTIVE'"])
    	->all();
    	
    	
    	foreach ($timeDeposits as $tdrow)
    	{
   
    		try {
    			
    			//add to td transaction
    			$tdTransaction = new TimeDepositTransaction();
    			$tdTransaction->fk_account_number = $tdrow['accountnumber'];
    			$tdTransaction->transaction_type = "TDINTEREST";
    			$tdTransaction->amount = $tdrow['amount'] * ($tdrow['interest_rate']/100);
    			$tdTransaction->balance = $tdTransaction->amount + $tdrow['amount'];
    			$tdTransaction->transaction_date = date('Y-m-d H:i:s');
    			$tdTransaction->transacted_by = \Yii::$app->user->identity->id;
    			
    			if(!$tdTransaction->save())
    			{
    				echo $tdTransaction->errors;
    				break;
    			}
    			
    			//deduct to td transaction
    			$tdTransaction2 = new TimeDepositTransaction();
    			$tdTransaction2->fk_account_number = $tdrow['accountnumber'];
    			$tdTransaction2->transaction_type = "TDTRANSFER";
    			$tdTransaction2->amount = $tdTransaction->balance;
    			$tdTransaction2->balance = 0;
    			$tdTransaction2->transaction_date = date('Y-m-d H:i:s');
    			$tdTransaction2->transacted_by = \Yii::$app->user->identity->id;
    			
    			if(!$tdTransaction2->save())
    			{
    				echo $tdTransaction2->errors;
    				break;
    			}
    			
    			$theTDAccount = TimeDepositAccount::findOne($tdrow['accountnumber']);
    			$theTDAccount->balance = 0;
    			$theTDAccount->account_status='MATURED';
    			
    			if(!$theTDAccount->save())
    			{
    				echo $theTDAccount->errors;
    				break;
    			}
    			
    			
    			
    		} catch (\Exception $e) {
    			
    			echo $e;
    		}
    		
    		
    	}
    	
    	
    }
}
