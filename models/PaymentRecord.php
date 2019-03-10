<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_record".
 *
 * @property integer $id
 * @property string $date_transact
 * @property string $or_num
 * @property string $name
 * @property string $type
 * @property string $posting_code
 * @property string $check_number
 */
class PaymentRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_transact'], 'safe'],
            [['type'], 'string'],
            [['or_num', 'name', 'posting_code', 'check_number'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_transact' => 'Date Transact',
            'or_num' => 'Or Num',
            'name' => 'Name',
            'type' => 'Type',
            'posting_code' => 'Posting Code',
            'check_number' => 'Check Number',
        ];
    }
}
