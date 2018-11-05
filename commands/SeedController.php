<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\db\Query;

class SeedController extends Controller
{
	public function actionOldMember()
    {
    	$oldmembers = \app\models\Membersold::find()->orderBy('IDNum ASC')->select(["*"])->all();
    	foreach ($oldmembers as $oldmember) {
            $getMember = \app\models\Member::find()->where(['first_name' => $oldmember->FName, 'last_name' =>  $oldmember->SName, 'middle_name' =>  $oldmember->MName])->one();
            //$getMember = \app\models\Member::find()->where(['old_db_idnum' => $oldmember->IDNum, ])->one();
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
                if($getDivision == null){
                    $getDivision = \app\models\Division::find()->where(['name' => "NONE"])->one();
                }

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
                $newMember->old_db_idnum = $oldmember->IDNum;

            	if($newMember->save()){
            		$newUser = new \app\models\User;
                    $lastname = str_replace(" ", "", $newMember->last_name);
                    $lastname = str_replace("Ñ", "N", $lastname);
                    $lastname = str_replace("ñ", "n", $lastname);
            		$newUser->username = $lastname. "_" . $newMember->id;

            		$rand = substr(md5(microtime()), rand(0,26), 10);
            		$newUser->password = sha1($rand);
            		$newUser->email = $lastname. "_" . $newMember->id . "@yahoo.com";
            		$newUser->level_id = 4;
            		$newUser->is_member = 1;
            		$newUser->password_text = $rand;
            		$newUser->save();

            		$newMember->user_id = $newUser->id;
            		$newMember->update();


            		if($oldmember->CityAddr != "---" && $oldmember->CityAddr != "" ){
	            		$newAddress = new \app\models\MemberAddress;
	            		$newAddress->member_id = $newMember->id;
	            		$newAddress->con_address = $oldmember->CityAddr;
	            		$newAddress->insert();
	            	}

            		if($oldmember->ProvAddr != "---" && $oldmember->ProvAddr != "" ){
	            		$newAddress = new \app\models\MemberAddress;
	            		$newAddress->member_id = $newMember->id;
	            		$newAddress->con_address = $oldmember->ProvAddr;
	            		$newAddress->insert();
	            	}

	            	$newFamily = new \app\models\MemberFamily;
	            	$newFamily->member_id = $newMember->id;
	            	$newFamily->name = $oldmember->EmContName;
	            	$newFamily->relation = $oldmember->EmContRel;
	            	$newFamily->fam_address = $oldmember->EmContAddr;
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
    public function actionUpdateIdnumLeadZero()
    {
        $getMembers = \app\models\Member::find()->where('old_db_idnum_zero IS NULL')->all();
        foreach ($getMembers as $member) {
            $num = $member->old_db_idnum;
            $str_length = 6;
            $str = substr("000000{$num}", - $str_length);
            $member->old_db_idnum_zero = $str;
            $member->update();
            echo "ID " . $member->old_db_idnum . " NAME: ". $member->last_name . " Number: " . $str . "\n";
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

    public function actionSeedShareAccount(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_sharecapitalledger scl');
        $capitalShare = $query->all();
        foreach ($capitalShare as $key => $cs) {
            $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $cs['IDNum'], 'last_name' => $cs['SName'], 'first_name' => $cs['FName']])->one();
            if($getMember){
                $getAccount = \app\models\Shareaccount::find()->where(['fk_memid' => $getMember->id])->one();
                if($getAccount == null){
                    $product = \app\models\ShareProduct::find()->where(['id' => 1])->one();
                    $trans_serial = $product->transaction_serial + 1;
                    $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

                    $account = new \app\models\Shareaccount;
                    $account->accountnumber = $product->id . "-" . $trans_serial_pad;
                    $account->fk_memid = $getMember->id;
                    $account->date_created = date('Y-m-d H:i:s');
                    $account->is_active = 1;
                    $account->no_of_shares = 1;
                    $account->totalSubscription = 1;
                    $account->balance = 0;
                    $account->status = "Active";
                    $account->fk_share_product = $product->id;

                    if($account->save()){
                        $product->transaction_serial = $trans_serial;
                        $product->save();

                        echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " Account created: \tAccountNumber->" . $account->accountnumber ."\tName: " . $getMember->last_name . " ".  $getMember->first_name . "\n";
                    }


                }
                
            }/*
            else{
                echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " No Member" . "\n";
            }*/
        }
    }

    public function actionSeedSavingsAccount(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_savingsledger scl');
        $capitalShare = $query->all();
        foreach ($capitalShare as $key => $cs) {
            $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $cs['IDNum'], 'last_name' => $cs['SName'], 'first_name' => $cs['FName']])->one();
            if($getMember){
                $getAccount = \app\models\SavingsAccount::find()->where(['member_id' => $getMember->id])->one();
                if($getAccount == null){
                    $product = \app\models\Savingsproduct::find()->where(['id' => 1])->one();
                    $trans_serial = $product->trans_serial + 1;
                    $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

                    $account = new \app\models\SavingsAccount;
                    $account->account_no = $product->id . "-" . $trans_serial_pad;
                    $account->saving_product_id = $product->id;
                    $account->member_id = $getMember->id;
                    $account->balance = 0;
                    $account->is_active = 1;
                    $account->date_created = date('Y-m-d H:i:s');
                    $account->transacted_date = date('Y-m-d H:i:s');

                    if($account->save()){
                        $product->trans_serial = $trans_serial;
                        $product->save();

                        echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " Account created: \tAccountNumber->" . $account->account_no ."\tName: " . $getMember->last_name . " ".  $getMember->first_name . "\n";
                    }
                    else{
                        echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " Account not created: \tAccountNumber->". "\n";
                    }


                }
                //echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " Has Member created: \tIDNum->" . $getMember->id ."\tName: " . $getMember->last_name . " ".  $getMember->first_name . "\n";
                
            }
            else{
                echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " No Member" . "\n";
            }
        }
    }


    public function actionSavingsAccountBalance(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_sdtransac sdt');
        $capitalShare = $query->all();
        foreach ($capitalShare as $key => $cs) {
            $balance = str_replace(",", "", $cs['Balance']);
            $balance = floatval($balance);
            $getAccount = \app\models\SavingsAccount::find()->joinWith(['member'])->where(['member.last_name' => $cs['SName'], 'member.first_name' => $cs['FName'] ])->orderBy('member.last_name ASC')->one();
            if($getAccount != null){
                $getAccount->balance = $balance;
                $getAccount->save(false);
                //echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " Has Account: \tAccountNumber->". $getAccount->account_no . "\n";
            }
            else{
                echo $cs['IDNum'] . "->" . $cs['Name'] . " ".  $cs['SName'] . " Has No Account \tBalance->". $balance. "\n";
            }
            //echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " \tBalance->". $balance. "\n";
        }
    }
}


?>