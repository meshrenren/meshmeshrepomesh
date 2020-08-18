<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_record_history".
 *
 * @property int $id
 * @property int|null $payment_record_id
 * @property string|null $or_num
 * @property string|null $data
 * @property string|null $created_date
 */
class PaymentRecordHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_record_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_record_id'], 'integer'],
            [['data'], 'string'],
            [['created_date'], 'safe'],
            [['or_num'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_record_id' => 'Payment Record ID',
            'or_num' => 'Or Num',
            'data' => 'Data',
            'created_date' => 'Created Date',
        ];
    }
}
