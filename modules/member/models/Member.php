<?php

namespace app\modules\member\models;

use Yii;

/**
 * This is the model class for table "member".
 *
 * @property integer $memberID
 * @property string $firstname
 * @property string $middlename
 * @property string $lastname
 * @property string $mem_date
 * @property string $birthday
 * @property integer $fk_branch
 * @property integer $mem_type
 * @property integer $isActive
 *
 * @property Loanaccount $loanaccount
 * @property MembershipType $memType
 * @property MemberEmployment[] $memberEmployments
 * @property MemberFamily[] $memberFamilies
 */
class Member extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstname', 'middlename', 'lastname', 'mem_date', 'birthday', 'fk_branch', 'mem_type', 'isActive'], 'required'],
            [['mem_date', 'birthday'], 'safe'],
            [['fk_branch', 'mem_type', 'isActive'], 'integer'],
            [['firstname', 'middlename', 'lastname'], 'string', 'max' => 25],
            [['mem_type'], 'exist', 'skipOnError' => true, 'targetClass' => MembershipType::className(), 'targetAttribute' => ['mem_type' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'memberID' => 'Member ID',
            'firstname' => 'Firstname',
            'middlename' => 'Middlename',
            'lastname' => 'Lastname',
            'mem_date' => 'Mem Date',
            'birthday' => 'Birthday',
            'fk_branch' => 'Fk Branch',
            'mem_type' => 'Mem Type',
            'isActive' => 'Is Active',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanaccount()
    {
        return $this->hasOne(Loanaccount::className(), ['fk_memid' => 'memberID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemType()
    {
        return $this->hasOne(MembershipType::className(), ['id' => 'mem_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberEmployments()
    {
        return $this->hasMany(MemberEmployment::className(), ['fk_memid' => 'memberID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberFamilies()
    {
        return $this->hasMany(MemberFamily::className(), ['fk_memid' => 'memberID']);
    }
}
