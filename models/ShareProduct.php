<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shareproduct".
 *
 * @property integer $id
 * @property string $name
 * @property double $amount_per_share
 * @property integer $transaction_serial
 *
 * @property Shareaccount[] $shareaccounts
 */
class ShareProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shareproduct';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'amount_per_share', 'transaction_serial'], 'required'],
            [['amount_per_share'], 'number'],
            [['transaction_serial'], 'integer'],
            [['name'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'amount_per_share' => 'Amount Per Share',
            'transaction_serial' => 'Transaction Serial',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShareaccounts()
    {
        return $this->hasMany(Shareaccount::className(), ['fk_share_product' => 'id']);
    }
}
