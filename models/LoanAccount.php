<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loanaccount".
 *
 * @property string $account_no
 * @property integer $loan_id
 * @property integer $member_id
 * @property double $principal
 * @property double $interest_balance
 * @property double $principal_balance
 * @property string $release_date
 * @property string $maturity_date
 * @property double $service_charge
 * @property double $prepaid_int
 * @property integer $is_active
 * @property string $deleted_date
 */
class LoanAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loanaccount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_no', 'loan_id', 'member_id', 'principal', 'interest_balance', 'principal_balance', 'release_date', 'maturity_date', 'service_charge', 'prepaid_int', 'is_active'], 'required'],
            [['loan_id', 'member_id', 'is_active'], 'integer'],
            [['principal', 'interest_balance', 'principal_balance', 'service_charge', 'prepaid_int'], 'number'],
            [['release_date', 'maturity_date', 'deleted_date'], 'safe'],
            [['account_no'], 'string', 'max' => 15],
            [['member_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_no' => 'Account No',
            'loan_id' => 'Loan ID',
            'member_id' => 'Member ID',
            'principal' => 'Principal',
            'interest_balance' => 'Interest Balance',
            'principal_balance' => 'Principal Balance',
            'release_date' => 'Release Date',
            'maturity_date' => 'Maturity Date',
            'service_charge' => 'Service Charge',
            'prepaid_int' => 'Prepaid Int',
            'is_active' => 'Is Active',
            'deleted_date' => 'Deleted Date',
        ];
    }

    public function getProduct() {
        return $this->hasOne(LoanProduct::className(), ['id' => 'loan_id'] );
    }

    public function getMember() {
        return $this->hasOne(Member::className(), [ 'id' => 'member_id' ] )->select(["member.*", "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname"]);
    }

    public function getAccountListByMemberID($member_id) {
        $retval = $this->find()->innerJoinWith(['product'])->where(['member_id' => $member_id])->asArray()->all();
        
        return $retval;
    }
}
