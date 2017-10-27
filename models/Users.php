<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property integer $level_type
 * @property integer $is_active
 * @property string $email
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'first_name', 'last_name', 'level_type', 'is_active', 'email'], 'required'],
            [['level_type', 'is_active'], 'integer'],
            [['username', 'password', 'first_name', 'last_name', 'email'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'level_type' => 'Level Type',
            'is_active' => 'Is Active',
            'email' => 'Email',
        ];
    }
}
