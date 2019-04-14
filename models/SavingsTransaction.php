<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savings_transaction".
 *
 * @property integer $id
 * @property string $fk_savings_id
 * @property double $amount
 * @property string $transaction_type
 * @property integer $transacted_by
 * @property string $transaction_date
 * @property double $running_balance
 * @property string $remarks
 * @property string $ref_no
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
            [['amount', 'running_balance'], 'number'],
            [['transacted_by'], 'integer'],
            [['transaction_date'], 'safe'],
            [['fk_savings_id'], 'string', 'max' => 25],
            [['transaction_type'], 'string', 'max' => 30],
            [['remarks'], 'string', 'max' => 120],
            [['ref_no'], 'string', 'max' => 50],
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
            'ref_no' => 'Ref No',
        ];
    }
}
