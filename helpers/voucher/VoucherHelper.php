<?php

namespace app\helpers\voucher;

use Yii;
use \app\models\GeneralVoucher;
use \app\models\voucherDetails;
use \app\models\LoanAccount;
use \app\models\LoanProduct;
use \app\models\LoanTransaction;


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

                if($row['type'] =='LOAN')
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

                        $loanDetails = array();
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
                        }
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


    public static function getVoucherParticular($filter){
        $voucher_details = VoucherDetails::find()->innerJoinWith(['voucher', 'particular'])
            ->joinWith(['member'])
            ->select([ 'voucher_details.*',
                'general_voucher.name as voucher_name',
                'general_voucher.date_transact'
            ])
            ->where('general_voucher.posted_date IS NOT NULL');

        if(isset($filter['particular_id'])){
            $voucher_details = $voucher_details->andWhere(['voucher_details.particular_id' => $filter['particular_id']]);
        }
        if(isset($filter['member_id'])){
            $voucher_details = $voucher_details->andWhere(['member_id' => $filter['member_id']]);
        }
        if(isset($filter['date'])){
            $start = $filter['date']['start'];
            $end = $filter['date']['end'];
            $voucher_details = $voucher_details->andWhere('general_voucher.date_transact BETWEEN "'.$start.'" AND "'.$end.'"');
        }

        $voucher_details = $voucher_details->orderBy('general_voucher.posted_date')
            ->asArray()->all();
        
        return $voucher_details;
    }

    public static function printList($postData){
        $member = $postData['member'];
        $particular = $postData['particular'];
        $period = $postData['period']['start'] . " To " .$postData['period']['end'] ;
        $total_amount = $postData['total_amount'];
        $transaction = $postData['transaction'];

        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">PARTICULAR Disbursement</div></tr>
        </table>';

        $accountDetail = '<table>';

        if($member){
            $accountDetail .= '
            <tr>
                <td style = "font-weight: bold;">NAME: </td> 
                <td><span>[member]</span></td>
            </tr>';
        }
        $accountDetail .= '
            <tr>
                <td style = "font-weight: bold;">PARTICULAR: </td> 
                <td><span>[particular]</span></td>
            </tr> 
            <tr>
                <td style = "font-weight: bold;">PERIOD: </td> 
                <td>[period] </td>
            </tr> 
        </table>';

        $accountDetail .= '<table class = "mt-10">
            <tr>
                <td style = "font-weight: bold;">TOTAL DEBIT: </td> 
                <td>[total_debit]</td>
                <td width = "50px"></td>
                <td style = "font-weight: bold;">TOTAL CREDITS: </td> 
                <td>[total_credit]</td>
            </tr>
        </table>';


        $accountDetail = str_replace('[member]', $member, $accountDetail);
        $accountDetail = str_replace('[particular]', $particular, $accountDetail);
        $accountDetail = str_replace('[period]', $period, $accountDetail);
        $accountDetail = str_replace('[total_credit]', Yii::$app->view->formatNumber($total_amount['credit']), $accountDetail);
        $accountDetail = str_replace('[total_debit]', Yii::$app->view->formatNumber($total_amount['debit']), $accountDetail);

        $listTemplate .= $accountDetail;

        if(count($transaction) > 0){
            $transTable = '<table class = "list-table table table-bordered mt-10" width = "100%">
                <tr>';
            if(!$member){
                $transTable .= '<th>Name</th>';
            }
            $transTable .= '<th>Particular</th> 
                    <th>OR Number</th> 
                    <th>Date</th> 
                    <th>Debit</th> 
                    <th>Credit</th> 
                </tr>';
            foreach ($transaction as $trans) {
                $fullname = $trans['member'] ? $trans['member']['fullname'] : $trans['voucher_name'];
                $particular = $trans['particular'] ? $trans['particular']['name'] : "";
                $transDate = date('Y-m-d', strtotime($trans['date_transact']));
                $credit = isset($trans['credit']) && floatval($trans['credit']) > 0 ? Yii::$app->view->formatNumber($trans['credit']) : "";
                $debit = isset($trans['debit']) && floatval($trans['debit']) > 0 ? Yii::$app->view->formatNumber($trans['debit']) : "";
                $transTable .= '<tr>';

                if(!$member){
                    $transTable .= '<td>'.$fullname.'</td>';
                }

                $transTable .=' <td>'.$particular.'</td> 
                    <td>'.$trans['gv_num'].'</td> 
                    <td>'.$transDate.'</td> 
                    <td>'.$debit.'</td> 
                    <td>'.$credit.'</td> 
                </tr>';
            }

            $transTable .= '</table>';
        }
        $listTemplate = $listTemplate . $transTable;

        return $listTemplate;
    }

    //For now this function is for Loan Release only with no other loan payments
    public static function unpostVoucher($ref_id)
    {
        //echo 'wow';
        try {
            
            $success = true;
            $transaction = \Yii::$app->db->beginTransaction();
            
            $dateTimeToday = \Yii::$app->user->identity->DateTimeNow;
            $dateToday = date('Y-m-d', strtotime($dateTimeToday));
            $generalVoucher = GeneralVoucher::find()->where(['id'=>$ref_id])
                ->andWhere('cancelled_date IS NULL')
                ->andWhere('posted_date IS NOT NULL')->one();
            
            if(!$generalVoucher){
                echo "Voucher with GV Number maybe already cancelled or not found";
                echo "<h3> Please close the window </h3>";
                die;
            }
            $gv_num = $generalVoucher->gv_num;

            //Check if has other loan payment
            $loanPayment = LoanTransaction::find()->where(['OR_no' =>$gv_num, 'transaction_type' => 'PAYPARTIAL'])->count();
            if($loanPayment > 0){
                echo "Vouher has loan payments. This facility is for Loan Release only.";
                echo "<h3> Please close the window </h3>";
                die;
            }

            //Get Loan Transaction
            $loanRelease = LoanTransaction::find()->where(['OR_no' => $gv_num, 'transaction_type' => 'RELEASE'])->one();
            if($loanRelease){
                $cancelLoanRelease = LoanHelper::cancelLoanRelease($loanRelease->loan_account);
                if(!$cancelLoanRelease['success']){
                    if(isset($cancelLoanRelease['error']) == 'release_has_payment'){
                       echo "Loan account has already payment. Cancelled account must have no other transaction.";
                        echo "<h3> Please close the window </h3>";
                        die; 
                    }
                    $success = true;
                }
            }
            else{
                echo "Loan Release not found. This facility is for Loan Release only.";
                echo "<h3> Please close the window </h3>";
                die; 
            }

            if($success){

                //Get Share Transaction
                $cancelShare = ShareHelper::cancelReference($gv_num, $dateTimeToday);
                if(!$cancelShare['success']){
                    echo "Error on cancelling share transaction.";
                    echo "<h3> Please close the window </h3>";
                    die;
                }

                //Cancel Savings Transaction
                $cancelSavings = SavingsHelper::cancelReference($gv_num, $dateTimeToday);
                if(!$cancelSavings['success']){
                    echo "Error on cancelling savings transaction.";
                    echo "<h3> Please close the window </h3>";
                    die;
                    $success = false;
                }
            }


            if($success){
                $generalVoucher->cancelled_date = $dateTimeToday;
                $generalVoucher->save();

                $transaction->commit();
                //$transaction->rollBack(); // Rollback for now
                echo "<br/><h3>Cancelled</h3>";
            }
            else {
                $transaction->rollBack();
                echo "<br/><h3>Not Cancelled. Please contact admin or the developer</h3>";
            }

            echo "<br/><h3>Close Window.</h3>";
            
            
        } catch (\Exception $e) {
            var_dump($e);
            echo $e->getMessage();
        }
    }
}