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
            [['id', 'transacted_by'], 'integer'],
            [['amount', 'running_balance'], 'number'],
            [['transaction_date'], 'safe'],
            [['loan_account'], 'string', 'max' => 25],
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
