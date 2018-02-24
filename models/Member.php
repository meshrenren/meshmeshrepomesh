<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $image_path
 * @property string $mem_date
 * @property string $birthday
 * @property integer $branch_id
 * @property integer $member_type_id
 * @property integer $station_id
 * @property integer $division_id
 * @property string $employee_no
 * @property string $position
 * @property string $gender
 * @property string $civil_status
 * @property double $salary
 * @property string $gsis_no
 * @property string $telephone
 * @property integer $is_active
 * @property string $deleted_date
 *
 * @property Loanaccount $loanaccount
 * @property MembershipType $memberType
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
            [['first_name', 'middle_name', 'last_name', 'image_path', 'mem_date', 'birthday', 'branch_id', 'member_type_id', 'station_id', 'division_id', 'employee_no', 'position', 'gender', 'civil_status', 'salary', 'gsis_no', 'telephone', 'is_active'], 'required'],
            [['mem_date', 'birthday', 'deleted_date'], 'safe'],
            [['branch_id', 'member_type_id', 'station_id', 'division_id', 'is_active'], 'integer'],
            [['salary'], 'number'],
            [['first_name', 'middle_name', 'last_name'], 'string', 'max' => 25],
            [['image_path', 'employee_no', 'position', 'gender', 'civil_status', 'gsis_no', 'telephone'], 'string', 'max' => 1000],
            [['member_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => MembershipType::className(), 'targetAttribute' => ['member_type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'image_path' => 'Image Path',
            'mem_date' => 'Mem Date',
            'birthday' => 'Birthday',
            'branch_id' => 'Branch ID',
            'member_type_id' => 'Member Type ID',
            'station_id' => 'Station ID',
            'division_id' => 'Division ID',
            'employee_no' => 'Employee No',
            'position' => 'Position',
            'gender' => 'Gender',
            'civil_status' => 'Civil Status',
            'salary' => 'Salary',
            'gsis_no' => 'Gsis No',
            'telephone' => 'Telephone',
            'is_active' => 'Is Active',
            'deleted_date' => 'Deleted Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanaccount()
    {
        return $this->hasOne(Loanaccount::className(), ['member_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberType()
    {
        return $this->hasOne(MembershipType::className(), ['id' => 'member_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberEmployments()
    {
        return $this->hasMany(MemberEmployment::className(), ['member_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberFamilies()
    {
        return $this->hasMany(MemberFamily::className(), ['member_id' => 'id']);
    }
}
