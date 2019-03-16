<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "journal_header".
 *
 * @property string $reference_no
 * @property string $posting_date
 * @property double $total_amount
 * @property string $trans_type
 * @property string $remarks
 * @property string $transacted_date
 * @property integer $transacted_by
 *
 * @property JournalDetails[] $journalDetails
 */
class JournalHeader extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'journal_header';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reference_no'], 'required'],
            [['posting_date', 'transacted_date'], 'safe'],
            [['total_amount'], 'number'],
            [['trans_type', 'remarks'], 'string'],
            [['transacted_by'], 'integer'],
            [['reference_no'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'reference_no' => 'Reference No',
            'posting_date' => 'Posting Date',
            'total_amount' => 'Total Amount',
            'trans_type' => 'Trans Type',
            'remarks' => 'Remarks',
            'transacted_date' => 'Transacted Date',
            'transacted_by' => 'Transacted By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJournalDetails()
    {
        return $this->hasMany(JournalDetails::className(), ['fk_reference_no' => 'reference_no']);
    }
}
