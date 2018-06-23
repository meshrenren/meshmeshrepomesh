<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "branch_parameters".
 *
 * @property string $branch_code
 * @property string $branch_name
 * @property string $location
 * @property string $currentdate
 * @property string $last_monthend
 */
class BranchParameters extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'branch_parameters';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['branch_code', 'branch_name', 'location', 'currentdate', 'last_monthend'], 'required'],
            [['currentdate', 'last_monthend'], 'safe'],
            [['branch_code'], 'string', 'max' => 5],
            [['branch_name'], 'string', 'max' => 80],
            [['location'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'branch_code' => 'Branch Code',
            'branch_name' => 'Branch Name',
            'location' => 'Location',
            'currentdate' => 'Currentdate',
            'last_monthend' => 'Last Monthend',
        ];
    }
}
