<?php

namespace app\controllers;

use Yii;

use yii\helpers\FileHelper;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory;

use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;
use app\helpers\accounts\TimeDepositHelper;
use app\helpers\payment\PaymentHelper;
use app\helpers\member\MemberHelper;

use \app\models\LoanProduct;
use \app\models\LoanAccount;
use \app\models\PaymentRecord;
use \app\models\PaymentRecordList;


class PaymentController extends \yii\web\Controller
{
    public $entryType = array();

    public function __construct($id, $module, $config = [])
    {
        $paymentHelper = new PaymentHelper;
        $entryType = $paymentHelper->entry_type;

        parent::__construct($id, $module, $config);
    } 


    public function actionIndex($id = null)
    {
        $this->layout = 'main-vue';

        if($id != null){
            $paymentModel = \app\models\PaymentRecord::find()->where(['id' => $id])->asArray()->one();

        }
        else{
            $paymentModel = new \app\models\PaymentRecord;
            $paymentModel = $paymentModel->getAttributes();

            $paymentModelList = new \app\models\PaymentRecordList;
            $paymentModelList = $paymentModelList->getAttributes();
        }

        $filter  = ['category' => ['OTHERS']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);


        return $this->render('index', [
            'model'             => $paymentModel,
            'paymentModelList'  => $paymentModelList,
            'particularList'    => $getParticular
        ]);
    }

    public function actionIndex2($id = null)
    {
        $this->layout = 'main-vue';

        if($id != null){
            $paymentModel = \app\models\PaymentRecord::find()->where(['id' => $id]);
        }
        else{
            $paymentModel = new \app\models\PaymentRecord;
            $paymentModel = $paymentModel->getAttributes();

            $paymentModelList = new \app\models\PaymentRecordList;
            $paymentModelList = $paymentModelList->getAttributes();
        }

        $filter  = ['category' => ['OTHERS']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);


        return $this->render('index', [
            'model'             => $paymentModel,
            'paymentModelList'  => $paymentModelList,
            'particularList'    => $getParticular
        ]);
    }

    public function actionList()
    {
        $this->layout = 'main-vue';
        
        return $this->render('list');
    }
    
    
    public function actionPostPayment($id)
    {
    	PaymentHelper::postPayment($id);
    }

    public function actionTest(){

        $ref_id = '3';
        $account_no = '2-000483';
        $paymentHeader = PaymentRecord::findOne(['id'=>$ref_id]);
        $payments = PaymentRecordList::findAll(['payment_record_id'=>$ref_id]);
        //var_dump($payments);

        $neededObject = array_filter(
            $payments,
            function ($e) use (&$account_no) {
                return $e->is_prepaid === 1 && $e->account_no == $account_no;
            }
        );
        $getPrepaid = null;
        if($neededObject > 0){
            foreach ($neededObject as $pyt) {
                if($pyt->account_no == $account_no){
                    $getPrepaid = $pyt;
                }
            }
        }
        //var_dump($neededObject->amount);
        var_dump($getPrepaid);
        $prouct_id = 1;
        /*$account_no = '1-000262';
        $product = LoanProduct::findOne($prouct_id);
        $account = LoanAccount::findOne($account_no);

        $connection = Yii::$app->getDb();

        $command = $connection->createCommand("
                    select ifnull((select date_posted FROM `loan_transaction` where loan_account=:accountnumber and left(transaction_type, 3)='PAY' AND is_cancelled=0 order by date_posted desc limit 1), (SELECT release_date FROM `loanaccount` where account_no=:accountnumber limit 1)) as lasttrandate", [':accountnumber' => $account_no]);
        $lastTransaction = $command->queryOne();
        
        echo $lastTransaction['lasttrandate']." | <br/>-";
        $noOfDaysPassed = date_diff(date_create(date('Y-m-d')), date_create($lastTransaction['lasttrandate']));
        
        $noOfDaysPassed = $noOfDaysPassed->format("%a");
        var_dump($noOfDaysPassed);

        $command = $connection->createCommand("SELECT ifnull((SELECT sum(prepaid_intpaid) FROM `loan_transaction` where
            loan_account=:accountnumber and left(transaction_type,3)='PAY'), 0) - ifnull((SELECT sum(prepaid_intpaid) FROM `loan_transaction` where
            loan_account=:accountnumber and left(transaction_type,2)='CN'), 0) AS totalPrepaidPaid", [':accountnumber' => $account_no]);
        $totalPrepaidPaid = $command->queryOne();
        var_dump($totalPrepaidPaid);
        var_dump($account->prepaid_amortization_quincena);
        $PIMustPay = ($noOfDaysPassed / 15) * $account->prepaid_amortization_quincena;
        echo 'PIMustPay: ' . $PIMustPay . "<br>";;

        $accumulatedPrepaid = $PIMustPay - $totalPrepaidPaid['totalPrepaidPaid'];
        $prepaidInterest= $accumulatedPrepaid < 0 ? 0 : $accumulatedPrepaid;

        echo 'accumulatedPrepaid: ' . $accumulatedPrepaid . "<br>";
        echo 'prepaidInterest: ' . $prepaidInterest . "<br>";;*/
    }

    public function actionSavePaymentList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = "begin me";
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();
                $paymentModel = $post['paymentModel'];
                $allAccounts = $post['allAccounts'];

                //Check GV Number if exist
                $or_num = $paymentModel['or_num'];
                $getOR = \app\models\JournalHeader::find()->where(['reference_no' => $or_num])->one();
                if($getOR){
                    return [
                        'success'   => "mesry",
                        'error'     => 'ERROR_HASOR'
                    ];
                }
                else{

                    //Save Loan payment here


                    //After loan transaction is saved, Save General Voucher and Entries
                    if($success){
                        //Save gv and entries
                        $saveOR = PaymentHelper::savePayment($paymentModel);
                        if($saveOR){
                            //Entries
                            $insertSuccess = PaymentHelper::insertAccount($allAccounts, $saveOR->id);
                            if($insertSuccess){
                                $success = true;
                            }
                            else{
                            	$success = $insertSuccess;
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $transaction->rollBack();
                        }

                    }

                    //Save in journal
                    /*if($success && $saveOR){
                        $journalHeader = new \app\models\JournalHeader;
                        $journalHeaderData = $journalHeader->getAttributes();
                        $journalHeaderData['reference_no'] = $saveOR->or_num;
                        $journalHeaderData['posting_date'] = $saveOR->date_transact;
                        $journalHeaderData['total_amount'] = 0;
                        $journalHeaderData['trans_type'] = 'Payment';
                        $journalHeaderData['remarks'] = '';

                        $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                        if($saveJournal){
                            //Entries

                            $journalList = new \app\models\JournalDetails;
                            $journalListAttr = $journalList->getAttributes();
                            $lists = array();
                            $totalAmount = 0;
                            $totalCredit = 0;
                            $totalDebit = 0;
                            foreach ($allAccounts as $acct) {
                                $arr = $journalListAttr;
                                $arr['amount'] = $acct['amount'];
                                $arr['particular_id'] = $acct['particular_id'];
                                $arr['entry_type'] = $entryType[$acct['type']];

                                if($arr['entry_type'] == 'CREDIT'){
                                    $totalCredit += $acct['amount'];
                                }
                                else if($arr['entry_type'] == 'CREDIT'){
                                    $totalDebit += $acct['amount'];
                                }

                                $totalAmount += $acct['amount'];
                                array_push($lists, $arr);
                            }

                            // Set total credit and total debit as Cash On Hand
                            $coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);
                            if($totalCredit > 0){
                                $arr = $journalListAttr;
                                $arr['amount'] = $totalCredit;
                                $arr['particular_id'] = $coh_id->id;
                                $arr['entry_type'] = "DEBIT";
                                array_push($lists, $arr);
                            } 

                            if($totalDebit > 0){
                                $arr = $journalListAttr;
                                $arr['amount'] = $totalDebit;
                                $arr['particular_id'] = $coh_id->id;
                                $arr['entry_type'] = "CREDIT";
                                array_push($lists, $arr);
                            } 

                            $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->id);
                            if($insertSuccess){
                                $saveJournal->total_amount = $totalAmount;
                                $saveJournal->save();
                                
                                $success = true;
                            }
                            else{
                                $success = false;
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $transaction->rollBack();
                        }
                    } */
                }

                if($success){
                    $transaction->commit();
                }
                
                else
                	$transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $error =  $e->getMessage();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                $error = $e->getMessage();
            }

            return [
                'success'   => $success,
                'data'      => $data,
                'error'     => $error
            ];
        }
    }

    public function actionPayroll(){
        $this->layout = 'main-vue';

        $paymentModel = new \app\models\PaymentRecord;
        $paymentModel = $paymentModel->attributes();

        $pytPayroll = new \app\models\PaymentPayroll;
        $pytPayrollModel = $pytPayroll->attributes();

        $pytPayrollList = new \app\models\PaymentPayrollList;
        $pytPayrollListModel = $pytPayrollList->attributes();

        $getParticular = ParticularHelper::getPayrollParticulars();

        $members = \app\models\Member::find()->all();

        return $this->render('payroll-payment', [
            'paymentModel'          => $paymentModel,
            'pytPayrollModel'       => $pytPayrollModel,
            'pytPayrollListModel'   => $pytPayrollListModel,
            'memberList'            => $members
        ]);
    }

    public function actionGetPaymentRecord(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $paymentRecord  = \app\models\PaymentRecord::find()->joinWith(['particular'])->asArray()->all();

            return [
                'data' => $paymentRecord,
            ];
        }
    }

    public function actionGetMemberAccounts(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $id = $post['member_id'];

            $filter = ['member_id' => $id];

            $getSavings = SavingsHelper::getAccountSavingsInfo($filter);
            $getShare = ShareHelper::getAccountShareInfo($filter);
            $getLoan = LoanHelper::getAccountLoanInfo($id);
        
            return [
                'savingsAccounts'        => $getSavings,
                'shareAccounts'          => $getShare,
                'loanAccounts'           => $getLoan
            ];
            
        }

    }

    public function actionGetPaymentDetails(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $paymentRecord  = \app\models\PaymentRecord::find()
                ->where(['or_num' => $post['or_num']])->asArray()->one();
            $paymentList = [];
            $success = false;
            if($paymentRecord != null){
                $paymentList = PaymentHelper::getPaymentList($paymentRecord['id']);
                foreach ($paymentList as $key => $list) {
                    $getProduct = [];
                    if($list['type'] == 'OTHERS'){
                        $getProduct = PaymentHelper::getPaymentProduct($list['type'], $list['particular_id']);
                    }
                    else{
                        $getProduct = PaymentHelper::getPaymentProduct($list['type'], $list['product_id']);
                    }

                    $paymentList[$key]['productData'] = $getProduct;
                    $paymentList[$key]['fullname'] = $list['member']['fullname'];
                }
                $success = true;
            }

            return [
                'success'   => $success,
                'paymentRecord' => $paymentRecord,
                'paymentList' => $paymentList,
            ];
        }
    }

    public function actionImportPayment(){
        $this->layout = 'main-vue';
        $paymentModel = new \app\models\PaymentRecord;
        $paymentModel = $paymentModel->getAttributes();

        $getParticular = ParticularHelper::getPayrollParticulars('name');
        $pageData = [
            'dataParticulars' => $getParticular,
            'dataModel' => $paymentModel
        ];

        return $this->render('import-payment', [
            'pageData'          => $pageData
        ]);
    }

    public function actionTest2(){
        $firstAscii = ord("A");
        var_dump($firstAscii);
        $count = intdiv(52, 26);
        var_dump($count);
    }

    public function actionSetImportedPayment(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $file = \yii\web\UploadedFile::getInstanceByName('file');
        $path = 'uploads/import/';

        $user_id = Yii::$app->user->identity->id;
        $dateInInt = strtotime(Yii::$app->view->current_date());
        $dateNow = date('Y_m_d_H_i_s', $dateInInt);

        $fileName = "payroll"."_".$user_id."_".$dateNow."_".$dateInInt.'.'.$file->extension;

        $columnHeader = [];
        $particularItem = [];
        $dataList = [];
        $possibleColCount = 30;

        if (FileHelper::createDirectory($path, $mode = 0775, $recursive = true)) {
            $file->saveAs($path.$fileName);

            $reader = IOFactory::createReader(ucfirst($file->extension));
            $spreadsheet = $reader->load($path.$fileName);
            $worksheet = $spreadsheet->getSheet(0);

            foreach ($worksheet->getRowIterator() as $rowKey => $row) {
                if($rowKey == 1){ continue; }

                if($rowKey == 2){
                    //Get account
                    $count = 1;
                    $col = Yii::$app->view->convertIntToExcelColumn($count);

                    for($cnt = 0; $cnt < $possibleColCount; $cnt++) {
                        $cellVal = $worksheet->getCell($col.$rowKey)->getformattedValue();
                        if($cellVal == ""){
                            break;
                        };

                        $cellArr = array();
                        $cellArr['value'] = $cellVal;
                        $cellArr['hasParticular'] = false;

                        //0 : Name, 1 : ID Num
                        if($cnt >= 2){ 
                            $where = ['name' => $cellVal];
                            $getParticular = ParticularHelper::getParticular($where, true);
                            if($getParticular){
                                $cellArr['hasParticular'] = true;
                                $cellArr['particular_id'] = $getParticular['id'];

                                $arr = array();
                                $arr['column_label'] = $cellVal;
                                $arr['column_prop'] = 'col_' . $cnt;
                                $arr = array_merge($arr, $getParticular);
                                $arr['particular_id'] = $getParticular['id'];
                                $arr['is_prepaid'] = false;
                                $arr['column_prop'] = 'particular_' . $getParticular['id'];
                                if($getParticular['category'] == 'LOAN'){
                                    $whereLoan = ['particular_id' => $getParticular['id']];
                                    if (strpos($getParticular['name'], 'PI on') !== false) {
                                        $whereLoan = ['pi_particular_id' => $getParticular['id']];
                                        $arr['is_prepaid'] = true;
                                    }
                                    $getProduct = LoanHelper::getProduct($whereLoan, true);
                                    if($getProduct){
                                        $arr['loanProduct'] = $getProduct;
                                    }

                                }
                                array_push($particularItem, $arr); 
                            }
                        }

                        array_push($columnHeader, $cellArr);  

                        $count++;
                        $col = Yii::$app->view->convertIntToExcelColumn($count); 

                    }

                }
                else{
                    $rowData = array();
                    $originalData = array();
                    $memberData = null;
                    $memberToPay = [];
                    $toAddAsSavings = 0;
                    for($cnt = 0; $cnt < count($columnHeader); $cnt++) {
                        $columnData = $columnHeader[$cnt];

                        $col = Yii::$app->view->convertIntToExcelColumn($cnt+1);
                        $cellVal = $worksheet->getCell($col.$rowKey)->getformattedValue();
                        array_push($originalData, $cellVal); 

                        //get member data using column 1 : ID Num
                        // 0 : Name
                        if($cnt == 1){
                            $where = ['id' => $cellVal];
                            $getMember = MemberHelper::getMember($where, true);
                            if($getMember){
                                $memberData = $getMember;
                            }
                        }
                        //Amounts
                        else if($cnt > 1 && $memberData){
                            if(!$columnData['hasParticular']){
                                continue;
                            }
                            $setParticular = array_filter( $particularItem,
                                function ($e) use (&$columnData){
                                    return $e['particular_id'] == $columnData['particular_id'];
                                }
                            );
                            $getParticular = null;
                            if(count($setParticular) > 0){
                                foreach ($setParticular as $key => $value) {
                                    $getParticular = $value;
                                    break;
                                }
                            }
                            if($getParticular){

                                $arr = array();
                                $arr['product_id'] = $getParticular['product_id'];
                                $arr['category'] = $getParticular['category'];
                                $arr['particular_id'] = $getParticular['id'];
                                $arr['is_prepaid'] = $getParticular['is_prepaid'];
                                $arr['account_id'] = null;
                                $amount = str_replace(" ", "", $cellVal);
                                $amount = str_replace(",", "", $cellVal);
                                $amount = floatval($amount);
                                if($getParticular['category'] == 'LOAN'){
                                    $arr['amount'] = $amount;
                                    $toSavings = 0;

                                    
                                    //get loan of the member
                                    $loanAccount = LoanHelper::getMemberLoan($memberData['id'], $getParticular['loanProduct']['id'] );
                                    if($loanAccount){
                                        $arr['accountDetails'] = $loanAccount;
                                        $arr['account_id'] = $loanAccount['account_no'];
                                        if($loanAccount['principal_balance'] > 0){
                                            if($amount > $loanAccount['principal_balance']){
                                                $arr['amount'] = $amount;
                                                $arr['error_status']  = 'balance_negative';
                                                //$arr['amount'] = $loanAccount['principal_balance'];
                                                //$toSavings = $amount - $loanAccount['principal_balance'];
                                            }
                                            else{
                                                $arr['amount'] = $amount;
                                            }
                                        }
                                        else{
                                            //$arr['amount'] = 0;
                                            $arr['amount'] = $amount;
                                            $arr['error_status']  = 'balance_zero';
                                            //$toSavings = $amount;
                                        }
                                        //GetArrear
                                        $getArrears = LoanHelper::getArrears($loanAccount['account_no']);
                                        if($getArrears['arrearAmount'] > 0){
                                            $arr['arrears'] = $getArrears['arrearAmount'];
                                        }
                                        //Need to think if I will process it here or in the vue files
                                    }
                                    //If no account then no loan to pay. Add in savings account
                                    else if($amount > 0){
                                        $arr['amount']  = 0;
                                        $arr['error_status']  = 'no_loan_account';
                                        $toSavings = $amount;
                                    }

                                    $toAddAsSavings += $toSavings;
                                }
                                else if($getParticular['category'] == 'SAVINGS'){
                                    //get the savings account of the member
                                    $getAccount = SavingsHelper::getMemberSavings($memberData['id']);
                                    if($getAccount){
                                        $arr['accountDetails'] = $getAccount;
                                        $arr['account_id'] = $getAccount['account_no'];
                                        $arr['amount'] = $amount;
                                    }

                                }
                                else if($getParticular['category'] == 'SHARE'){
                                    //get the savings account of the member
                                    $getAccount = ShareHelper::getAccountShareInfo($memberData['id'], true, false);
                                    if($getAccount){
                                        $arr['accountDetails'] = $getAccount;
                                        $arr['account_id'] = $getAccount['accountnumber'];
                                        $arr['amount'] = $amount;
                                    }

                                }
                                else{
                                    $arr['amount'] = $amount;
                                } 

                                array_push($memberToPay, $arr);
                            }
                        }
                    }

                    if($toAddAsSavings){
                        //Get savings account
                        foreach ($memberToPay as $pytKey => $pyt ) {
                            if($pyt['category'] == 'SAVINGS'){
                                if($memberToPay[$pytKey]['accountDetails']){
                                    $memberToPay[$pytKey]['amount'] = $memberToPay[$pytKey]['amount'] + $toAddAsSavings;
                                }
                                
                            }
                        }
                    }

                    if($memberData){
                        $rowData['memberData'] = $memberData;
                        $rowData['memberToPay'] = $memberToPay;
                        $rowData['originalData'] = $originalData;

                        array_push($dataList, $rowData);
                    }
                    
                    
                }

            }
            unlink($path.$fileName);
            //Loop to validate all data
            foreach ($dataList as $dataKEy => $dataVal) {
            }
        }
        return [
            'columnHeader' => $columnHeader,
            'particularItem' => $particularItem,
            'dataList' => $dataList
        ];


    }

    public function actionTestLoan(){
        $getArrear = LoanHelper::getArrears('12-000185');

        $getArrear2 = LoanHelper::getArrears('1-000265');
        

    }
}
