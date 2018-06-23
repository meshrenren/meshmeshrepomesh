<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "td_rate_table".
 *
 * @property integer $id
 * @property integer $fk_td_product
 * @property integer $day_from
 * @property integer $day_to
 * @property string $interest_rate
 */
class TimeDepositRateTable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'td_rate_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fk_td_product', 'day_from', 'day_to', 'interest_rate'], 'required'],
            [['fk_td_product', 'day_from', 'day_to'], 'integer'],
            [['interest_rate'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_td_product' => 'Fk Td Product',
            'day_from' => 'Day From',
            'day_to' => 'Day To',
            'interest_rate' => 'Interest Rate',
        ];
    }
}
