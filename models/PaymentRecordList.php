<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_record_list".
 *
 * @property integer $id
 * @property integer $payment_record_id
 * @property string $type
 * @property integer $particular_id
 * @property double $amount
 * @property integer $product_id
 * @property string $account_no
 * @property string $entry_type
 * @property integer $member_id
 *
 * @property PaymentRecord $paymentRecord
 */
class PaymentRecordList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_record_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_record_id', 'particular_id', 'product_id', 'member_id'], 'integer'],
            [['type', 'entry_type'], 'required'],
            [['type', 'entry_type'], 'string'],
            [['amount'], 'number'],
            [['account_no'], 'string', 'max' => 1000],
            [['payment_record_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentRecord::className(), 'targetAttribute' => ['payment_record_id' => 'id']],
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
            'type' => 'Type',
            'particular_id' => 'Particular ID',
            'amount' => 'Amount',
            'product_id' => 'Product ID',
            'account_no' => 'Account No',
            'entry_type' => 'Entry Type',
            'member_id' => 'Member ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentRecord()
    {
        return $this->hasOne(PaymentRecord::className(), ['id' => 'payment_record_id']);
    }
}
