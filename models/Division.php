<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "division".
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 * @property string $deleted_date
 */
class Division extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'division';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address'], 'required'],
            [['deleted_date'], 'safe'],
            [['name', 'address'], 'string', 'max' => 1000],
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
