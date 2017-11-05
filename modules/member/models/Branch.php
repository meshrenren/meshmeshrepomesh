<?php

namespace app\modules\member\models;

use Yii;

/**
 * This is the model class for table "branch".
 *
 * @property integer $id
 * @property string $branch_desc
 * @property string $address
 *
 * @property MemberEmployment[] $memberEmployments
 */
class Branch extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'branch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['branch_desc', 'address'], 'required'],
            [['branch_desc', 'address'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_desc' => 'Branch Desc',
            'address' => 'Address',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberEmployments()
    {
        return $this->hasMany(MemberEmployment::className(), ['branchID' => 'id']);
    }
}
