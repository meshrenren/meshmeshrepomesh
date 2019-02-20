<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zold_payrollparticular".
 *
 * @property string $Particular
 */
class ZoldPayrollparticular extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zold_payrollparticular';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Particular'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Particular' => 'Particular',
        ];
    }
}
