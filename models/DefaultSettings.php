<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "default_settings".
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 */
class DefaultSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'default_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['name', 'value'], 'string', 'max' => 1000],
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
            'value' => 'Value',
        ];
    }

    public function getValue($name){
        $model = $this->find()->where(['name' => $name])->one();
        return $model->value;
    }
}
