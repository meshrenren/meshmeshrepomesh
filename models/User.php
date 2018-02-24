<?php

namespace app\models;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
//    public $username;
    public $authKey;
    public $accessToken;

  //  public $first_name;
   // public $last_name;
   // public $fullName;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['is_active', 'default', 'value' => self::STATUS_ACTIVE],
            ['is_active', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['username','password'], 'string'],
            [['email'], 'email'],
            [['level_id', 'is_active'], 'integer']
        ];
    }


    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $model = static::findOne(['id' => $id, 'is_active' => self::STATUS_ACTIVE, 'deleted_date' => null]);
        if($model){
            $model->username = $model->username;
        }
        return $model;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $model = static::findOne(['username' => $username, 'is_active' => self::STATUS_ACTIVE, 'deleted_date' => null]);
        if($model)
        {
            $model->username = $model->username;
            return $model;
        }
        else
            return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        echo var_dump($this->password);

        return $this->password === $password;
    }
}
