<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "branch".
 *
 * @property integer $id
 * @property string $branch_desc
 * @property string $address
 * @property string $deleted_date
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
            [['deleted_date'], 'safe'],
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
            'deleted_date' => 'Deleted Date',
        ];
    }
    
    
    public function beginningOfDay()
    {
    	
    }
    
    
    public function changeDate()
    {
    	$currentDate = Calendar::findOne(['is_current'=>1]);
    	
    	
    	
    	
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberEmployments()
    {
        return $this->hasMany(MemberEmployment::className(), ['branch_id' => 'id']);
    }
}
