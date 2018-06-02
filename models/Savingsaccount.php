<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savingsaccount".
 *
 * @property string $account_no
 * @property integer $saving_product_id
 * @property integer $member_id
 * @property double $balance
 * @property integer $is_active
 * @property string $date_created
 * @property integer $transacted_date
 * @property string $deleted_date
 */
class SavingsAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'savingsaccount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_no', 'saving_product_id', 'member_id', 'balance', 'is_active', 'date_created', 'transacted_date'], 'required'],
            [['saving_product_id', 'member_id', 'is_active'], 'integer'],
            [['balance'], 'number'],
            [['date_created', 'deleted_date', 'transacted_date'], 'safe'],
            [['account_no'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_no' => 'Account No',
            'saving_product_id' => 'Saving ID',
            'member_id' => 'Member ID',
            'balance' => 'Balance',
            'is_active' => 'Is Active',
            'date_created' => 'Date Created',
            'transacted_date' => 'Transacted Date',
            'deleted_date' => 'Deleted Date',
        ];
    }


    public function getProduct() {
        return $this->hasOne(SavingsProduct::className(), [ 'id' => 'saving_product_id' ] );
    }

    public function getMember() {
        return $this->hasOne(Member::className(), [ 'id' => 'member_id' ] )->select(["member.*", "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname"]);
    }


    public function getAccountList($name) {
        $retval = $this->find()->innerJoinWith(['product', 'member'])->where("CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) like '".$name."%'")->asArray()->all();
        
        return $retval;
    }

}
