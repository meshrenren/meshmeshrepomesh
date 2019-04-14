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
            [['type'], 'required'],
            [['type'], 'string'],
            [['amount'], 'number'],
            [['account_no'], 'string', 'max' => 1000],
            //[['payment_record_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentRecord::className(), 'targetAttribute' => ['payment_record_id' => 'id']],
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

    public function getMember() {
        return $this->hasOne(Member::className(), [ 'id' => 'member_id' ] )->select(["member.id", "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname"]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentRecord()
    {
        return $this->hasOne(PaymentRecord::className(), ['id' => 'payment_record_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticular()
    {
        return $this->hasOne(AccountParticulars::className(), ['id' => 'particular_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSavingsProduct()
    {
        return $this->hasOne(Savingsproduct::className(), ['id' => 'product_id'])
            ->onCondition('payment_record_list.type = "SAVINGS"');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShareProduct()
    {
        return $this->hasOne(ShareProduct::className(), ['id' => 'product_id'])
            ->andOnCondition('payment_record_list.type = "SHARE"');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanProduct()
    {
        return $this->hasOne(LoanProduct::className(), ['id' => 'product_id'])
            ->andOnCondition('payment_record_list.type = "LOAN"');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTdProduct()
    {
        return $this->hasOne(TimeDepositProduct::className(), ['id' => 'product_id'])
            ->andOnCondition('payment_record_list.type = "TIME_DEPOSIT"');
    }
}
