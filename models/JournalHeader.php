<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "journal_header".
 *
 * @property string $reference_no
 * @property string $posting_date
 * @property string $created_date
 * @property integer $transated_by
 * @property string $remarks
 * @property double $total_amount
 * @property string $trans_type
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
            [['posting_date', 'created_date'], 'safe'],
            [['transated_by'], 'integer'],
            [['remarks', 'trans_type'], 'string'],
            [['total_amount'], 'number'],
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
            'created_date' => 'Created Date',
            'transated_by' => 'Transated By',
            'remarks' => 'Remarks',
            'total_amount' => 'Total Amount',
            'trans_type' => 'Trans Type',
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
