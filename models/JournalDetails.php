<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "journal_details".
 *
 * @property integer $id
 * @property string $fk_reference_no
 * @property double $amount
 * @property string $entry_type
 * @property integer $particular_id
 *
 * @property JournalHeader $fkReferenceNo
 * @property AccountParticulars $particular
 */
class JournalDetails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'journal_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount'], 'number'],
            [['entry_type'], 'string'],
            [['particular_id'], 'integer'],
            [['fk_reference_no'], 'string', 'max' => 100],
            [['fk_reference_no'], 'exist', 'skipOnError' => true, 'targetClass' => JournalHeader::className(), 'targetAttribute' => ['fk_reference_no' => 'reference_no']],
            [['particular_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccountParticulars::className(), 'targetAttribute' => ['particular_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_reference_no' => 'Fk Reference No',
            'amount' => 'Amount',
            'entry_type' => 'Entry Type',
            'particular_id' => 'Particular ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkReferenceNo()
    {
        return $this->hasOne(JournalHeader::className(), ['reference_no' => 'fk_reference_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticular()
    {
        return $this->hasOne(AccountParticulars::className(), ['id' => 'particular_id']);
    }
}
