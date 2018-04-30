<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class SeedController extends Controller
{
	public function actionOldMember()
    {
    	$oldmembers = \app\models\Membersold::find()->orderBy('IDNum ASC')->select(['id', 'CONCAT(first_name, )'])->all();
    	foreach ($oldmembers as $oldmember) {
            $getMember = \app\models\Member::find()->where(['first_name' => $oldmember->FName, 'last_name' =>  $oldmember->SName, 'middle_name' =>  $oldmember->MName])->one();
            if($getMember == null){
            	$newMember = new \app\models\Member;
            	$getStation = \app\models\Station::find()->where(['name' => $oldmember->Station])->one();

            	$division = $oldmember->Division;
            	if($oldmember->Division == "ADMIN" || $oldmember->Division == " ADMIN")
            		$division = "ADMIN";
            	else if($oldmember->Division == "---" || $oldmember->Division == ".")
            		$division = "NONE";
            	else if($oldmember->Division == "ADMIN DIV" || $oldmember->Division == "ADMIN. DIV")
            		$division = "ADMIN DIV";

            	$getDivision = \app\models\Division::find()->where(['name' => $division])->one();

            	$newMember->member_type_id = 2;
            	if(strpos($oldmember->Station, 'ASSOCIATE')){
            		$newMember->member_type_id = 1;
            	}    

            	if($oldmember->DateMem != "---" && $oldmember->DateMem != "")   {
            		$date = explode("/", $oldmember->DateMem);
            		$memdate = date("Y-m-d", strtotime($date[2] . '-' . $date[1] . '-' . $date[0] )); 
            		$newMember->mem_date = $memdate;
            	} 
            	if($oldmember->BDay != "---" && $oldmember->BDay != "")   {
            		$bday = date("Y-m-d", strtotime($oldmember->BDay)); 
            		$newMember->birthday = $bday;
            	} 	

            	$newMember->first_name = $oldmember->FName;
            	$newMember->last_name = $oldmember->SName;
            	$newMember->middle_name = $oldmember->MName;
            	$newMember->image_path = $oldmember->MemPic;
            	$newMember->station_id = $getStation->id;
            	$newMember->division_id = $getDivision->id;
            	$newMember->employee_no = $oldmember->EmployeeNum;
            	$newMember->position = $oldmember->Position;
            	$newMember->gender = $oldmember->Sex;
            	$newMember->civil_status = $oldmember->CivilStatus;
            	$newMember->gsis_no = $oldmember->GSISNum;
            	$newMember->telephone = $oldmember->ContactNum;

            	if($newMember->save()){
            		$newUser = new \app\models\User;
            		$newUser->username = $newMember->last_name . "_" . $newMember->id;

            		$rand = substr(md5(microtime()), rand(0,26), 10);
            		$newUser->password = sha1($rand);
            		$newUser->email = $newMember->last_name . "@yahoo.com";
            		$newUser->level_id = 5;
            		$newUser->is_member = 1;
            		$newUser->password_text = $rand;
            		$newUser->save();

            		$newMember->user_id = $newUser->id;
            		$newMember->update();


            		if($oldmember->CityAddr != "---" && $oldmember->CityAddr != "" ){
	            		$newAddress = new \app\models\MemberAddress;
	            		$newAddress->member_id = $newMember->id;
	            		$newAddress->address = $oldmember->CityAddr;
	            		$newAddress->insert();
	            	}

            		if($oldmember->ProvAddr != "---" && $oldmember->ProvAddr != "" ){
	            		$newAddress = new \app\models\MemberAddress;
	            		$newAddress->member_id = $newMember->id;
	            		$newAddress->address = $oldmember->ProvAddr;
	            		$newAddress->insert();
	            	}

	            	$newFamily = new \app\models\MemberFamily;
	            	$newFamily->member_id = $newMember->id;
	            	$newFamily->name = $oldmember->EmContName;
	            	$newFamily->relation = $oldmember->EmContRel;
	            	$newFamily->address = $oldmember->EmContAddr;
	            	$newFamily->contact_no = $oldmember->EmContNum;
	            	$newFamily->insert();

	            	echo "Success: ID " . $oldmember->IDNum . " NAME: ". $oldmember->Name . "\n";

            	}
            	else{
	            	echo "Failed: ID " . $oldmember->IDNum . " NAME: ". $oldmember->Name . "\n";
            	}
            	
            }
            else{
	            echo "Not Save: ID " . $oldmember->IDNum . " NAME: ". $oldmember->Name . "\n";
            }
    	}

    }
    public function actionUpdateNullUser()
    {
    	$nullmembers = \app\models\Member::find()->where('user_id IS NULL')->all();
    	foreach ($nullmembers as $nullmember) {
    		$newUser = new \app\models\User;
            $newUser->username = $nullmember->last_name . "_" . $nullmember->id;

            $em = str_replace(" ", "", $nullmember->last_name);
            $em = str_replace("Ñ", "N", $em);

            $rand = substr(md5(microtime()), rand(0,26), 10);
            $newUser->password = sha1($rand);
            $newUser->email = $em . "@yahoo.com";
            $newUser->level_id = 5;
            $newUser->is_member = 1;
            $newUser->password_text = $rand;
            $newUser->save();

            $nullmember->user_id = $newUser->id;
            $nullmember->update();
    	}
    }
}


?>