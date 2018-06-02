<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savingsproduct".
 *
 * @property integer $id
 * @property string $description
 * @property double $int_rate
 * @property double $minimum_deposit
 * @property double $deposit_to_interest
 * @property integer $trans_serial
 * @property integer $is_withdrawable
 * @property double $holding_balance
 * @property string $date_created
 * @property integer $created_by
 * @property integer $is_active
 * @property string $deleted_date
 */
class Savingsproduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'savingsproduct';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'int_rate', 'minimum_deposit', 'deposit_to_interest', 'trans_serial', 'is_withdrawable', 'holding_balance', 'date_created', 'created_by', 'is_active'], 'required'],
            [['int_rate', 'minimum_deposit', 'deposit_to_interest', 'holding_balance'], 'number'],
            [['trans_serial', 'is_withdrawable', 'created_by', 'is_active'], 'integer'],
            [['date_created', 'deleted_date'], 'safe'],
            [['description'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'int_rate' => 'Int Rate',
            'minimum_deposit' => 'Minimum Deposit',
            'deposit_to_interest' => 'Deposit To Interest',
            'trans_serial' => 'Trans Serial',
            'is_withdrawable' => 'Is Withdrawable',
            'holding_balance' => 'Holding Balance',
            'date_created' => 'Date Created',
            'created_by' => 'Created By',
            'is_active' => 'Is Active',
            'deleted_date' => 'Deleted Date',
        ];
    }
}
