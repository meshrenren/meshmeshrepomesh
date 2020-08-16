<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loan_cutoff".
 *
 * @property int $id
 * @property int|null $loan_id
 * @property int|null $member_id
 * @property int|null $year
 * @property float|null $prepaid_interest
 * @property float|null $interest_earned
 * @property string|null $date_created
 */
class LoanCutoff extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loan_cutoff';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['loan_id', 'member_id', 'year', 'created_by'], 'integer'],
            [['prepaid_interest', 'interest_earned'], 'number'],
            [['date_created'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'member_id' => 'Member ID',
            'year' => 'Year',
            'prepaid_interest' => 'Prepaid Interest',
            'interest_earned' => 'Interest Earned',
            'date_created' => 'Date Created',
        ];
    }
}
