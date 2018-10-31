<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tdproduct".
 *
 * @property integer $id
 * @property string $description
 * @property double $min_deposit
 * @property double $max_deposit
 * @property integer $serial
 */
class TimeDepositProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tdproduct';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'min_deposit', 'max_deposit', 'trans_serial'], 'required'],
            [['min_deposit', 'max_deposit'], 'number'],
            [['trans_serial'], 'integer'],
            [['description'], 'string', 'max' => 35],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'min_deposit' => 'Min Deposit',
            'max_deposit' => 'Max Deposit',
            'trans_serial' => 'Serial',
        ];
    }

    public function getRatetable() {
        return $this->hasMany(TimeDepositRateTable::className(), [ 'fk_td_product' => 'id' ]);
    }
}
