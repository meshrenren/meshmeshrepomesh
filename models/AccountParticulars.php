<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "account_particulars".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property string $description
 * @property string $category
 * @property integer $product_id
 */
class AccountParticulars extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'account_particulars';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'category'], 'string'],
            [['product_id'], 'integer'],
            [['name', 'short_name'], 'string', 'max' => 1000],
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
            'short_name' => 'Short Name',
            'description' => 'Description',
            'category' => 'Category',
            'product_id' => 'Product ID',
        ];
    }
}
