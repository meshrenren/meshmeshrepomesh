<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payroll_particulars".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $is_loan
 */
class PayrollParticulars extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payroll_particulars';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['is_loan'], 'integer'],
            [['name'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'is_loan' => 'Is Loan',
        ];
    }
}
