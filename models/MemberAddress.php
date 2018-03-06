<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member_address".
 *
 * @property integer $ID
 * @property integer $member_id
 * @property string $address
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
            [['address'], 'required'],
            [['member_id', 'is_mailing', 'is_permanent'], 'integer'],
            [['deleted_date'], 'safe'],
            [['address'], 'string', 'max' => 1000],
            [['city'], 'string', 'max' => 50],
            [['province'], 'string', 'max' => 100],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => MemberAddress::className(), 'targetAttribute' => ['member_id' => 'ID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'member_id' => 'Member ID',
            'address' => 'Address',
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
        return $this->hasOne(MemberAddress::className(), ['ID' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberAddresses()
    {
        return $this->hasMany(MemberAddress::className(), ['member_id' => 'ID']);
    }
}
