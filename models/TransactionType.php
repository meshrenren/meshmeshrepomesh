<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction_type".
 *
 * @property string $transaction_code
 * @property string $description
 * @property double $debit_entry
 * @property double $credit_entry
 */
class TransactionType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_code', 'description', 'debit_entry', 'credit_entry'], 'required'],
            [['debit_entry', 'credit_entry'], 'number'],
            [['transaction_code'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'transaction_code' => 'Transaction Code',
            'description' => 'Description',
            'debit_entry' => 'Debit Entry',
            'credit_entry' => 'Credit Entry',
        ];
    }
}
