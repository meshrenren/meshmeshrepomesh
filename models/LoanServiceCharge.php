<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loan_service_charge".
 *
 * @property integer $id
 * @property integer $loan_id
 * @property integer $month_term
 * @property string $percentage
 * @property double $min_amount
 * @property double $max_amount
 */
class LoanServiceCharge extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loan_service_charge';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_product_id', 'month_term', 'percentage', 'min_amount', 'max_amount'], 'required'],
            [['loan_product_id', 'month_term'], 'integer'],
            [['percentage', 'min_amount', 'max_amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_product_id' => 'Loan ID',
            'month_term' => 'Month Term',
            'percentage' => 'Percentage',
            'min_amount' => 'Min Amount',
            'max_amount' => 'Max Amount',
        ];
    }
}
