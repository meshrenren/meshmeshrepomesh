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
 * @property integer $is_active
 *
 * @property Loanaccount $loanaccount
 * @property MembershipType $memType
 * @property MemberEmployment[] $memberEmployments
 * @property MemberFamily[] $memberFamilies
 */
class Member extends \yii\db\ActiveRecord
{
    public $image;
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
            [['image'], 'file'],
            [['firstname', 'middlename', 'lastname', 'mem_date', 'birthday', 'fk_branch', 'mem_type', 'is_active'], 'required'],
            [['mem_date', 'birthday'], 'safe'],
            [['salary'], 'double'],
            [['fk_branch', 'mem_type', 'is_active'], 'integer'],
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
            'memberID'      => 'Member ID',
            'firstname'     => 'First Name',
            'middlename'    => 'Middle Name',
            'lastname'      => 'Last Name',
            'mem_date'      => 'Memebership Date',
            'birthday'      => 'Birthdate',
            'fk_branch'     => 'Branch',
            'mem_type'      => 'Member Type',
            'employee_no'   => 'Employee Number',
            'gsis_no'       => 'GSIS Number',
            'is_active'     => 'Is Active',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'fk_branch']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division']);
    }
}
