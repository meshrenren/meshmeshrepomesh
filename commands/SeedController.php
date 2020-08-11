<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\db\Query;
use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;
use app\helpers\accounts\TimeDepositHelper;

use app\helpers\payment\PaymentHelper;
use app\helpers\voucher\VoucherHelper;
use app\helpers\journal\JournalHelper;

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

            	if($oldmember->BDay != "---" && $oldmember->BDay != "")   {
            		/*$date = explode("/", $oldmember->BDay);
            		$memdate = date("Y-m-d", strtotime($date[2] . '-' . $date[0] . '-' . $date[1] )); */
                    $memdate = date("Y-m-d", strtotime($oldmember->BDay)); 
            		$newMember->birthday = $memdate;
            	} 
            	if($oldmember->DateMem != "---" && $oldmember->DateMem != "")   {
            		$bday = date("Y-m-d", strtotime($oldmember->DateMem)); 
            		$newMember->mem_date = $bday;
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
                $newMember->old_db_idnum = intval($oldmember->IDNum) ;
                $newMember->old_db_idnum_zero = $oldmember->IDNum;

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
            		$newMember->save(false);


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

    public function actionMemberDate()
    {
        $oldmembers = \app\models\Membersold::find()->orderBy('IDNum ASC')->select(["*"])->all();
        foreach ($oldmembers as $oldmember) {
            $getMember = \app\models\Member::find()->where(['first_name' => $oldmember->FName, 'last_name' =>  $oldmember->SName, 'middle_name' =>  $oldmember->MName])->one();
            if($getMember != null){


                if($oldmember->DateMem != "---" && $oldmember->DateMem != "")   {
                    $date = explode(' ', $oldmember->DateMem) ;
                    $dSub = explode('/', $date[0]) ;
                    $d = date('Y-m-d', strtotime($dSub[2] . '-' . $dSub[1] . '-' .$dSub[0]));

                    $getMember->mem_date = $d;
                } 
                if($oldmember->BDay != "---" && $oldmember->BDay != "")   {
                    $date = explode(' ', $oldmember->DateMem) ;
                    $dSub = explode('/', $date[0]) ;
                    $d = date('Y-m-d', strtotime($dSub[2] . '-' . $dSub[1] . '-' .$dSub[0]));
                    
                    $getMember->birthday = $d;
                } 
                $getMember->save(false);
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

    public function actionUpdateImage()
    {
        $getMembers = \app\models\Member::find()->all();
        foreach ($getMembers as $member) {
            $fileName = $member->first_name . "_" . $member->middle_name . "_" . $member->last_name . ".jpg";
            $image_path = 'images/members/' . $fileName;
            $newImage_path = '/images/user.png';

            if(file_exists($image_path)){
                $newFileName = $member->id . ".jpg";
                $newImage_path = 'images/members/' . $newFileName;
                rename( $image_path, $newImage_path);
                $newImage_path = '/images/members/' . $newFileName;
                echo $fileName . " exist. <br>";
            }else{
                //echo $fileName . " no exist. \n";
            }
            $member->image_path = $newImage_path;
            $member->save();
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

    //Done Seeding loan. Please don't uncomment or run this unless necessary
    public function actionSeedLoanTransacMember(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_loantransacmember llm');
        $loanMember = $query->all();
        $memberCount = 0;
        foreach ($loanMember as $key => $llm) {
            $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $llm['IDNum'], 'last_name' => $llm['Sname']])->one();
            if($getMember){
                $memberCount++;
                $query2 = new \yii\db\Query;
                $query2->select(['*']);
                $query2->from('zold_loantransac ll')->where(['IDNum' => $llm['IDNum']])->andWhere("PrincipalLoan != ''");
                $query2->groupBy(['LoanType']);
                $loanLedgerMember = $query2->all();
                $loans = "";
                foreach ($loanLedgerMember as $key => $loan) {
                    $balance = floatVal(str_replace(",", "", $loan['Balance']));
                    $dateStrpos = strpos($loan['DateTransac'], '2019');
                    $dateYear = date('Y', strtotime($loan['DateTransac']));
                    $skip = true;
                    if($balance > 0){
                        $skip = false;
                    }
                    else if($dateYear >= 2015){
                        $skip = false;
                    }

                    if($skip) continue;
                    $loanType = strtoupper($loan['LoanType']);
                    $term = floatVal(str_replace(",", "", $loan['Duration']));
                    $loan_id = 0;
                    $prepaid_rate = 0;
                    $interest_rate = 0;
                    if(strpos($loanType, "APPLIANCE")){
                        $loan_id = 1;
                        $prepaid_rate = 0;
                        $interest_rate = 2;
                    }
                    else if(strpos( $loanType, "REGULAR")){
                        $loan_id = 2;
                        $prepaid_rate = 0;
                        $interest_rate = 1.25;
                    }
                    else if(strpos($loanType, "EDUCATIONAL")){
                        $loan_id = 3;
                        $prepaid_rate = 0.06;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "EMERGENCY")){
                        $loan_id = 4;
                        $prepaid_rate = 0.0687;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "HOSPITALIZATION")){
                        $loan_id = 5;
                        $prepaid_rate = 0.06;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "HOUSE")){
                        $loan_id = 6;
                        $prepaid_rate = 0.12;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "MEDICAL")){
                        $loan_id = 7;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "BUSINESS")){
                        $loan_id = 8;
                        $prepaid_rate = 0.12;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "CELLPHONE")){
                        $loan_id = 10;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "CELLCARD")){
                        $loan_id = 11;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "BUY-OUT")){
                        $loan_id = 12;
                        $prepaid_rate = floatval($term)/100;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "MEMORIAL")){
                        $loan_id = 13;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "GADGET")){
                        $loan_id = 14;
                        $prepaid_rate = floatval($term)/100;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "DOMESTIC")){
                        $loan_id = 15;
                        if($term == 12){
                            $prepaid_rate = 0.2;
                        }
                        else if($term == 6){
                            $prepaid_rate = 0.1;
                        }
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "RADIATION")){
                        $loan_id = 16;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "CASSEROLE") || $loanType == "CASSEROLE"){
                        $loan_id = 17;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    else if(strpos($loanType, "KEYBOARD") || $loanType == "KEYBOARD"){
                        $loan_id = 18;
                        $prepaid_rate = 0;
                        $interest_rate = 0;
                    }
                    if($loan_id == 0){
                       $loans .= "No type: " . $loan['LoanType'] . ", "; 
                    }
                    else{
                        $loans .= "Loan ID: " . $loan_id . ", ";
                        $product = \app\models\LoanProduct::find()->where(['id' => $loan_id])->one();
                        $trans_serial = $product->trans_serial + 1;
                        $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

                        $release_date = date("Y-m-d");
                        if($loan['DateTransac'] != ""){
                            //$date = explode(" ", $loan['DateTransac']);
                            //$date = explode("/", $date[0]);
                            //$release_date = $date[2] . "-" . $date[1] . "-" . $date[0];
                            $date = date("Y-m-d", strtotime($loan['DateTransac']));
                            $release_date = $date;
                        }
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
                        $addLoanAccount->int_rate = $interest_rate;
                        $addLoanAccount->prepaid_int = $prepaid_rate;
                        $addLoanAccount->is_active = 1;
                        $addLoanAccount->status = "Current";
                        $addLoanAccount->principal_amortization_quincena = floatVal(str_replace(",", "", $loan['QuinPrincipal']));
                        $addLoanAccount->prepaid_amortization_quincena = floatVal(str_replace(",", "", $loan['QuiPrepaid']));
                        $addLoanAccount->qiun_principal = floatVal(str_replace(",", "", $loan['QuinPrincipal']));
                        $addLoanAccount->quin_prepaid = floatVal(str_replace(",", "", $loan['QuiPrepaid']));
                        $addLoanAccount->interest_accum = floatVal(str_replace(",", "", $loan['InterestAccum']));
                        $addLoanAccount->prepaid_accum = floatVal(str_replace(",", "", $loan['PrepaidAccum']));
                        $addLoanAccount->olddb_transacnum = $loan['TransactNum'];
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
                //echo $llm['IDNum'] . "->" . $llm['SName'] . " ".  $llm['Fname'] . " Is Member \n\n";
                //break;
            }
            else{
                echo $llm['IDNum'] . "->" . $llm['Sname'] . " ".  $llm['Fname'] . " No Member" . "\n\n";
            }
        }
    }

    public function actionLoanLedger(){

        $loanAccount = \app\models\LoanAccount::find()->all();

        foreach ($loanAccount as $key => $accs) {
            $query = new \yii\db\Query;
            $query->select('*');
            $query->from('zold_loanledger ll')->where("TransacNum = '".$accs->olddb_transacnum."' AND PrincipalLoan != ''")->orderBy('EntryNum DESC');
            $getPLegder = $query->one();
            if($getPLegder != null){
                echo $accs->account_no . "\tHAs Ledger: \t" .  $getPLegder['DateTransac'] . "\n";

                $query = new \yii\db\Query;
                $query->select('*');
                $query->from('zold_loanledger ll')->where("TransacNum = '".$accs->olddb_transacnum."' AND EntryNum >= ".$getPLegder['EntryNum']);
                $getLegder = $query->all();
                if(count($getLegder) > 0){
                    echo "\tList Ledger: \t" .  count($getLegder) . "\n";
                    foreach ($getLegder as $key2 => $ledger) {
                        $getTrans = \app\models\LoanTransaction::find()->where(['olddb_entrynum' => $ledger['EntryNum'], 'loan_account' => $accs->account_no])->one();
                        if($getTrans != null){
                            continue;
                        }
                        /*$date = explode(' ', $ledger['DateTransac']) ;
                        $dSub = explode('/', $date[0]) ;
                        $d = date('Y-m-d', strtotime($dSub[2] . '-' . $dSub[1] . '-' .$dSub[0]));*/
                        $d = date("Y-m-d", strtotime($ledger['DateTransac']));

                        $type = "PAYPARTIAL";
                        $amount = 0;
                        $AmtPaid = 0;
                        $Prepaid = 0;
                        $Interest = 0;
                        $Interest_earn = 0;
                        $Balance = floatval(str_replace(",", "", $ledger['Balance']));
                        if($ledger['PrincipalLoan'] != ''){
                            $type = "RELEASE";
                            $amount = floatval(str_replace(",", "", $ledger['PrincipalLoan']));
                        }else{
                            $AmtPaid = floatval(str_replace(",", "", $ledger['AmtPaid']));
                            $Prepaid = floatval(str_replace(",", "", $ledger['Prepaid']));
                            $Interest = floatval(str_replace(",", "", $ledger['Interest']));
                            $Interest_earn = $Interest;
                            $amount = $AmtPaid + $Prepaid;

                            if($ledger['Remarks'] == 'End'){
                                $amount = $AmtPaid + $Prepaid + $Interest;
                                $Interest_earn = 0;
                            }else{
                                $Interest = 0;
                            }                       
                        }

                        $addLoanAccount = new \app\models\LoanTransaction;
                        $addLoanAccount->loan_account = $accs->account_no;
                        $addLoanAccount->amount = $amount;
                        $addLoanAccount->loan_id = $accs->loan_id;
                        $addLoanAccount->member_id = $accs->member_id;
                        $addLoanAccount->transaction_type = $type;
                        $addLoanAccount->transacted_by = 18;
                        $addLoanAccount->transaction_date = $d;
                        $addLoanAccount->running_balance = $Balance;
                        $addLoanAccount->remarks = "Migrate from old db";
                        $addLoanAccount->prepaid_intpaid = $Prepaid;
                        $addLoanAccount->interest_paid = $Interest;
                        $addLoanAccount->OR_no = $ledger['GVORNum'];
                        $addLoanAccount->principal_paid = $AmtPaid;
                        $addLoanAccount->arrears_paid = 0;
                        $addLoanAccount->date_posted = $d;
                        $addLoanAccount->interest_earned = $Interest_earn;
                        $addLoanAccount->olddb_entrynum = $ledger['EntryNum'];

                        if($addLoanAccount->save()){
                            echo $ledger['EntryNum'] . "\tSuccess Save \n";
                        }
                        else{
                            var_dump($addLoanAccount->getErrors());
                        }

                    }
                }/*else{
                    echo $accs->account_no . "\tNo Ledger: \n";
                }*/
            }

            /*if(count($getLegder) > 0){
                echo $accs->account_no . "\tHAs Ledger: \t" .  count($getLegder) . "\n";
                foreach ($getLegder as $key2 => $ledger) {
                    
                }
            }else{
                echo $accs->account_no . "\tNo Ledger: \n";
            }*/
            
        }
    }

    public function actionSeedShareAccount(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_memshare scl');
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
                    $account->old_db_idnum_zero = $cs['IDNum'];
                    $account->old_db_sname = $cs['SName'];
                    $account->old_db_fname = $cs['FName'];

                    if($account->save()){
                        $product->transaction_serial = $trans_serial;
                        $product->save();

                        echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " Account created: \tAccountNumber->" . $account->accountnumber ."\tName: " . $getMember->last_name . " ".  $getMember->first_name . "\n";
                    }


                }
                
            }
            else{
                echo $cs['IDNum'] . "->" . $cs['SName'] . " ".  $cs['FName'] . " No Member" . "\n";
            }
        }
    }

    public function actionShareTransaction(){
        $accounts = \app\models\Shareaccount::find()->all();

        foreach ($accounts as $key => $acc) {
            $getTRans = \app\models\ShareTransaction::find()->where(['fk_share_id' => $acc->accountnumber])->count(); 
            if($getTRans > 0){
                continue;
            }

            $query = new \yii\db\Query;
            $query->select('*');
            $query->from('zold_sharecapledger scl')->where(['IDNum' => $acc->old_db_idnum_zero, 'SName' => $acc->old_db_sname, 'FName' => $acc->old_db_fname]);
            $shareLedger = $query->orderBy('EntryNum ASC')->all();
            $total = 0;
            foreach ($shareLedger as $key => $ledger) {
                $type = "";
                $amount = 0;
                $Receive = floatval(str_replace(",", "", $ledger['Receive']));
                $Withdrawal = floatval(str_replace(",", "", $ledger['Withdrawal']));
                $Balance = floatval(str_replace(",", "", $ledger['Balance']));

                if($ledger['Receive'] != '' && $Receive > 0){
                    $type = "CASHDEP";
                    $amount = $Receive;
                }
                else if($ledger['Withdrawal'] != '' && $Withdrawal > 0){
                    $type = "WITHDRWL";
                    $amount = $Withdrawal;
                }
                else{
                    if($ledger['EntryNum'] == 1 && $Balance > 0){
                        $type = "CASHDEP";
                        $amount = $Balance;
                    }
                    else{
                        continue;
                    }
                }

                /*$date = explode(' ', $ledger['DateTransac']) ;
                $dSub = explode('/', $date[0]) ;
                $d = date('Y-m-d', strtotime($dSub[2] . '-' . $dSub[1] . '-' .$dSub[0]));*/
                $d = date("Y-m-d", strtotime($ledger['DateTransac']));

                $addTrans = new \app\models\ShareTransaction;
                $addTrans->fk_share_id = $acc->accountnumber;
                $addTrans->reference_number = $ledger['ORGVNum'];
                $addTrans->amount = $amount;
                $addTrans->transaction_type = $type;
                $addTrans->transacted_by = 18;
                $addTrans->transaction_date = $d;
                $addTrans->running_balance = 0;
                $addTrans->olddb_balance = $Balance;
                $addTrans->remarks = "Migrate from old DB";
                $addTrans->olddb_entrynum = $ledger['EntryNum'];
                if($addTrans->save()){
                    if($type == "CASHDEP"){
                        $total += $amount;
                    }
                    else if($type == "WITHDRWL"){
                        $total -= $amount;
                    }

                    $addTrans->running_balance = $total;
                    $addTrans->save();
                }
            }
            $acc->balance = $total;
            $acc->save();
            echo "Save \t" . $total . " \n" ;
        }
    }

    public function actionSeedSavingsAccount(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_sdtransac sdl');
        $capitalShare = $query->all();
        foreach ($capitalShare as $key => $cs) {
            if($cs['IDNum'] != '' && $cs['SName'] != '' && $cs['FName'] != ''){
                $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $cs['IDNum'], 'last_name' => $cs['SName'], 'first_name' => $cs['FName']])->one();
                if($getMember){
                    $getAccount = \app\models\SavingAccounts::find()->where(['member_id' => $getMember->id])->one();
                    if($getAccount == null){
                        $product = \app\models\Savingsproduct::find()->where(['id' => 1])->one();
                        $trans_serial = $product->trans_serial + 1;
                        $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

                        $d = date("Y-m-d H:i:s", strtotime($cs['DateOpen']));
                        $account = new \app\models\SavingAccounts;
                        $account->account_no = $product->id . "-" . $trans_serial_pad;
                        $account->saving_product_id = $product->id;
                        $account->member_id = $getMember->id;
                        $account->balance = 0;
                        $account->is_active = 1;
                        $account->date_created = $d;
                        $account->transacted_date = $d;
                        $account->old_db_name = $cs['Name'];
                        $account->old_db_idnum_zero = $cs['IDNum'];
                        $account->old_db_sname = $cs['SName'];
                        $account->old_db_fname = $cs['FName'];


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
            else{
                $getAccount = \app\models\SavingAccounts::find()->where(['account_name' => $cs['Name']])->one();
                if($getAccount == null){
                    $product = \app\models\Savingsproduct::find()->where(['id' => 1])->one();
                    $trans_serial = $product->trans_serial + 1;
                    $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);


                    $d = date("Y-m-d H:i:s", strtotime($cs['DateOpen']));
                    $account = new \app\models\SavingAccounts;
                    $account->account_no = $product->id . "-" . $trans_serial_pad;
                    $account->saving_product_id = $product->id;
                    $account->account_name = $cs['Name'];
                    $account->member_id = null;
                    $account->balance = 0;
                    $account->is_active = 1;
                    $account->date_created = $d;
                    $account->transacted_date = $d;
                    $account->old_db_name = $cs['Name'];


                    if($account->save()){
                        $product->trans_serial = $trans_serial;
                        $product->save();

                        echo $cs['Name'] . "->" . " Account created: \tAccountNumber->" . $account->account_no ."\tName: " . $account->account_name . "\n";
                    }
                    else{
                        echo $cs['Name'] . "->" . " Account not created: \tAccountNumber->". "\n";
                    }


                }
            }
            
        }
    }

    public function actionSavingsTransaction(){
        $accounts = \app\models\SavingAccounts::find()->all();

        foreach ($accounts as $key => $acc) {
            $getTRans = \app\models\SavingsTransaction::find()->where(['fk_savings_id' => $acc->account_no])->count(); 
            if($getTRans > 0){
                continue;
            }

            if($acc->member_id != null){
                $query = new \yii\db\Query;
                $query->select('*');
                $query->from('zold_sdledger scl')->where(['IDNum' => $acc->old_db_idnum_zero, 'Name' => $acc->old_db_name, 'SName' => $acc->old_db_sname, 'FName' => $acc->old_db_fname]);
                $savingsLedger = $query->orderBy('Numbering ASC')->all();
            }
            else{
                $query = new \yii\db\Query;
                $query->select('*');
                $query->from('zold_sdledger scl')->where(['Name' => $acc->old_db_name]);
                $savingsLedger = $query->orderBy('Numbering ASC')->all();
            }
            $total = 0;
            foreach ($savingsLedger as $key => $ledger) {
                $type = "";
                $amount = 0;
                $Deposit = floatval(str_replace(",", "", $ledger['Deposit']));
                $Withdrawal = floatval(str_replace(",", "", $ledger['Withdrawal']));
                $Balance = floatval(str_replace(",", "", $ledger['Balance']));

                if($ledger['Deposit'] != '' && $Deposit > 0){
                    $type = "CASHDEP";
                    $amount = $Deposit;
                }
                else if($ledger['Withdrawal'] != '' && $Withdrawal > 0){
                    $type = "WITHDRWL";
                    $amount = $Withdrawal;
                }
                else{
                    var_dump($ledger['Numbering']);
                    var_dump($Balance);
                    if($ledger['Numbering'] == "1" && $Balance > 0){
                        $type = "CASHDEP";
                        $amount = $Balance;
                        var_dump("Here");
                    }
                    else{
                        continue;
                    }
                }

                /*$date = explode(' ', $ledger['DateTransac']) ;
                $dSub = explode('/', $date[0]) ;
                $d = date('Y-m-d', strtotime($dSub[2] . '-' . $dSub[1] . '-' .$dSub[0]));*/
                if($ledger['DateTransac'] == '0000-00-00 00:00:00.000000'){
                    $d = '2010-06-01';
                }
                else{
                    $d = date("Y-m-d", strtotime($ledger['DateTransac']));
                }
                

                $addTrans = new \app\models\SavingsTransaction;
                $addTrans->fk_savings_id = $acc->account_no;
                $addTrans->ref_no = $ledger['ORGVNum'];
                $addTrans->amount = $amount;
                $addTrans->transaction_type = $type;
                $addTrans->transacted_by = 18;
                $addTrans->transaction_date = $d;
                $addTrans->running_balance = 0;
                $addTrans->olddb_balance = $Balance;
                $addTrans->remarks = "Migrate from old DB";
                $addTrans->olddb_entrynum = $ledger['Numbering'];
                if($addTrans->save()){
                    if($type == "CASHDEP"){
                        $total += $amount;
                        echo "Add:" . $total . "\n";
                    }
                    else if($type == "WITHDRWL"){
                        $total -= $amount;
                        echo "Sub:" . $total . "\n";
                    }

                    $addTrans->running_balance = $total;
                    $addTrans->save();
                }
                else{
                    var_dump($addTrans->getErrors());
                }
            }
            $acc->balance = $total;
            $acc->save();
            echo "Save \t" . $total . "\n";            
        }
    }


    /*public function actionSavingsAccountBalance(){
        $query = new \yii\db\Query;
        $query->select('*');
        $query->from('zold_sdtransac sdt');
        $capitalShare = $query->all();
        foreach ($capitalShare as $key => $cs) {
            $balance = str_replace(",", "", $cs['Balance']);
            $balance = floatval($balance);
            $getAccount = \app\models\SavingAccounts::find()->joinWith(['member'])->where(['member.last_name' => $cs['SName'], 'member.first_name' => $cs['FName'] ])->orderBy('member.last_name ASC')->one();
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
    }*/


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

    public function actionPaymentHasPayroll(){
        $query = new \yii\db\Query;
        $query->select('Distinct(ORNum), IDNum, Sname, Name, DateTransac');
        //$query->from('zold_paymentrec')->where(['ORNum' => 'n5706'])->orderBy('ORNum');
        $query->from('zold_paymentrec')->where("DateTransac >= '2015-01-01'")->orderBy('ORNum');
        $payrollRec = $query->all();

        foreach ($payrollRec as $key => $rec) {

            $getOR = \app\models\PaymentRecord::find()->where(['or_num' => $rec['ORNum']])->one();
            if($getOR != null){
                echo 'Payment exist: ' . $rec['ORNum'] . "\n";
                continue;
            }

            $success = null;

            $recordList = [];
            $journalList = [];
            $totalCredit = 0;
            $totalDebit = 0;
            echo ($rec['ORNum'] . "\t" . $rec['Name']) . "\n";


            //Get
            $total = 0;
            $totalpayroll = 0;
            $query3 = new \yii\db\Query;
            $query3->select('*');
            $query3->from('zold_payrolllist')->where(['ORNum' => $rec['ORNum']]);
            $payrollRec3 = $query3->all();
            if(count($payrollRec3) > 0){
                $totalpayroll = 0;
                foreach ($payrollRec3 as $key3 => $rec3) {

                    $getMember = \app\models\Member::find()->where("CONCAT(last_name, ', ', first_name, ' ', middle_name) = '" . $rec3['Name'] . "'")->one();
                    $member_id = null;
                    if($getMember){
                        $member_id = $getMember->id;
                        //echo "\t Has Member: \t" . $getMember->last_name . "\n";
                    }else{
                        //echo "\t No Member: \t" . $rec3['Name'] . "\n";
                    }

                    $ShareDeposit = floatval(str_replace(",", "", $rec3['ShareDeposit']));
                    $RL = floatval(str_replace(",", "", $rec3['RL']));
                    $PIonRL = floatval(str_replace(",", "", $rec3['PIonRL']));
                    $PCL = floatval(str_replace(",", "", $rec3['PCL']));
                    $EduL = floatval(str_replace(",", "", $rec3['EduL']));
                    $HL = floatval(str_replace(",", "", $rec3['HL']));
                    $SD = floatval(str_replace(",", "", $rec3['SD']));
                    $MCL = floatval(str_replace(",", "", $rec3['MCL']));
                    $HIL = floatval(str_replace(",", "", $rec3['HIL']));
                    $AL = floatval(str_replace(",", "", $rec3['AL']));
                    $PIonAL = floatval(str_replace(",", "", $rec3['PIonAL']));
                    $HC = floatval(str_replace(",", "", $rec3['HC']));
                    $Mortuary = floatval(str_replace(",", "", $rec3['Mortuary']));
                    $OBL = floatval(str_replace(",", "", $rec3['OBL']));
                    $BUL = floatval(str_replace(",", "", $rec3['BUL']));
                    $ML = floatval(str_replace(",", "", $rec3['ML']));
                    $EG = floatval(str_replace(",", "", $rec3['EG']));
                    $DML = floatval(str_replace(",", "", $rec3['DML']));
                    $Time = floatval(str_replace(",", "", $rec3['Time']));
                    $CPL = floatval(str_replace(",", "", $rec3['CPL']));
                    $CCL = floatval(str_replace(",", "", $rec3['CCL']));
                    $AntiRad = floatval(str_replace(",", "", $rec3['AntiRad']));
                    $Casserole = floatval(str_replace(",", "", $rec3['Casserole']));
                    $Keyboard = floatval(str_replace(",", "", $rec3['Keyboard']));
                    $LGCode = floatval(str_replace(",", "", $rec3['LGCode']));
                    $RaffleTicket = floatval(str_replace(",", "", $rec3['RaffleTicket']));
                    $Catering = floatval(str_replace(",", "", $rec3['Catering']));
                    $RiceLoan = floatval(str_replace(",", "", $rec3['RiceLoan']));
                    $TShirt = floatval(str_replace(",", "", $rec3['TShirt']));
                    $NotarialFee = floatval(str_replace(",", "", $rec3['NotarialFee']));
                    $Misc = floatval(str_replace(",", "", $rec3['Misc']));

                    $list = [
                        'ShareDeposit' => $ShareDeposit,
                        'RL' => $RL,
                        'PIonRL' => $PIonRL,
                        'PCL' => $PCL,
                        'EduL' => $EduL,
                        'HL' => $HL,
                        'SD' => $SD,
                        'MCL' => $MCL,
                        'HIL' => $HIL,
                        'AL' => $AL,
                        'PIonAL' => $PIonAL,
                        'HC' => $HC,
                        'Mortuary' => $Mortuary,
                        'OBL' => $OBL,
                        'BUL' => $BUL,
                        'EG' => $EG,
                        'DML' => $DML,
                        'Time' => $Time,
                        'CPL' => $CPL,
                        'CCL' => $CCL,
                        'AntiRad' => $AntiRad,
                        'Casserole' => $Casserole,
                        'Keyboard' => $Keyboard,
                        'LGCode' => $LGCode,
                        'RaffleTicket' => $RaffleTicket,
                        'Catering' => $Catering,
                        'RiceLoan' => $RiceLoan,
                        'TShirt' => $TShirt,
                        'NotarialFee' => $NotarialFee,
                        'Misc' => $Misc
                    ];


                    if($rec3['Name'] == 'TOTAL'){
                        $total += $ShareDeposit + $RL + $PIonRL + $PCL + $EduL + $HL + $SD + $MCL + $HIL + $AL + $PIonAL + $HC + $Mortuary + $OBL + $BUL + $EG + $DML + $CPL + $CCL + $AntiRad + $Casserole + $Keyboard + $LGCode + $RaffleTicket + $Catering + $RiceLoan + $TShirt + $NotarialFee + $Misc;
                        continue;
                    }
                    else{
                        $totalpayroll += $ShareDeposit + $RL + $PIonRL + $PCL + $EduL + $HL + $SD + $MCL + $HIL + $AL + $PIonAL + $HC + $Mortuary + $OBL + $BUL + $EG + $DML + $CPL + $CCL + $AntiRad + $Casserole + $Keyboard + $LGCode + $RaffleTicket + $Catering + $RiceLoan + $TShirt + $NotarialFee + $Misc;
                    }

                    $res = $this->paymentList($member_id, $list, $rec3['Name']);
                    $totalCredit += $res['totalCredit'];
                    $totalDebit += $res['totalDebit'];

                    $recordList = array_merge($recordList, $res['recordList']);
                    $journalList = array_merge($journalList, $res['journalList']);
                }

                if($totalpayroll != $total){
                    echo "totalpayroll \t" . $totalpayroll. "\n";
                    echo "total \t" . $total. "\n";
                    echo "\Payroll Not Equal \t" . $totalpayroll . "\t" . $total  . "\n";
                }
 
            }
            else{

                $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $rec['IDNum'], 'last_name' => $rec['Sname']])->one();
                $member_id = null;
                if($getMember){
                    $member_id = $getMember->id;
                }

                $paymentRec = new \app\models\PaymentRecordList;
                $paymentRecAttr = $paymentRec->getAttributes();
                $paymentRecAttr['member_id'] = $member_id ? $member_id : null;
                $paymentRecAttr['name'] = $rec['Name'];

                $journal = new \app\models\JournalDetails;
                $journalListAttr = $journal->getAttributes();


                $totalpayroll = 0;
                $query3 = new \yii\db\Query;
                $query3->select('*');
                $query3->from('zold_paymentrec')->where(['ORNum' => $rec['ORNum']]);
                $payrollRec3 = $query3->all();

                foreach ($payrollRec3 as $key3 => $rec3) {
                    if($rec3['Description'] == 'TOTAL'){
                        continue;
                    }
                    $AmtPaid = floatval(str_replace(",", "", $rec3['AmtPaid']));
                    $totalpayroll += $AmtPaid;

                    $getParticular = ParticularHelper::getParticular(['name' => $rec3['Description']]);
                    if($getParticular){
                        //Payment
                        $prArr = $paymentRecAttr;
                        $prArr['type'] = $getParticular['category'];
                        $prArr['amount'] = $AmtPaid;
                        $prArr['particular_id'] = $getParticular->id;
                        $prArr['product_id'] = $getParticular->product_id;
                        $prArr['account_no'] = null;

                        //Journal
                        $jrArr = $journalListAttr;
                        $jrArr['amount'] = $AmtPaid;
                        $jrArr['entry_type'] = 'DEBIT';
                        $jrArr['particular_id'] = $getParticular->id;


                        if($getParticular['category'] == "SAVINGS"){

                            $getSDAcct = SavingsHelper::getAccountSavingsInfo(['member_id' => $member_id], false);
                            $getAcct = null;
                            if(count($getSDAcct) > 0){
                                $getAcct = $getSDAcct[0];
                            }
                            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;

                            
                            $jrArr['entry_type'] = 'CREDIT';
                            $totalCredit += $AmtPaid; 
                        }
                        else if($getParticular['category'] == "SHARE"){

                            $getShareAcct = ShareHelper::getAccountShareInfo($member_id, false);
                            $getAcct = null;
                            if(count($getShareAcct) > 0){
                                $getAcct = $getShareAcct[0];
                            }
                            $prArr['account_no'] = $getAcct ? $getAcct->accountnumber : null;
                            
                            $jrArr['entry_type'] = 'CREDIT';
                            $totalCredit += $AmtPaid; 
                        }
                        else if($getParticular['category'] == "TIME_DEPOSIT"){

                            $jrArr['entry_type'] = 'CREDIT';
                            $totalCredit += $AmtPaid; 
                        }
                        else if($getParticular['category'] == "LOAN"){
                            $getAcct = LoanHelper::getMemberLoan($member_id, $getParticular->product_id, false);
                            $getAcct = $getAcct ? $getAcct : null;

                            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;

                            if (strpos($getParticular['name'], 'PI on') !== false) {
                               $prArr['is_prepaid'] = 1; 
                            }

                            $totalDebit += $AmtPaid; 
                        }
                        else{
                            $totalDebit += $AmtPaid; 
                        }


                        array_push($recordList, $prArr);
                        array_push($journalList, $jrArr);
                    }
                    else{
                        $particular_id = 28; //Misc 
                        if (strpos($rec3['Description'], 'PCL') !== false) {
                           $particular_id = 29; //PCL
                        }

                        //Payment
                        $prArr = $paymentRecAttr;
                        $prArr['type'] = 'OTHERS';
                        $prArr['amount'] = $AmtPaid;
                        $prArr['particular_id'] = $particular_id;
                        $prArr['product_id'] = null;
                        $prArr['account_no'] = null;
                        array_push($recordList, $prArr);

                        //Journal
                        $jrArr = $journalListAttr;
                        $jrArr['amount'] = $AmtPaid;
                        $jrArr['entry_type'] = 'DEBIT';
                        $jrArr['particular_id'] = $particular_id;
                        array_push($journalList, $jrArr);

                        $totalDebit += $AmtPaid; 
                        //echo "\tNo Particular : \t" . $rec3['Description'] . "\t" . $particular_id . "\n";
                    }
                }
                $total = $totalpayroll;
            }

            //echo "\tHas Payroll List \t" . $totalpayroll . "\n";

            $d = date("Y-m-d", strtotime($rec['DateTransac']));

            $PaymentRecord = [];
            $PaymentRecord['or_num'] = $rec['ORNum'];
            $PaymentRecord['name'] = $rec['Name'];
            $PaymentRecord['type'] = 'Individual';
            $PaymentRecord['posting_code'] = '';
            $PaymentRecord['check_number'] = '';
            $PaymentRecord['amount_paid'] = $totalpayroll;
            $PaymentRecord['date_transact'] = $d;


            $saveOR = PaymentHelper::savePayment($PaymentRecord);
            if($saveOR){
                $success = true;
                $insertSuccess = PaymentHelper::insertAccount($recordList, $saveOR->id);
                if($insertSuccess){
                    $success = true;
                }
                else{
                    $success = false;
                }
            }
            else{
                $success = false;
            }

            if($success){
                //Journal
                $journalHeaderData = array();
                $journalHeaderData['reference_no'] = $rec['ORNum'];
                $journalHeaderData['posting_date'] = $saveOR->date_transact;
                $journalHeaderData['transacted_date'] = $saveOR->date_transact;
                $journalHeaderData['total_amount'] = $totalpayroll;
                $journalHeaderData['trans_type'] = 'Payment';
                $journalHeaderData['remarks'] = '';

                $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                if($saveJournal){

                    if($totalCredit > 0){
                        $arr = [];
                        $arr['amount'] = $totalCredit;
                        $arr['particular_id'] = 99;
                        $arr['entry_type'] = "DEBIT";
                        array_push($journalList, $arr);
                    } 

                    if($totalDebit > 0){
                        $arr = [];
                        $arr['amount'] = $totalDebit;
                        $arr['particular_id'] = 99;
                        $arr['entry_type'] = "CREDIT";
                        array_push($journalList, $arr);
                    }

                    $insertSuccess = JournalHelper::insertJournal($journalList, $saveJournal->reference_no);
                    if($insertSuccess){
                        $success = true;
                    }
                    else{
                        $success = false;
                    }
                }
                else{
                    $success = false;
                }

            }

            
        }
    }

    public function paymentList($member_id, $list, $name){
        $recordList = [];
        $journalList = [];
        $totalCredit = 0;
        $totalDebit = 0;

        $paymentRec = new \app\models\PaymentRecordList;
        $paymentRecAttr = $paymentRec->getAttributes();
        $paymentRecAttr['member_id'] = $member_id ? $member_id : null;
        $paymentRecAttr['name'] = $name;

        $journal = new \app\models\JournalDetails;
        $journalListAttr = $journal->getAttributes();

        if($list['ShareDeposit'] > 0){
            $amt = $list['ShareDeposit'];
            $getShareAcct = ShareHelper::getAccountShareInfo($member_id, false);
            $getShareProd = ShareHelper::getProduct(['id' => 1], false);
            $getAcct = null;
            if(count($getShareAcct) > 0){
                $getAcct = $getShareAcct[0];
            }

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'SHARE';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = $getShareProd->particular_id;
            $prArr['product_id'] = $getShareProd->id;
            $prArr['account_no'] = $getAcct ? $getAcct->accountnumber : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'CREDIT';
            $jrArr['particular_id'] = $getShareProd->particular_id;
            array_push($journalList, $jrArr);

            $totalCredit += $amt;                        
        }

        //SAving Deposit
        if($list['SD'] > 0){
            $amt = $list['SD'];
            $getSDAcct = SavingsHelper::getAccountSavingsInfo(['member_id' => $member_id], false);
            $getSDProd = SavingsHelper::getProduct(['id' => 1], false);
            $getAcct = null;
            if(count($getSDAcct) > 0){
                $getAcct = $getSDAcct[0];
            }

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'SAVINGS';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = $getSDProd->particular_id;
            $prArr['product_id'] = $getSDProd->id;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'CREDIT';
            $jrArr['particular_id'] = $getSDProd->particular_id;
            array_push($journalList, $jrArr);

            $totalCredit += $amt;                         
        }


        //Regular Loan
        if($list['RL'] > 0){
            $amt = $list['RL'] + $list['PIonRL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 2, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 4;
            $prArr['product_id'] = 2;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 4;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        //PI Regular Loan
        if($list['PIonRL'] > 0){
            $amt = $list['RL'] + $list['PIonRL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 2, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 5;
            $prArr['product_id'] = 2;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            $prArr['is_prepaid'] = 1;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 5;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        //Educational Loan
        if($list['EduL'] > 0){
            $amt = $list['EduL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 3, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 9;
            $prArr['product_id'] = 3;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 9;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        //Hospitalization Loan
        if($list['HL'] > 0){
            $amt = $list['HL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 5, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 11;
            $prArr['product_id'] = 5;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 11;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        //MEDICAL CARE LOAN
        if($list['MCL'] > 0){
            $amt = $list['MCL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 7, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 14;
            $prArr['product_id'] = 7;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 14;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        // HOUSE IMPROVEMENT LOAN
        if($list['HIL'] > 0){
            $amt = $list['HIL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 6, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 12;
            $prArr['product_id'] = 6;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 12;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }

        if($list['AL'] > 0){
            $amt = $list['AL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 1, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 2;
            $prArr['product_id'] = 1;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 2;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        if($list['PIonAL'] > 0){
            $amt = $list['PIonAL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 1, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 3;
            $prArr['product_id'] = 1;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            $prArr['is_prepaid'] = 1;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 3;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        //OTHER BUSINESS LOAN
        if($list['OBL'] > 0){
            $amt = $list['OBL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 8, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 15;
            $prArr['product_id'] = 8;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 15;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }

        //BUY-OUT LOAN
        if($list['BUL'] > 0){
            $amt = $list['BUL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 12, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 37;
            $prArr['product_id'] = 12;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 37;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }

        //EMERGENCY LOAN
        if($list['EG'] > 0){
            $amt = $list['EG'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 4, false);
            $getAcct = $getAcct ? $getAcct : null;
            
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 10;
            $prArr['product_id'] = 4;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 10;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }

        //DOMESTIC MERCHANDISE LOAN
        if($list['DML'] > 0){
            $amt = $list['DML'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 15, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 56;
            $prArr['product_id'] = 15;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 56;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }


        //CELLPHONE LOAN
        if($list['CPL'] > 0){
            $amt = $list['CPL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 10, false);
            $getAcct = $getAcct ? $getAcct : null;
           
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 16;
            $prArr['product_id'] = 10;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 16;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        //CELLCARD LOAN
        if($list['CCL'] > 0){
            $amt = $list['CCL'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 11, false);
            $getAcct = $getAcct ? $getAcct : null;
            
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 18;
            $prArr['product_id'] = 11;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 18;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }


        if($list['AntiRad'] > 0){
            $amt = $list['AntiRad'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 16, false);
            $getAcct = $getAcct ? $getAcct : null;
            
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 31;
            $prArr['product_id'] = 16;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 31;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }


        if($list['Casserole'] > 0){
            $amt = $list['Casserole'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 17, false);
            $getAcct = $getAcct ? $getAcct : null;
            
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 32;
            $prArr['product_id'] = 17;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 32;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }


        if($list['Keyboard'] > 0){
            $amt = $list['Keyboard'];
            $getAcct = LoanHelper::getMemberLoan($member_id, 18, false);
            $getAcct = $getAcct ? $getAcct : null;

            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'LOAN';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 33;
            $prArr['product_id'] = 18;
            $prArr['account_no'] = $getAcct ? $getAcct->account_no : null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 33;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        if($list['PCL'] > 0){
            $amt = $list['PCL'];
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'OTHERS';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 29;
            $prArr['product_id'] = null;
            $prArr['account_no'] = null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 29;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                       
        }

        //HEalth Care
        if($list['HC'] > 0){
            $amt = $list['HC'];
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'OTHERS';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 21;
            $prArr['product_id'] = null;
            $prArr['account_no'] = null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 21;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        if($list['Mortuary'] > 0){
            $amt = $list['Mortuary'];
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'OTHERS';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 22;
            $prArr['product_id'] = null;
            $prArr['account_no'] = null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 22;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                         
        }

        if($list['Misc'] > 0){
            $amt = $list['Misc'];
            //Payment
            $prArr = $paymentRecAttr;
            $prArr['type'] = 'OTHERS';
            $prArr['amount'] = $amt;
            $prArr['particular_id'] = 28;
            $prArr['product_id'] = null;
            $prArr['account_no'] = null;
            array_push($recordList, $prArr);

            //Journal
            $jrArr = $journalListAttr;
            $jrArr['amount'] = $amt;
            $jrArr['entry_type'] = 'DEBIT';
            $jrArr['particular_id'] = 28;
            array_push($journalList, $jrArr);

            $totalDebit += $amt;                        
        }

        return [
            'recordList'    => $recordList,
            'journalList'   => $journalList,
            'totalCredit'   => $totalCredit,
            'totalDebit'    => $totalDebit,
        ];
    }

    public function actionSeedVoucher(){
        $query = new \yii\db\Query;
        $query->select('Distinct(GVNum), Name, DateTransac');
        $query->from('zold_voucher')->where("DateTransac >= '2015-01-01'")->groupBy('GVNum');
        $payrollRec = $query->all();

        foreach ($payrollRec as $key => $rec) {

            $getOR = \app\models\GeneralVoucher::find()->where(['gv_num' => $rec['GVNum']])->one();
            if($getOR != null){
                echo 'Voucher exist: ' . $rec['GVNum'] . "\n";
                continue;
            }

            $voucherDet = new \app\models\VoucherDetails;
            $voucherDetAttr = $voucherDet->getAttributes();

            $journal = new \app\models\JournalDetails;
            $journalListAttr = $journal->getAttributes();

            $recordList = [];
            $journalList = [];
            $totalCredit = 0;
            $totalDebit = 0;
            echo ($rec['GVNum'] . "\t" . $rec['Name']) . "\n";

            $totalVoucher = 0;
            $query3 = new \yii\db\Query;
            $query3->select('*');
            $query3->from('zold_voucher')->where(['GVNum' => $rec['GVNum']]);
            $payrollRec3 = $query3->all();
            foreach ($payrollRec3 as $key3 => $rec3) {
                $getMember = \app\models\Member::find()->where("CONCAT(last_name, ', ', first_name, ' ', middle_name) = '" . $rec3['Name'] . "'")->one();
                $member_id = null;
                if($getMember){
                    $member_id = $getMember->id;
                    //echo "\t Has Member: \t" . $getMember->last_name . "\n";
                }else{
                    //echo "\t No Member: \t" . $rec3['Name'] . "\n";
                }

                $getParticular = ParticularHelper::getParticular(['name' => $rec3['Description']]);
                $particular_id = $getParticular ? $getParticular->id : null;
                
                $gvArr = $voucherDetAttr;
                $gvArr['member_id'] = $member_id ? $member_id : null;

                if($rec3['Debit']){
                    $Amount = floatval(str_replace(",", "", $rec3['Debit']));
                    $gvArr['debit'] = $particular_id;
                    $totalDebit += $Amount;

                    //Journal
                    $jrArr = $journalListAttr;
                    $jrArr['amount'] = $Amount;
                    $jrArr['entry_type'] = 'DEBIT';
                    $jrArr['particular_id'] = $particular_id;
                    array_push($journalList, $jrArr);
                }

                if($rec3['Credit']){
                    $Amount = floatval(str_replace(",", "", $rec3['Credit']));
                    $gvArr['credit'] = $particular_id;
                    $totalCredit += $Amount;

                    //Journal
                    $jrArr = $journalListAttr;
                    $jrArr['amount'] = $Amount;
                    $jrArr['entry_type'] = 'CREDIT';
                    $jrArr['particular_id'] = $particular_id;
                    array_push($journalList, $jrArr);
                }

                array_push($recordList, $gvArr);
            }
            $totalVoucher = $totalDebit;
            if($totalCredit > $totalDebit){
                $totalVoucher = $totalCredit;
            }
            if($totalDebit != $totalCredit){
                echo "\tVoucher Equal \t" . $totalCredit . "\t" . $totalDebit  . "\n";
            }

            $d = date("Y-m-d", strtotime($rec['DateTransac']));

            $GeneralVoucher = [];
            $GeneralVoucher['gv_num'] = $rec['GVNum'];
            $GeneralVoucher['name'] = $rec['Name'];
            $GeneralVoucher['type'] = 'Individual';
            $GeneralVoucher['date_transact'] = $d;

            $saveGV = VoucherHelper::saveVoucher($GeneralVoucher);
            if($saveGV){
                $success = true;
                $insertSuccess = VoucherHelper::insertEntries($recordList, $saveGV->id);
                if($insertSuccess){
                    $success = true;
                }
                else{
                    $success = false;
                }
            }
            else{
                $success = false;
            }

            if($success){
                //Journal
                $journalHeaderData = array();
                $journalHeaderData['reference_no'] = $rec['GVNum'];
                $journalHeaderData['posting_date'] = $saveGV->date_transact;
                $journalHeaderData['transacted_date'] = $saveGV->date_transact;
                $journalHeaderData['total_amount'] = $totalVoucher;
                $journalHeaderData['trans_type'] = 'GeneralVoucher';
                $journalHeaderData['remarks'] = '';

                $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                if($saveJournal){

                    if($totalCredit > 0){
                        $arr = [];
                        $arr['amount'] = $totalCredit;
                        $arr['particular_id'] = 99;
                        $arr['entry_type'] = "DEBIT";
                        array_push($journalList, $arr);
                    } 

                    if($totalDebit > 0){
                        $arr = [];
                        $arr['amount'] = $totalDebit;
                        $arr['particular_id'] = 99;
                        $arr['entry_type'] = "CREDIT";
                        array_push($journalList, $arr);
                    }

                    $insertSuccess = JournalHelper::insertJournal($journalList, $saveJournal->reference_no);
                    if($insertSuccess){
                        $success = true;
                    }
                    else{
                        $success = false;
                    }
                }
                else{
                    $success = false;
                }

            }

        }
    }

    /*
    To loop additional dates in calendar table
    */
    public function actionLoopCalendar($currYear = null){
        if($currYear == null){
            $currYear = date('Y');
        }
        $getLastCalendar = \app\models\Calendar::find()->orderBy('date_id DESC')->one();
        if($getLastCalendar){
            $calendarDate = $getLastCalendar->date;
            $newDate = date('Y-m-d', strtotime($calendarDate. ' +1 days'));
        }
        $newDate = date('Y-01-01');
        $count = 1;

        //echo date('Y-m-t', strtotime('2019-04-12'));
        for ($i=0; $i < $count; $i++) { 
            $calendar = new \app\models\Calendar;
            $calendar->date = $newDate;
            $calendar->is_current = 0;
            $calendar->is_holiday = 0;
            $calendar->is_month_end = 0;

            //Get if date is month end
            $lastMonthDate = date('Y-m-t', strtotime($newDate));
            if($lastMonthDate == $newDate){
                $calendar->is_month_end = 1;
            }

            if($calendar->save()){
                echo "Date Saved \t" .  $newDate . "\n";
                $newDate = date('Y-m-d', strtotime($newDate. ' +1 days'));
                if(date('Y', strtotime($newDate)) == $currYear){
                    $count++;
                }
                else{
                    break;
                }
            }
        }
    }

    public function actionTimeDepositAccount(){
        $query = new \yii\db\Query;
        $query->from('zold_tdtransac');
        $tdAccs = $query->all();
        foreach ($tdAccs as $key => $td) {
            $findAcc = \app\models\TimeDepositAccount::find()->where(['old_td_account' => $td['TDAcctNum']])->one(); 
            if($findAcc != null){
                continue;
            }

            $model = new \app\models\TimeDepositAccount;

            $product = \app\models\TimeDepositProduct::find()->joinWith(['ratetable'])->where(['tdproduct.id' => 1])->one();
            $trans_serial = $product->trans_serial + 1;
            $trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);

            $amount = floatval(str_replace(",", "", $td['AmountOpen']));
            $balance = floatval(str_replace(",", "", $td['Balance']));
            $amountMature = floatval(str_replace(",", "", $td['AmountMature']));

            if($balance < 0 || $td['Remarks'] == "CLOSED"){
                continue;
            }
            //Get member
            $member_id = null;
            $accountName = null;
            $toSave = false;
            if($td['IDNum'] != '' && $td['SName'] != '' && $td['FName'] != ''){
                $getMember = \app\models\Member::find()->where(['old_db_idnum_zero' => $td['IDNum'], 'last_name' => $td['SName'], 'first_name' => $td['FName']])->one(); 
                if($getMember){
                    $member_id = $getMember->id;
                    $toSave = true;
                }
                else{
                    $text = $td['IDNum'] . "->" . $td['SName'] . " ".  $td['FName'] . " \tName: " . $td['Name'] . " \t" . $td['TDAcctNum'] .  "\n";
                    $getMember = \app\models\Member::find()->where(['last_name' => $td['SName'], 'first_name' => $td['FName']])->one();

                    if($getMember){
                        if ($this->confirm($text . " Save as individual? If no, it will be save as a group.")) {
                            $member_id = $getMember->id;
                        } else {
                            $accountName = $td['Name'];
                        }
                        $toSave = true;
                    }
                    
                }
            }
            else{
                $accountName = $td['Name'];
                $toSave = true;
            }

            if(!$toSave) continue;

            $dateOpen = "";
            if($td['DateOpen'] != "---" && $td['DateOpen'] != "")   {
                /*$dateExplode = explode(" ", $td['DateOpen']);
                $date = explode("/", $dateExplode[0]);
                $dateOpen = date("Y-m-d", strtotime($date[2] . '-' . $date[0] . '-' . $date[1] )); */
                $dateOpen = date("Y-m-d", strtotime($td['DateOpen']));
            }

            $dateMature = "";
            if($td['DateMature'] != "---" && $td['DateMature'] != "")   {
                /*$dateExplode = explode(" ", $td['DateMature']);
                $date = explode("/", $dateExplode[0]);
                $dateMature = date("Y-m-d", strtotime($date[2] . '-' . $date[0] . '-' . $date[1] ));*/
                $dateMature = date("Y-m-d", strtotime($td['DateMature']));          
            }

            //Manually set interest based on old interes
            $interest_rate = 0;
            $service_fee = 0;
            if(intval($td['Terms']) == 6){
                if($amount < 49999){
                   $interest_rate = 4;
                   $service_fee = 15;
                }
            }
            else if(intval($td['Terms']) == 12){
                if($amount < 9999){
                   $interest_rate = 5;
                   $service_fee = 30;
                }
                else if($amount > 10000 && $amount < 29999){
                   $interest_rate = 5.5;
                   $service_fee = 30;
                }
                else if($amount > 30000 && $amount < 49999){
                   $interest_rate = 6;
                   $service_fee = 30;
                }
            }

            if($amount > 50000 && $amount < 99999){
               $interest_rate = 6.5;
               $service_fee = 30;
            }
            else if($amount > 100000 && $amount < 299999){
               $interest_rate = 7;
               $service_fee = 30;
            }
            else if($amount > 300000 && $amount < 499999){
               $interest_rate = 7.5;
               $service_fee = 30;
            }
            else if($amount >= 500000){
               $interest_rate = 8;
               $service_fee = 30;
            }


            $model->accountnumber = $product->id . "-" . $trans_serial_pad;
            $model->fk_td_product = $product->id;
            $model->member_id = $member_id;
            $model->account_name = $accountName;
            $model->account_status = 'ACTIVE';
            $model->term = $td['Terms'];
            $model->amount = $amount;
            $model->balance = $amount;
            $model->open_date = $dateOpen;
            $model->maturity_date = $dateMature;
            $model->amount_mature = 0.00;
            $model->date_created = date('Y-m-d H:i:s', strtotime($dateOpen));
            $model->interest_rate = $interest_rate;


            $model->old_td_account = $td['TDAcctNum'];
            $model->old_idnum = $td['IDNum'];
            $model->old_postingcode = $td['Postingcode'];

            if($model->save()){
                $tdTransaction = new \app\models\TimeDepositTransaction;
                $tdTransaction->fk_account_number = $model->accountnumber;
                $tdTransaction->transaction_type = 'TDCASHDEP';
                $tdTransaction->amount = $model->amount;
                $tdTransaction->balance = $model->balance;
                $tdTransaction->transaction_date = $model->date_created;
                //$tdTransaction->transacted_by = \Yii::$app->user->identity->id;
                $tdTransaction->save();

                $product->trans_serial = $trans_serial;
                $product->save();
            }
            else{
                var_dump($model->getErrors());
            }
        }
    }

    public function actionLoanRedemption(){

        $loanAccount = \app\models\LoanAccount::find()->all();
        foreach ($loanAccount as $key => $accs) {
            //Add redemption to Appliance and Regualr Loan
            if($accs->loan_id == 1 || $accs->loan_id == 2){
                //if($accs->redemption_insurance == null || $accs->redemption_insurance == 0){
                    $getProd = \app\models\LoanProduct::findOne($accs->loan_id);
                    $insurance = ($accs->principal * 0.0120) * ($accs->term / 12);
                    $accs->redemption_insurance = $insurance;
                    if($accs->save()){
                        echo "Account: \t" . $accs->account_no . "\t success \n";
                    }
                    else{
                        echo "Account: \t" . $accs->account_no . "\t not success \n";
                    }
                //}
                
            }
        }
    }

}


?>