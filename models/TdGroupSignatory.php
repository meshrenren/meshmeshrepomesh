<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "td_group_signatory".
 *
 * @property integer $id
 * @property string $td_account
 * @property integer $member_id
 */
class TdGroupSignatory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'td_group_signatory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'integer'],
            [['td_account'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'td_account' => 'Td Account',
            'member_id' => 'Member ID',
        ];
    }
}
