<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_record".
 *
 * @property integer $id
 * @property string $date_transact
 * @property string $or_num
 * @property integer $particular_id
 * @property double $amount_paid
 * @property string $name
 * @property string $type
 * @property integer $type_id
 * @property string $posting_code
 * @property string $check_number
 */
class PaymentRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_transact'], 'safe'],
            [['particular_id', 'type_id'], 'integer'],
            [['amount_paid'], 'number'],
            [['type'], 'string'],
            [['or_num', 'name', 'posting_code', 'check_number'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_transact' => 'Date Transact',
            'or_num' => 'Or Num',
            'particular_id' => 'Particular ID',
            'amount_paid' => 'Amount Paid',
            'name' => 'Name',
            'type' => 'Type',
            'type_id' => 'Type ID',
            'posting_code' => 'Posting Code',
            'check_number' => 'Check Number',
        ];
    }


    /*
    * Particular relationship
    */
    public function getParticular(){
        return $this->hasOne(PayrollParticulars::className(), ['id' => 'particular_id']);
    }
}
