<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sa_group_signatory".
 *
 * @property integer $id
 * @property string $savings_account
 * @property integer $member_id
 */
class SaGroupSignatory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sa_group_signatory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'integer'],
            [['savings_account'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'savings_account' => 'Savings Account',
            'member_id' => 'Member ID',
        ];
    }
}
