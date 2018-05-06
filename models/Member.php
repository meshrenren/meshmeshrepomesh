<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $image_path
 * @property string $mem_date
 * @property string $birthday
 * @property integer $member_type_id
 * @property integer $station_id
 * @property integer $division_id
 * @property string $employee_no
 * @property string $position
 * @property string $gender
 * @property string $civil_status
 * @property double $salary
 * @property string $gsis_no
 * @property string $telephone
 * @property integer $is_active
 * @property string $deleted_date
 *
 * @property Loanaccount $loanaccount
 * @property MembershipType $memberType
 * @property MemberEmployment[] $memberEmployments
 * @property MemberFamily[] $memberFamilies
 */
class Member extends \yii\db\ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'member';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
				[ 
						[ 
								'first_name',
								'middle_name',
								'last_name',
								'mem_date',
								'member_type_id',
								'station_id',
								'division_id' 
						],
						'required' 
				],
				[ 
						[ 
								'mem_date',
								'birthday',
								'deleted_date' 
						],
						'safe' 
				],
				[ 
						[ 
								'member_type_id',
								'station_id',
								'division_id',
								'is_active' 
						],
						'integer' 
				],
				[ 
						[ 
								'salary' 
						],
						'number' 
				],
				[ 
						[ 
								'member_type_id' 
						],
						'exist',
						'skipOnError' => true,
						'targetClass' => MembershipType::className (),
						'targetAttribute' => [ 
								'member_type_id' => 'id' 
						] 
				] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
				'id' => 'ID',
				'first_name' => 'First Name',
				'middle_name' => 'Middle Name',
				'last_name' => 'Last Name',
				'image_path' => 'Image Path',
				'mem_date' => 'Member Date',
				'birthday' => 'Birthday',
				'member_type_id' => 'Member Type',
				'station_id' => 'Station',
				'division_id' => 'Division',
				'employee_no' => 'Employee No',
				'position' => 'Position',
				'gender' => 'Gender',
				'civil_status' => 'Civil Status',
				'salary' => 'Salary',
				'gsis_no' => 'GSIS No',
				'telephone' => 'Telephone',
				'is_active' => 'Is Active',
				'deleted_date' => 'Deleted Date' 
		];
	}
	public function getLoanaccount() {
		return $this->hasOne ( Loanaccount::className (), [ 
				'member_id' => 'id' 
		] );
	}
	public function getMemberType() {
		return $this->hasOne ( MembershipType::className (), [ 
				'id' => 'member_type_id' 
		] );
	}
	public function getStation() {
		return $this->hasOne ( Station::className (), [ 
				'id' => 'station_id' 
		] );
	}
	public function getDivision() {
		return $this->hasOne ( Division::className (), [ 
				'id' => 'division_id' 
		] );
	}
	public function getMemberFamilies() {
		return $this->hasMany ( MemberFamily::className (), [ 
				'member_id' => 'id' 
		] );
	}
	public function getMemberEmployments() {
		return $this->hasMany ( MemberAddress::className (), [ 
				'member_id' => 'id' 
		] );
	}
	public function getUser() {
		return $this->hasOne ( User::className (), [ 
				'id' => 'user_id' 
		] );
	}
	public function getMemberList($name) {
		$retval = $this->find()->select(["id", "CONCAT(last_name,', ',first_name,' ',middle_name)fullname"])->where("CONCAT(last_name,', ',first_name,' ',middle_name) like '".$name."%'")->asArray()->all();
		
		return $retval;
	}
	public function getMemberCount($start, $end, $state = NULL) {
		$count = 0;
		if ($state == "All") {
			$count = static::find ()->count ();
		} else {
			$count = static::find ()->where ( [ 
					'between',
					'mem_date',
					$start,
					$end 
			] )->count ();
		}
		return $count;
	}
}
