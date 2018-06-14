<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "td_transaction".
 *
 * @property integer $id
 * @property string $fk_account_number
 * @property string $transaction_type
 * @property double $amount
 * @property double $balance
 * @property string $tran_date
 * @property integer $transacted_by
 * @property string $cancelled_date
 */
class TimeDepositTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'td_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fk_account_number', 'transaction_type', 'amount', 'balance', 'transaction_date', 'transacted_by'], 'required'],
            [['amount', 'balance'], 'number'],
            [['tran_date', 'cancelled_date'], 'safe'],
            [['transacted_by'], 'integer'],
            [['fk_account_number'], 'string', 'max' => 25],
            [['transaction_type'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_account_number' => 'Fk Account Number',
            'transaction_type' => 'Transaction Type',
            'amount' => 'Amount',
            'balance' => 'Balance',
            'tran_date' => 'Tran Date',
            'transacted_by' => 'Transacted By',
            'cancelled_date' => 'Cancelled Date',
        ];
    }
}
