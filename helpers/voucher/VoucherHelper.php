<?php

namespace app\helpers\voucher;

use Yii;
use \app\models\GeneralVoucher;
use \app\models\voucherDetails;
use \app\models\LoanAccount;
use \app\models\LoanProduct;

use app\helpers\journal\JournalHelper;
use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;
class VoucherHelper 
{
    public static function saveVoucher($data){
        $voucher = new GeneralVoucher;
        
        if(isset($data['id'])){
            $voucher = GeneralVoucher::findOne($data['id']);
        }

        $voucher->gv_num = $data['gv_num'];
        $voucher->name = $data['name'];
        $voucher->type = $data['type'];
        $voucher->date_transact = $data['date_transact'];
        $voucher->created_date = date('Y-m-d H:i:s');;
        $voucher->created_by = isset(\Yii::$app->user) && isset(\Yii::$app->user->identity) ? \Yii::$app->user->identity->id : 18;

        if(isset($data['posted_date'])){
            $voucher->posted_date = $data['posted_date'];
        }

        if(isset($value['is_prepaid'])){
            $voucher->is_prepaid = $value['is_prepaid'] === 1|| $value['is_prepaid'] === "1" || $value['is_prepaid'] === true || $value['is_prepaid'] === "true" ? 1 : 0;
        }

        if($voucher->save()){
            return $voucher;
        }
        /*else{
            var_dump($voucher->getErrors());
        }*/
        return null;
    }

	public static function insertEntries($list, $voucher_id, $posted_date = null){
        $success = true;
        $voucherModel = GeneralVoucher::findOne($voucher_id);
        foreach ($list as $key => $value) {
            $voucher = new VoucherDetails;
            $voucher->voucher_id = $voucher_id;
            $voucher->member_id = $value['member_id'];
            $voucher->particular_id = $value['particular_id'];
            $voucher->debit = $value['debit'];
            $voucher->credit = $value['credit'];
            $voucher->account_no = isset($value['account_no']) ? $value['account_no'] : null;
            $voucher->type = isset($value['type']) ? $value['type'] : null;
            $voucher->gv_num = isset($value['gv_num']) ? $value['gv_num'] :  $voucherModel ? $voucherModel->gv_num : null;

            if($posted_date){
                $voucher->posted_date = $posted_date;
            }

            if(!$voucher->save()){
                $success = false;
            }
        }
        return $success;
	}

    public static function saveJournalVoucherBase($voucher_id){

        $voucher = GeneralVoucher::findOne($voucher_id);
        if($voucher){
            $journalHeader = new \app\models\JournalHeader;
            $journalHeaderData = $journalHeader->getAttributes();
            $journalHeaderData['reference_no'] = $voucher->gv_num;
            $journalHeaderData['posting_date'] = $voucher->date_transact;
            $journalHeaderData['total_amount'] = 0;
            $journalHeaderData['trans_type'] = 'GeneralVoucher';
            $journalHeaderData['remarks'] = '';

            $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);

            if($saveJournal){
                $voucherList = VoucherDetails::find()->where(['voucher_id' => $voucher_id])->asArray()->all();
                if($voucherList && count($voucherList) > 0){
                    //Entries

                    $journalList = new \app\models\JournalDetails;
                    $journalListAttr = $journalList->getAttributes();
                    $lists = array();
                    $totalAmount = 0;
                    $totalCredit = 0;
                    $totalDebit = 0;
                    foreach ($voucherList as $acct) {
                        if($acct['debit'] && (float)$acct['debit'] > 0){
                            $arr = $journalListAttr;
                            $arr['amount'] = $acct['debit'];
                            $arr['particular_id'] = $acct['particular_id'];
                            $arr['entry_type'] = "DEBIT";
                            array_push($lists, $arr);

                            $totalAmount += $acct['debit'];
                        }

                        if($acct['credit'] && (float)$acct['credit'] > 0){
                            $arr = $journalListAttr;
                            $arr['amount'] = $acct['credit'];
                            $arr['particular_id'] = $acct['particular_id'];
                            $arr['entry_type'] = "CREDIT";
                            array_push($lists, $arr);
                        }
                        
                    }

                    $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->reference_no);
                    if($insertSuccess){
                        $saveJournal->total_amount = $totalAmount;
                        $saveJournal->save();
                        
                        $success = true;
                        return $success;
                    }
                    else{
                        $success = false;
                    }

                }
                
            }
            else{
                $success = false;
            }
        }
        
        $success = false;

        return $success;
    }

    public static function getVoucherList($filter = null, $limit = null){

        $voucherList = \app\models\VoucherDetails::find()->joinWith(['particular']);

        if($filter &&is_array($filter)){
            $where = [];
            foreach ($filter as $key => $value) {
                $where[$key] = $value;
            }

            if(count($where) > 0){
                $voucherList = $voucherList->where($where);
            }
        }

        if($limit){
            $voucherList = $voucherList->limit(100);
        }

        $voucherList = $voucherList->orderBy('id DESC')->asArray()->all();

        return $voucherList;

    }

    public static function getList($voucher_id){
        $accountList = \app\models\VoucherDetails::find()->joinWith(['member', 'particular']);
        if($voucher_id != null){
            $accountList = $accountList->where(['voucher_id' => $voucher_id]);
        }
        
        return $accountList->asArray()->all();
    }

    public static function getVoucherByGvNum($gv_num){

        $voucher= GeneralVoucher::find()->where(['gv_num' => $gv_num])->one();
        if($voucher){
            return $voucher;
        }
        return null;
    }

    public static function getPrepaid($list, $voucher){
        $getPrepaid = null;

        $getVoucherList = array_filter( $list,
            function ($e) use ($voucher) {
                return intval($e->is_prepaid) === 1 && $e->account_no == $voucher['account_no'];
            }
        );
        

        if(count($getVoucherList) > 0){
            foreach ($getVoucherList as $pyt) {
                if($pyt->account_no == $voucher['account_no']){
                    $getPrepaid = $pyt;
                }
            }
        }

        return $getPrepaid;
        
    }

    public static function postVoucher($ref_id){
        try {

            
            $success = true;
            $transaction = \Yii::$app->db->beginTransaction();
            
            $dateToday = date('Y-m-d', strtotime(\Yii::$app->user->identity->DateTimeNow));
            $voucherList = VoucherDetails::find()->joinWith(['member'])->where(['voucher_id'=>$ref_id])->all();
            $generalVoucher = GeneralVoucher::findOne(['id'=>$ref_id]);

            
            //2. prepare header for accounting entry
            $posted_date = $dateToday;
            
            if($generalVoucher->posted_date){
                echo "General VOucher Number " . $generalVoucher->gv_num . ' for ' . $generalVoucher->name . ' is already posted.<br/>';
                echo "<h3> Please close the window </h3>";
                die;
            }
            
            $ref_num = $generalVoucher->gv_num;
            $journaldetails = [];
            
            foreach ($voucherList as $row)
            {
                $memberName = $row['member'] ? $row['member']['fullname'] : "";
                
                echo "Posting " . $row['type'].' for ' . $memberName . ' ......<br/>';

                if($row['type']=='LOAN')
                {
                    //IF CREDIT, THIS WILL BE LOAN PAYMENT
                    if($row['credit']  && floatval($row['credit'] ) > 0){

                        //If prepaid paid skip as it will be included on adding the loan
                        if(intval($row['is_prepaid']) === 1){
                            continue;
                        }

                        $account = LoanAccount::findOne($row['account_no']);
                        $product = LoanProduct::findOne($account['loan_id']);

                        $isNewLoanPolicy = false;
                        //New policy was updates. Eg. No prepaid monthly for Applicance and interest earned calculcation
                        $calVersion = Yii::$app->view->getVersion($account['release_date']);
                        if($calVersion !== "1"){
                            $isNewLoanPolicy = true;
                        }
                        
                        $prepaidInterest = 0;
                        $amount = $row['credit'];

                        if($product->id == 1 || ($product->id == 2 && !$isNewLoanPolicy)) //No quiencena prepaid for appliance loan 
                        {
                            //Get prepaid from the payment
                            $getPrepaid = static::getPrepaid($voucherList, $row);
                            if($getPrepaid){
                                $prepaidInterest = $getPrepaid['credit'] < 0 ? 0 : $getPrepaid['credit'];
                                $amount += $prepaidInterest;
                            }
                            
                            
                        }
                        echo "i am interest prepaid .. ".$prepaidInterest." | <br/>";
                        $principal_pay = $row['credit']/* - $prepaidInterest*/;

                        /*$loanDetails = array();
                        $loanDetails['principal_pay'] = $row['credit'];
                        $loanDetails['prepaid_pay'] = $prepaidInterest;
                        $loanDetails['ref_num'] = $generalVoucher->gv_num;
                        $loanDetails['product_id'] = $account['loan_id'];
                        $loanDetails['transaction_date'] = $dateToday;
                        $loanPayment = LoanHelper::loanPayment($row['account_no'], $loanDetails);
                        
                        if(!$loanPayment['success']){
                            var_dump($loanPayment['error']);
                            $success = false;
                            break;
                        }*/
                    }

                    //IF DEBIT, This mostly for refund
                    if($row['debit']  && floatval($row['debit'] ) > 0){

                        echo "refund amount .. ".$row['debit']." | <br/>";
                        $loanDetails = array();
                        $loanDetails['amount'] = $row['debit'];
                        $loanDetails['ref_num'] = $generalVoucher->gv_num;
                        $loanDetails['transaction_date'] = $dateToday;
                        $loanRefund = LoanHelper::loanRefund($row['account_no'], $loanDetails);
                        
                        if(!$loanRefund['success']){
                            var_dump($loanRefund['error']);
                            $success = false;
                            break;
                        }
                    }
                    
                    
                }
                
                
                else if($row['type']=='SAVINGS')
                {
                    //IF CREDIT, THIS WILL BE SAVINGS DEPOSIT
                    if($row['credit']  && floatval($row['credit'] ) > 0){
                        $savingsDetails = array();
                        $savingsDetails['account_no'] = $row['account_no'];
                        $savingsDetails['remarks'] = "Posted as deposit from ". $ref_num;
                        $savingsDetails['amount'] = floatval($row['credit'] );
                        $savingsDetails['ref_num'] = $ref_num;
                        $savingsDetails['transaction_date'] = $dateToday;
                        $savingsDetails['transaction_type'] = 'CASHDEP';

                        $depositSavings = SavingsHelper::transactionSavings($savingsDetails);
                        if(!$depositSavings['success']){
                            $success = false;
                            break;
                        }
                    }
                    
                    //IF DEBIT, THIS WILL BE SAVINGS WITHDRAW
                    if($row['debit']  && floatval($row['debit'] ) > 0){
                        $savingsDetails = array();
                        $savingsDetails['account_no'] = $row['account_no'];
                        $savingsDetails['remarks'] = "Posted as withdrawal from ". $ref_num;
                        $savingsDetails['amount'] = floatval($row['debit'] );
                        $savingsDetails['ref_num'] = $ref_num;
                        $savingsDetails['transaction_date'] = $dateToday;
                        $savingsDetails['transaction_type'] = 'WITHDRWL';

                        $depositSavings = SavingsHelper::transactionSavings($savingsDetails);
                        if(!$depositSavings['success']){
                            $success = false;
                            break;
                        }
                    }
                }

                else if($row['type']=='SHARE')
                {
                    //IF CREDIT, THIS WILL BE SHARE DEPOSIT
                    if($row['credit']  && floatval($row['credit'] ) > 0){
                        $shareDetails = array();
                        $shareDetails['account_no'] = $row['account_no'];
                        $shareDetails['remarks'] = "Posted as deposit from ". $ref_num;
                        $shareDetails['amount'] = floatval($row['credit'] );
                        $shareDetails['ref_num'] = $ref_num;
                        $shareDetails['transaction_date'] = $dateToday;
                        $shareDetails['transaction_type'] = "CASHDEP";

                        $depositShare = ShareHelper::transactionShare($shareDetails);
                        if(!$depositShare['success']){
                            $success = false;
                            break;
                        }
                    }  

                    //IF DEBIT, THIS WILL BE SHARE WITHDRAW
                    if($row['debit']  && floatval($row['debit'] ) > 0){
                        $shareDetails = array();
                        $shareDetails['account_no'] = $row['account_no'];
                        $shareDetails['remarks'] = "Posted as withdrawal from ". $ref_num;
                        $shareDetails['amount'] = floatval($row['debit'] );
                        $shareDetails['ref_num'] = $ref_num;
                        $shareDetails['transaction_date'] = $dateToday;
                        $shareDetails['transaction_type'] = "WITHDRWL";

                        $depositShare = ShareHelper::transactionShare($shareDetails);
                        if(!$depositShare['success']){
                            $success = false;
                            break;
                        }
                    }
                }
            }

            if($success){
                $generalVoucher->posted_date = $posted_date;
                $generalVoucher->save();

                $transaction->commit();
                //$transaction->rollBack(); // Rollback for now
                echo "<br/><h3>Saved</h3>";
            }
            
            else {
                $transaction->rollBack();
                echo "<br/><h3>Unsaved. Please contact admin or the developer</h3>";
            }

            echo "<br/><h3>Close Window.</h3>";
            
            
        } catch (\Exception $e) {
            var_dump($e);
            echo $e->getMessage();
        }
    }


}