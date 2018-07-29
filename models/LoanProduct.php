<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loanproduct".
 *
 * @property integer $id
 * @property string $product_name
 * @property double $int_rate
 * @property integer $max_amount
 * @property integer $min_amount
 * @property double $prepaid_interest
 * @property integer $interest_type_id
 *
 * @property LoanInterestType $interestType
 */
class LoanProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loanproduct';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_name', 'int_rate', 'max_amount', 'min_amount', 'prepaid_interest', 'interest_type_id'], 'required'],
            [['int_rate', 'prepaid_interest'], 'number'],
            [['max_amount', 'min_amount', 'interest_type_id'], 'integer'],
            [['product_name'], 'string', 'max' => 75],
            [['interest_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => LoanInterestType::className(), 'targetAttribute' => ['interest_type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_name' => 'Product Name',
            'int_rate' => 'Int Rate',
            'max_amount' => 'Max Amount',
            'min_amount' => 'Min Amount',
            'prepaid_interest' => 'Prepaid Interest',
            'interest_type_id' => 'Interest Type ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInterestType()
    {
        return $this->hasOne(LoanInterestType::className(), ['id' => 'interest_type_id']);
    }

    public function getServiceCharge()
    {
        return $this->hasMany(LoanServiceCharge::className(), ['loan_product_id' => 'id']);
    }
}
