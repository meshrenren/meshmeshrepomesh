<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savingsaccount".
 *
 * @property string $account_no
 * @property integer $saving_id
 * @property integer $member_id
 * @property double $balance
 * @property integer $is_active
 * @property string $date_created
 * @property integer $transacted_date
 * @property string $deleted_date
 */
class Savingsaccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'savingsaccount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_no', 'saving_id', 'member_id', 'balance', 'is_active', 'date_created', 'transacted_date'], 'required'],
            [['saving_id', 'member_id', 'is_active', 'transacted_date'], 'integer'],
            [['balance'], 'number'],
            [['date_created', 'deleted_date'], 'safe'],
            [['account_no'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_no' => 'Account No',
            'saving_id' => 'Saving ID',
            'member_id' => 'Member ID',
            'balance' => 'Balance',
            'is_active' => 'Is Active',
            'date_created' => 'Date Created',
            'transacted_date' => 'Transacted Date',
            'deleted_date' => 'Deleted Date',
        ];
    }
}
