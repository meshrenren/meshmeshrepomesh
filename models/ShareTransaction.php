<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "share_transaction".
 *
 * @property integer $id
 * @property string $fk_share_id
 * @property string $reference_number
 * @property double $amount
 * @property string $transaction_type
 * @property integer $transacted_by
 * @property string $transaction_date
 * @property double $running_balance
 * @property string $remarks
 */
class ShareTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'share_transaction';
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
            [['fk_share_id', 'reference_number'], 'string', 'max' => 25],
            [['transaction_type'], 'string', 'max' => 30],
            [['remarks'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_share_id' => 'Fk Share ID',
            'reference_number' => 'Reference Number',
            'amount' => 'Amount',
            'transaction_type' => 'Transaction Type',
            'transacted_by' => 'Transacted By',
            'transaction_date' => 'Transaction Date',
            'running_balance' => 'Running Balance',
            'remarks' => 'Remarks',
        ];
    }
}
