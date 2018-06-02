<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "level_functions".
 *
 * @property integer $id
 * @property integer $levels_id
 * @property integer $functions_id
 * @property integer $_view
 * @property integer $_add
 * @property integer $_edit
 * @property integer $_delete
 * @property string $date_modified
 * @property integer $modiefied_by
 */
class LevelFunctions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'level_functions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['levels_id', 'functions_id'], 'required'],
            [['levels_id', 'functions_id', '_view', '_add', '_edit', '_delete', 'modiefied_by'], 'integer'],
            [['date_modified'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'levels_id' => 'Levels ID',
            'functions_id' => 'Functions ID',
            '_view' => 'View',
            '_add' => 'Add',
            '_edit' => 'Edit',
            '_delete' => 'Delete',
            'date_modified' => 'Date Modified',
            'modiefied_by' => 'Modiefied By',
        ];
    }

    public function getFunction(){
        return $this->hasOne(Functions::className(), ['id' => 'function_id']);
    }

    public function getLevel(){
        return $this->hasOne(Levels::className(), ['id' => 'level_id']);
    }
}
