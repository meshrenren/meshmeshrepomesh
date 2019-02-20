<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_payroll_list".
 *
 * @property integer $id
 * @property integer $payment_payroll_id
 * @property integer $description_id
 * @property double $amount
 */
class PaymentPayrollList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_payroll_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_payroll_id', 'description_id'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_payroll_id' => 'Payment Payroll ID',
            'description_id' => 'Description ID',
            'amount' => 'Amount',
        ];
    }
}
