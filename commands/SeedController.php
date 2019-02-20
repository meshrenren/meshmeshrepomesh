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

    /*public function actionSeedLoanLedgerMember(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_loanledgermember llm');
        $loanMember = $query->all();
        foreach ($loanMember as $key => $llm) {
            $getMember = \app\models\Member::find()->where(['old_db_idnum' => $llm['IDNum']])->one();
            if($getMember){

                $query2 = new \yii\db\Query;
                $query2->select(['*']);
                $query2->from('zold_loanledger ll')->where(['IDNum' => $llm['IDNum']])->andWhere("PrincipalLoan != ''");
                $query2->groupBy(['LoanType']);
                $loanLedgerMember = $query2->all();
                $loans = "";
                foreach ($loanLedgerMember as $key => $loan) {
                    $loanType = strtoupper($loan['LoanType']);
                    $loan_id = 0;
                    if(strpos($loanType, "APPLIANCE")){
                        $loan_id = 1;
                    }
                    else if(strpos( $loanType, "REGULAR")){
                        $loan_id = 2;
                    }
                    else if(strpos($loanType, "EDUCATIONAL")){
                        $loan_id = 3;
                    }
                    else if(strpos($loanType, "EMERGENCY")){
                        $loan_id = 4;
                    }
                    else if(strpos($loanType, "HOSPITALIZATION")){
                        $loan_id = 5;
                    }
                    else if(strpos($loanType, "HOUSE")){
                        $loan_id = 6;
                    }
                    else if(strpos($loanType, "MEDICAL")){
                        $loan_id = 7;
                    }
                    else if(strpos($loanType, "BUSINESS")){
                        $loan_id = 8;
                    }
                    else if(strpos($loanType, "CELLPHONE")){
                        $loan_id = 10;
                    }
                    else if(strpos($loanType, "CELLCARD")){
                        $loan_id = 11;
                    }
                    else if(strpos($loanType, "BUY-OUT")){
                        $loan_id = 12;
                    }
                    else if(strpos($loanType, "MEMORIAL")){
                        $loan_id = 13;
                    }
                    else if(strpos($loanType, "GADGET")){
                        $loan_id = 14;
                    }
                    else if(strpos($loanType, "DOMESTIC")){
                        $loan_id = 15;
                    }
                    else if(strpos($loanType, "RADIATION")){
                        $loan_id = 16;
                    }
                    else if(strpos($loanType, "CASSEROLE") || $loanType == "CASSEROLE"){
                        $loan_id = 17;
                    }
                    else if(strpos($loanType, "KEYBOARD") || $loanType == "KEYBOARD"){
                        $loan_id = 18;
                    }
                    if($loan_id == 0){
                       $loans .= "No type: " . $loan['LoanType'] . ", "; 
                    }
                    else{
                        //$loans .= "Loan ID: " . $loan_id . ", ";
                        $product = \app\models\LoanProduct::find()->where(['id' => $loan_id])->one();
                        $trans_serial = $product->trans_serial + 1;
                        $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

                        $release_date = date("Y-m-d");
                        if($loan['DateTransac'] != ""){
                            $date = explode(" ", $loan['DateTransac']);
                            $date = explode("/", $date[0]);
                            $release_date = $date[2] . "-" . $date[1] . "-" . $date[0];
                        }
                        $addLoanAccount = new \app\models\LoanAccount;
                        $addLoanAccount->account_no = $product->id . "-" . $trans_serial_pad;
                        $addLoanAccount->loan_id = $loan_id;
                        $addLoanAccount->member_id = $getMember->id;
                        $addLoanAccount->principal = floatVal(str_replace(",", "", $loan['PrincipalLoan'])) ;
                        $addLoanAccount->interest_balance = floatVal(str_replace(",", "", $loan['Interest']));
                        $addLoanAccount->principal_balance = floatVal(str_replace(",", "", $loan['Balance']));
                        $addLoanAccount->prepaid = floatVal(str_replace(",", "", $loan['Prepaid'])) ;
                        $addLoanAccount->release_date = $release_date;
                        $addLoanAccount->service_charge = 0;
                        $addLoanAccount->prepaid_int = 0;
                        $addLoanAccount->is_active = 1;
                        $addLoanAccount->status = "Verified";
                        if(!$addLoanAccount->save()){
                            var_dump($addLoanAccount->getErrors());
                        }
                        else{
                            $product->trans_serial = $trans_serial;
                            $product->save();
                        }
                    }
                    
                    //var_dump($loan);
                }

                echo $llm['IDNum'] . "->" . $llm['Sname'] . " ".  $llm['Fname'] . " Is Member \n\t Loans: " . $loans . "\n\n";
                //break;
            }
            else{
                echo $llm['IDNum'] . "->" . $llm['Sname'] . " ".  $llm['Fname'] . " No Member" . "\n\n";
            }
        }
    }*/

    public function actionSeedLoanTransacMember(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_loantransacmember llm');
        $loanMember = $query->all();
        foreach ($loanMember as $key => $llm) {
            $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $llm['IDNum']])->one();
            if($getMember){

                $query2 = new \yii\db\Query;
                $query2->select(['*']);
                $query2->from('zold_loantransac ll')->where(['IDNum' => $llm['IDNum']])->andWhere("PrincipalLoan != ''");
                $query2->groupBy(['LoanType']);
                $loanLedgerMember = $query2->all();
                $loans = "";
                foreach ($loanLedgerMember as $key => $loan) {
                    $loanType = strtoupper($loan['LoanType']);
                    $loan_id = 0;
                    if(strpos($loanType, "APPLIANCE")){
                        $loan_id = 1;
                    }
                    else if(strpos( $loanType, "REGULAR")){
                        $loan_id = 2;
                    }
                    else if(strpos($loanType, "EDUCATIONAL")){
                        $loan_id = 3;
                    }
                    else if(strpos($loanType, "EMERGENCY")){
                        $loan_id = 4;
                    }
                    else if(strpos($loanType, "HOSPITALIZATION")){
                        $loan_id = 5;
                    }
                    else if(strpos($loanType, "HOUSE")){
                        $loan_id = 6;
                    }
                    else if(strpos($loanType, "MEDICAL")){
                        $loan_id = 7;
                    }
                    else if(strpos($loanType, "BUSINESS")){
                        $loan_id = 8;
                    }
                    else if(strpos($loanType, "CELLPHONE")){
                        $loan_id = 10;
                    }
                    else if(strpos($loanType, "CELLCARD")){
                        $loan_id = 11;
                    }
                    else if(strpos($loanType, "BUY-OUT")){
                        $loan_id = 12;
                    }
                    else if(strpos($loanType, "MEMORIAL")){
                        $loan_id = 13;
                    }
                    else if(strpos($loanType, "GADGET")){
                        $loan_id = 14;
                    }
                    else if(strpos($loanType, "DOMESTIC")){
                        $loan_id = 15;
                    }
                    else if(strpos($loanType, "RADIATION")){
                        $loan_id = 16;
                    }
                    else if(strpos($loanType, "CASSEROLE") || $loanType == "CASSEROLE"){
                        $loan_id = 17;
                    }
                    else if(strpos($loanType, "KEYBOARD") || $loanType == "KEYBOARD"){
                        $loan_id = 18;
                    }
                    if($loan_id == 0){
                       $loans .= "No type: " . $loan['LoanType'] . ", "; 
                    }
                    else{
                        //$loans .= "Loan ID: " . $loan_id . ", ";
                        $product = \app\models\LoanProduct::find()->where(['id' => $loan_id])->one();
                        $trans_serial = $product->trans_serial + 1;
                        $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

                        $release_date = date("Y-m-d");
                        if($loan['DateTransac'] != ""){
                            $date = explode(" ", $loan['DateTransac']);
                            $date = explode("/", $date[0]);
                            $release_date = $date[2] . "-" . $date[1] . "-" . $date[0];
                        }
                        /*echo $loan['DateTransac'] . "\n";
                        echo $release_date . "\n";*/
                        $addLoanAccount = new \app\models\LoanAccount;
                        $addLoanAccount->account_no = $product->id . "-" . $trans_serial_pad;
                        $addLoanAccount->loan_id = $loan_id;
                        $addLoanAccount->member_id = $getMember->id;
                        $addLoanAccount->principal = floatVal(str_replace(",", "", $loan['PrincipalLoan'])) ;
                        //$addLoanAccount->interest_balance = floatVal(str_replace(",", "", $loan['Interest']));
                        $addLoanAccount->principal_balance = floatVal(str_replace(",", "", $loan['Balance']));
                        $addLoanAccount->term = floatVal(str_replace(",", "", $loan['Duration'])) ;
                        $addLoanAccount->prepaid = floatVal(str_replace(",", "", $loan['PrePaidInt'])) ;
                        $addLoanAccount->release_date = $release_date ;
                        $addLoanAccount->service_charge = floatVal(str_replace(",", "", $loan['ServChrg'])) ;
                        $addLoanAccount->prepaid_int = 0;
                        $addLoanAccount->is_active = 1;
                        $addLoanAccount->status = "Verified";
                        $addLoanAccount->qiun_principal = floatVal(str_replace(",", "", $loan['QuinPrincipal']));
                        $addLoanAccount->quin_prepaid = floatVal(str_replace(",", "", $loan['QuiPrepaid']));
                        $addLoanAccount->interest_accum = floatVal(str_replace(",", "", $loan['InterestAccum']));
                        $addLoanAccount->prepaid_accum = floatVal(str_replace(",", "", $loan['PrepaidAccum']));
                        $addLoanAccount->olddb_transacnum = floatVal(str_replace(",", "", $loan['TransactNum']));
                        if(!$addLoanAccount->save()){
                            var_dump($addLoanAccount->getErrors());
                        }
                        else{
                            $product->trans_serial = $trans_serial;
                            $product->save();
                        }
                    }
                    
                    //var_dump($loan);
                }

                echo $llm['IDNum'] . "->" . $llm['SName'] . " ".  $llm['Fname'] . " Is Member \n\t Loans: " . $loans . "\n\n";
                //echo $llm['IDNum'] . "->" . $llm['SName'] . " ".  $llm['Fname'] . " Is Member \n\n";
                //break;
            }
            else{
                echo $llm['IDNum'] . "->" . $llm['SName'] . " ".  $llm['Fname'] . " No Member" . "\n\n";
            }
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

    public function actionParticulars(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_particulars');
        $particulars = $query->all();
        foreach ($particulars as $key => $prt) {

            $getAccount = \app\models\Particulars::find()->where(['name' => $prt['Particular'], 'acct_num' => $prt['AcctNum']])->one();
            if($getAccount == null){
                $product = new \app\models\Particulars;
                $product->name = $prt['Particular'];
                $product->acct_num = $prt['AcctNum'];
                $product->save();
            }
        }
    }

    public function actionPayrollParticulars(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_payrollparticular');
        $particulars = $query->all();
        foreach ($particulars as $key => $prt) {

            $getAccount = \app\models\PayrollParticulars::find()->where(['name' => $prt['Particular']])->one();
            if($getAccount == null){
                $product = new \app\models\PayrollParticulars;
                $product->name = $prt['Particular'];
                $product->save();
            }
        }
    }
}


?>