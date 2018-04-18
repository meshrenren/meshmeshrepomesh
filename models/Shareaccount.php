<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shareaccount".
 *
 * @property string $accountnumber
 * @property string $fk_memid
 * @property integer $NoOfShares
 * @property integer $totalSubscription
 * @property double $balance
 * @property integer $dateCreated
 * @property string $status
 *
 * @property Member $fkMem
 */
class Shareaccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shareaccount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountnumber', 'fk_memid', 'NoOfShares', 'totalSubscription', 'balance', 'dateCreated', 'status'], 'required'],
            [['fk_memid', 'NoOfShares', 'totalSubscription', 'dateCreated'], 'integer'],
            [['balance'], 'number'],
            [['accountnumber'], 'string', 'max' => 25],
            [['status'], 'string', 'max' => 10],
            [['fk_memid'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['fk_memid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'accountnumber' => 'Accountnumber',
            'fk_memid' => 'Fk Memid',
            'NoOfShares' => 'No Of Shares',
            'totalSubscription' => 'Total Subscription',
            'balance' => 'Balance',
            'dateCreated' => 'Date Created',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkMem()
    {
        return $this->hasOne(Member::className(), ['id' => 'fk_memid']);
    }
}
