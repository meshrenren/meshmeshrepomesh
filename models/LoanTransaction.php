<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loan_transaction".
 *
 * @property integer $id
 * @property string $loan_account
 * @property double $amount
 * @property string $transaction_type
 * @property integer $transacted_by
 * @property string $transaction_date
 * @property double $running_balance
 * @property string $remarks
 * @property double $prepaid_intpaid
 * @property double $interest_paid
 * @property string $OR_no
 * @property double $principal_paid
 * @property double $arrears_paid
 * @property string $date_posted
 *
 * @property Loanaccount $loanAccount
 */
class LoanTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loan_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount', 'running_balance', 'prepaid_intpaid', 'interest_paid', 'principal_paid', 'arrears_paid'], 'number'],
            [['transacted_by'], 'integer'],
            [['transaction_date', 'date_posted'], 'safe'],
            [['prepaid_intpaid', 'interest_paid', 'OR_no', 'principal_paid', 'arrears_paid', 'date_posted'], 'required'],
            [['loan_account', 'OR_no'], 'string', 'max' => 15],
            [['transaction_type'], 'string', 'max' => 30],
            [['remarks'], 'string', 'max' => 1000],
            [['loan_account'], 'exist', 'skipOnError' => true, 'targetClass' => Loanaccount::className(), 'targetAttribute' => ['loan_account' => 'account_no']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_account' => 'Loan Account',
            'amount' => 'Amount',
            'transaction_type' => 'Transaction Type',
            'transacted_by' => 'Transacted By',
            'transaction_date' => 'Transaction Date',
            'running_balance' => 'Running Balance',
            'remarks' => 'Remarks',
            'prepaid_intpaid' => 'Prepaid Intpaid',
            'interest_paid' => 'Interest Paid',
            'OR_no' => 'Or No',
            'principal_paid' => 'Principal Paid',
            'arrears_paid' => 'Arrears Paid',
            'date_posted' => 'Date Posted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanAccount()
    {
        return $this->hasOne(Loanaccount::className(), ['account_no' => 'loan_account']);
    }
}
