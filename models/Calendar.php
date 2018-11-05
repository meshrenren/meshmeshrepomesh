<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "calendar".
 *
 * @property integer $date_id
 * @property string $date
 * @property integer $is_current
 * @property integer $is_holiday
 * @property integer $is_month_end
 */
class Calendar extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'calendar';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'is_current', 'is_holiday', 'is_month_end'], 'required'],
            [['date'], 'safe'],
            [['is_current', 'is_holiday', 'is_month_end'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date_id' => 'Date ID',
            'date' => 'Date',
            'is_current' => 'Is Current',
            'is_holiday' => 'Is Holiday',
            'is_month_end' => 'Is Month End',
        ];
    }
}
