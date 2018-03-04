<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "station".
 *
 * @property integer $id
 * @property integer $name
 * @property integer $address
 * @property string $deleted_date
 */
class Station extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'station';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address'], 'required'],
            [['name', 'address'], 'integer'],
            [['deleted_date'], 'safe'],
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
            'address' => 'Address',
            'deleted_date' => 'Deleted Date',
        ];
    }
}
