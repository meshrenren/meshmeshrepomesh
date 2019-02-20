<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "general_voucher".
 *
 * @property integer $id
 * @property string $date_transact
 * @property string $gv_num
 * @property string $description
 * @property double $debit
 * @property double $credit
 * @property string $name
 * @property string $type
 * @property integer $type_id
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
            [['description', 'type'], 'string'],
            [['debit', 'credit'], 'number'],
            [['type_id', 'created_by', 'description_id'], 'integer'],
            [['description', 'name'], 'required'],
            [['gv_num', 'name', 'posting_code'], 'string', 'max' => 1000],
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
            'description' => 'Description',
            'debit' => 'Debit',
            'credit' => 'Credit',
            'name' => 'Name',
            'type' => 'Type',
            'type_id' => 'Type ID',
            'posting_code' => 'Posting Code',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
        ];
    }


    /*
    * get particular relationship
    */
    public function getParticular(){
        return $this->hasOne(Particular::className(), ['id' => 'description_id']);
    }
}
