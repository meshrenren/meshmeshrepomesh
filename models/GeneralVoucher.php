<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "general_voucher".
 *
 * @property integer $id
 * @property string $date_transact
 * @property string $gv_num
 * @property string $name
 * @property string $type
 * @property string $posting_code
 * @property string $created_date
 * @property integer $created_by
 */
class GeneralVoucher extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'general_voucher';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_transact', 'created_date'], 'safe'],
            [['gv_num'], 'required'],
            [['type'], 'string'],
            [['created_by'], 'integer'],
            [['gv_num'], 'string', 'max' => 100],
            [['name', 'posting_code'], 'string', 'max' => 1000],
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
            'gv_num' => 'Gv Num',
            'name' => 'Name',
            'type' => 'Type',
            'posting_code' => 'Posting Code',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
        ];
    }
}
