<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savings_transaction".
 *
 * @property integer $id
 * @property integer $fk_savings_id
 * @property double $amount
 * @property string $transaction_type
 * @property integer $transacted_by
 * @property string $transaction_date
 * @property double $running_balance
 * @property string $remarks
 */
class SavingsTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'savings_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fk_savings_id', 'amount', 'transaction_type', 'transacted_by', 'transaction_date', 'running_balance'], 'required'],
            [['transacted_by'], 'integer'],
            [['amount', 'running_balance'], 'number'],
            [['transaction_date'], 'safe'],
            [['transaction_type', 'remarks'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_savings_id' => 'Fk Savings ID',
            'amount' => 'Amount',
            'transaction_type' => 'Transaction Type',
            'transacted_by' => 'Transacted By',
            'transaction_date' => 'Transaction Date',
            'running_balance' => 'Running Balance',
            'remarks' => 'Remarks',
        ];
    }
}
