<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member_family".
 *
 * @property integer $id
 * @property integer $member_id
 * @property string $name
 * @property string $relation
 * @property string $address
 * @property string $contact_no
 * @property integer $is_deceased
 * @property string $deleted_date
 */
class MemberFamily extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member_family';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['member_id', 'is_deceased'], 'integer'],
            [['address'], 'string'],
            [['deleted_date'], 'safe'],
            [['name'], 'string', 'max' => 1000],
            [['relation', 'contact_no'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'name' => 'Name',
            'relation' => 'Relation',
            'address' => 'Address',
            'contact_no' => 'Contact No',
            'is_deceased' => 'Is Deceased',
            'deleted_date' => 'Deleted Date',
        ];
    }
}
