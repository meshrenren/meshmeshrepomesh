<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member_address".
 *
 * @property integer $ID
 * @property integer $member_id
 * @property string $con_address
 * @property string $city
 * @property string $province
 * @property integer $is_mailing
 * @property integer $is_permanent
 * @property string $deleted_date
 *
 * @property MemberAddress $member
 * @property MemberAddress[] $memberAddresses
 */
class MemberAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['con_address'], 'required'],
            [['member_id', 'is_mailing', 'is_permanent'], 'integer'],
            [['deleted_date'], 'safe'],
            [['con_address'], 'string'],
            [['city'], 'string'],
            [['province'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'member_id' => 'Member ID',
            'con_address' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'is_mailing' => 'Is Mailing',
            'is_permanent' => 'Is Permanent',
            'deleted_date' => 'Deleted Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(MemberAddress::className(), ['id' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberAddresses()
    {
        return $this->hasMany(MemberAddress::className(), ['member_id' => 'id']);
    }
}
