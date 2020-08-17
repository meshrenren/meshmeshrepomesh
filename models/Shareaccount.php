<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shareaccount".
 *
 * @property string $accountnumber
 * @property integer $fk_memid
 * @property string $date_created
 * @property integer $is_active
 * @property integer $no_of_shares
 * @property double $totalSubscription
 * @property double $balance
 * @property string $status
 * @property integer $fk_share_product
 *
 * @property Shareproduct $fkShareProduct
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
            [['accountnumber', 'fk_memid', 'date_created', 'is_active', 'no_of_shares', 'totalSubscription', 'balance', 'status', 'fk_share_product'], 'required'],
            [['fk_memid', 'is_active', 'no_of_shares', 'fk_share_product'], 'integer'],
            [['date_created'], 'safe'],
            [['totalSubscription', 'balance'], 'number'],
            [['accountnumber'], 'string', 'max' => 25],
            [['status'], 'string', 'max' => 10],
            [['fk_share_product'], 'exist', 'skipOnError' => true, 'targetClass' => ShareProduct::className(), 'targetAttribute' => ['fk_share_product' => 'id']],
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
            'date_created' => 'Date Created',
            'is_active' => 'Is Active',
            'no_of_shares' => 'No Of Shares',
            'totalSubscription' => 'Total Subscription',
            'balance' => 'Balance',
            'status' => 'Status',
            'fk_share_product' => 'Fk Share Product',
        ];
    }
    
    
    public function getShareProducts()
    {
    	return ShareProduct::find()->asArray()->all();
    }

    public function getMember() {
        return $this->hasOne(Member::className(), [ 'id' => 'fk_memid' ] )->select(["member.*", "CONCAT(member.last_name,', ',member.first_name,' ',member.middle_name) fullname"]);
    }

    public function getProduct() {
        return $this->hasOne(ShareProduct::className(), [ 'id' => 'fk_share_product' ] );
    }

    public function getLastTransaction() {
        return $this->hasOne(ShareTransaction::className(), [ 'fk_share_id' => 'accountnumber' ] )->orderBy('id DESC');
    }

}
