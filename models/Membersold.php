<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "membersold".
 *
 * @property integer $IDNum
 * @property string $Name
 * @property string $SName
 * @property string $FName
 * @property string $MName
 * @property string $Position
 * @property string $Station
 * @property string $Division
 * @property string $DateMem
 * @property string $EmployeeNum
 * @property string $GSISNum
 * @property string $BDay
 * @property string $Age
 * @property string $CivilStatus
 * @property string $Sex
 * @property string $CityAddr
 * @property string $ProvAddr
 * @property string $ContactNum
 * @property string $EmContName
 * @property string $EmContRel
 * @property string $EmContAddr
 * @property string $EmContNum
 * @property string $MemPic
 */
class Membersold extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'membersold';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['IDNum'], 'integer'],
            [['Name'], 'string', 'max' => 30],
            [['SName'], 'string', 'max' => 11],
            [['FName'], 'string', 'max' => 15],
            [['MName', 'Age'], 'string', 'max' => 2],
            [['Position'], 'string', 'max' => 19],
            [['Station'], 'string', 'max' => 17],
            [['Division'], 'string', 'max' => 20],
            [['DateMem', 'EmContNum'], 'string', 'max' => 18],
            [['EmployeeNum', 'CivilStatus'], 'string', 'max' => 7],
            [['GSISNum'], 'string', 'max' => 16],
            [['BDay', 'EmContRel'], 'string', 'max' => 10],
            [['Sex'], 'string', 'max' => 6],
            [['CityAddr'], 'string', 'max' => 1000],
            [['ProvAddr'], 'string', 'max' => 76],
            [['ContactNum'], 'string', 'max' => 14],
            [['EmContName'], 'string', 'max' => 28],
            [['EmContAddr'], 'string', 'max' => 88],
            [['MemPic'], 'string', 'max' => 107],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'IDNum' => 'Idnum',
            'Name' => 'Name',
            'SName' => 'Sname',
            'FName' => 'Fname',
            'MName' => 'Mname',
            'Position' => 'Position',
            'Station' => 'Station',
            'Division' => 'Division',
            'DateMem' => 'Date Mem',
            'EmployeeNum' => 'Employee Num',
            'GSISNum' => 'Gsisnum',
            'BDay' => 'Bday',
            'Age' => 'Age',
            'CivilStatus' => 'Civil Status',
            'Sex' => 'Sex',
            'CityAddr' => 'City Addr',
            'ProvAddr' => 'Prov Addr',
            'ContactNum' => 'Contact Num',
            'EmContName' => 'Em Cont Name',
            'EmContRel' => 'Em Cont Rel',
            'EmContAddr' => 'Em Cont Addr',
            'EmContNum' => 'Em Cont Num',
            'MemPic' => 'Mem Pic',
        ];
    }
}
