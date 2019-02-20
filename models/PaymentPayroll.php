<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_payroll".
 *
 * @property integer $id
 * @property integer $payment_record_id
 * @property string $date_transact
 * @property string $or_num
 * @property integer $member_id
 */
class PaymentPayroll extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_payroll';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_record_id', 'member_id'], 'integer'],
            [['date_transact'], 'safe'],
            [['member_id'], 'required'],
            [['or_num'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_record_id' => 'Payment Record ID',
            'date_transact' => 'Date Transact',
            'or_num' => 'Or Num',
            'member_id' => 'Member ID',
        ];
    }
}
