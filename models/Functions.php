<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "functions".
 *
 * @property integer $id
 * @property string $function_key
 * @property string $description
 * @property integer $is_active
 */
class Functions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'functions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['function_key', 'description'], 'required'],
            [['description'], 'string'],
            [['is_active'], 'integer'],
            [['function_key'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'function_key' => 'Function Key',
            'description' => 'Description',
            'is_active' => 'Is Active',
        ];
    }
}
