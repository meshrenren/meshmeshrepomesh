<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "voucher_details".
 *
 * @property integer $id
 * @property integer $voucher_id
 * @property integer $member_id
 * @property double $amount
 * @property double $debit
 * @property double $credit
 * @property string $entry_type
 * @property integer $particular_id
 *
 * @property GeneralVoucher $voucher
 */
class VoucherDetails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'voucher_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['voucher_id', 'member_id', 'particular_id'], 'integer'],
            [['amount', 'debit', 'credit'], 'number'],
            [['gv_num'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'voucher_id' => 'Voucher ID',
            'member_id' => 'Member ID',
            'amount' => 'Amount',
            'debit' => 'Debit',
            'credit' => 'Credit',
            'entry_type' => 'Entry Type',
            'particular_id' => 'Particular ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoucher()
    {
        return $this->hasOne(GeneralVoucher::className(), ['id' => 'voucher_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticular()
    {
        return $this->hasOne(AccountParticulars::className(), ['id' => 'particular_id']);
    }
}
