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
use app\helpers\settings\SettingsHelper;
use app\helpers\ReportHelper;

use \app\models\LoanProduct;
use \app\models\LoanAccount;
use \app\models\PaymentRecord;
use \app\models\PaymentRecordList;
use \app\models\PaymentRecordHistory;


class PaymentController extends \yii\web\Controller
{
    public $entryType = array();

    public function __construct($id, $module, $config = [])
    {
        $paymentHelper = new PaymentHelper;
        $entryType = $paymentHelper->entry_type;

        parent::__construct($id, $module, $config);
    } 


    public function actionIndex($record = null)
    {
        $this->layout = 'main-vue';

        $paymentRecordList = [];
        if($record != null){
            $paymentModel = \app\models\PaymentRecord::find()->where(['id' => $record])->asArray()->one();
            $paymentRecordList = PaymentHelper::getPaymentList($record);

        }
        else{
            $paymentModel = new \app\models\PaymentRecord;
            $paymentModel = $paymentModel->getAttributes();
        }

        $paymentModelList = new \app\models\PaymentRecordList;
        $paymentModelList = $paymentModelList->getAttributes();

        $filter  = ['category' => ['OTHERS']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);


        return $this->render('index', [
            'model'             => $paymentModel,
            'paymentModelList'  => $paymentModelList,
            'particularList'    => $getParticular,
            'paymentRecordList' => $paymentRecordList
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
            $success = true;
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $connection = Yii::$app->getDb();
                $post = \Yii::$app->getRequest()->getBodyParams();
                $paymentModel = $post['paymentModel'];
                $allAccounts = $post['allAccounts'];

                //Check GV Number if exist
                $or_num = $paymentModel['or_num'];
                $getOR = \app\models\JournalHeader::find()->where(['reference_no' => $or_num])->one();
                if($getOR){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASOR'
                    ];
                }
                else{
                    
                    $paymentRec = PaymentRecord::find()->where(['or_num' => $or_num])->one();
                    if($paymentRec){
                        if($paymentRec->posted_date != null){
                            return [
                                'success'   => false,
                                'error'     => 'ERROR_HASOR',
                                'test'     => 'ERROR_HASOR'
                            ];
                        }
                    }


                    $saveOR = PaymentHelper::savePayment($paymentModel);
                    if($saveOR){
                        if($paymentRec){
                            //Get then save to history
                            $paymentRecList = PaymentHelper::getPaymentList($paymentRec->id);
                            $paymentHistory = new PaymentRecordHistory();
                            $paymentHistory->or_num = $or_num;
                            $paymentHistory->payment_record_id = $paymentRec->id;
                            $paymentHistory->data = json_encode($paymentRecList);
                            $paymentHistory->created_date = date('Y-m-d h:i:s');
                            $paymentHistory->save();
                            //Delete existing payment record
                            PaymentRecordList::deleteAll('payment_record_id = :payment_record_id', [':payment_record_id' => $paymentRec->id]);
                        }

                        //Entries
                        $insertSuccess = PaymentHelper::insertAccount($allAccounts, $saveOR->id);
                        if($insertSuccess){
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
                }

                if($success){
                    $transaction->commit();
                }
                
                else
                	$transaction->rollBack();
            } catch (\Exception $e) {
                $success = false;
                $transaction->rollBack();
                $error =  $e->getMessage();
            } catch (\Throwable $e) {
                $success = false;
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

        $getParticular = ParticularHelper::getPayrollParticulars();

        $members = \app\models\Member::find()->all();

        return $this->render('payroll-payment', [
            'paymentModel'          => $paymentModel,
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
    
    public function actionGetPaymentListWithReference()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$post = \Yii::$app->getRequest()->getBodyParams();
    	
    	if($post)
    	{
    		//$interest = PaymentHelper::getCurrentInterest($post['accountnumber'], $post['int_rate']);
    		
    		$payments = \app\models\PaymentRecord::find()->where(['or_num'=>$post['or_num']])->andWhere(['not', ['posted_date' => null]])
    					->innerJoinWith('paymentlist')
    					->asArray()->all();
    		
    		return $payments;
    	}
    	
    }
    
    public function actionProcessPaymentCancellation($ref_id)
    {
    	//\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	PaymentHelper::unpostPayment($ref_id);
    }

    public function actionExportPayroll()
    {
        $this->layout = 'main-vue';

        $memberList = MemberHelper::getMemberList(null, true);

        $stationList  = SettingsHelper::getStation();


        $columnList = array();
        //Share Deposit, Savings Deposit 
        $particularId = [1, 19, 22, 29, 61, 21];

        $getParticular = ParticularHelper::getParticulars(['ids' => $particularId]);
        foreach ($getParticular as $key => $particular) {
            $arrProd = array();
            $arrProd['key'] = $particular['category'] . '_' . $particular['id'];
            $arrProd['label'] = $particular['name'];
            array_push($columnList, $arrProd);
        }

        $pageData =  [
            'memberList'    => $memberList,
            'stationList'   => $stationList,
            'columnList'    => $columnList
        ];

        return $this->render('payroll', [
            'pageData'    => $pageData]);
    }

    public function actionSetPaymentPayroll(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $members = $post['members'];

            $loanMember = array();
            $loanColumns = array();

            $loanId = array();
            $prepaidId = array();
            foreach ($members as $key => $mem) {

                $getLatestLoan = LoanHelper::getLatestLoan($mem, 'loan_id');

                if($getLatestLoan && count($getLatestLoan) > 0){
                    $arr = array();
                    $arr['member_id'] = $mem;
                    foreach ($getLatestLoan as $key => $loan) {
                        $arrKey = 'LOAN_' . $loan['loan_id'];
                        $arr[$arrKey] =  $loan['principal_amortization_quincena'];

                        if($loan['prepaid_amortization_quincena'] && floatval($loan['prepaid_amortization_quincena']) > 0){
                            $arrPreKey = 'LOAN_PI_' . $loan['loan_id'];
                            $arr[$arrPreKey] =  $loan['prepaid_amortization_quincena'];

                            if(!in_array($loan['loan_id'], $prepaidId)){
                                array_push($prepaidId, $loan['loan_id']);
                            }
                        }

                        if(!in_array($loan['loan_id'], $loanId)){
                            array_push($loanId, $loan['loan_id']);
                        }
                    }

                    array_push($loanMember, $arr);
                }
            }
            sort($loanId);

            //LOAN
            foreach ($loanId as $key => $loan) {
                $getProduct = LoanHelper::getProduct(['id' => $loan]);
                if($getProduct){
                    $pName = ucwords(strtolower($getProduct['product_name']));
                    $arrProd = array();
                    $arrProd['key'] = 'LOAN_' . $getProduct['id'];
                    $arrProd['label'] = $pName;
                    array_push($loanColumns, $arrProd);

                    if(in_array($loan, $prepaidId)){
                        $arrProd = array();
                        $arrProd['key'] = 'LOAN_PI_' . $getProduct['id'];
                        $arrProd['label'] = "PI on " . $pName;
                        array_push($loanColumns, $arrProd);
                    }
                }
            }

            return ['loanMember' => $loanMember, 
                'loanColumns' => $loanColumns];
        }
    }

    public function actionPayrollExport(){

        $alignment = new \PhpOffice\PhpSpreadsheet\Style\Alignment;

        $postData = \Yii::$app->getRequest()->getBodyParams();
        $data = $postData['data'];
        $title = $postData['title'];
        $headers = $postData['headers'];

        $spreadsheet = new Spreadsheet();
        $exportStyle = ReportHelper::getExportStyle();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle($title);

        $startLet = 'A';
        $cellCount = count($headers);

        $endChar = Yii::$app->view->convertIntToExcelColumn($cellCount);
        
        //TITLE
        $activeSheet->setCellValue('A1', "DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE")
            ->getStyle('A1')->applyFromArray($exportStyle['topColumn']);
        $activeSheet->mergeCells('A1:'.$endChar.'1');
        //Station Title

        $activeSheet->setCellValue('A3', $title)
            ->getStyle('A3')->applyFromArray($exportStyle['secondaryHeader']);
        $activeSheet->mergeCells('A3:'.$endChar.'3');

        //HEADER COLUMNS
        $dataHeaderRowIndex = 4;
        $headerProps = []; // used for getting items in $data

        if($headers && count($headers) > 0){
            $charNum = 1;
            foreach ($headers as $key => $value) {
                $currChar = Yii::$app->view->convertIntToExcelColumn($charNum);

                $headerProps[] = $value['key'];

                $activeSheet->setCellValue($currChar.$dataHeaderRowIndex, ($value['label'] ?? ''));

                $charNum++;
            }
        }
        
        //Set Column Design
        $cnt = 1;
        foreach (range('A',$endChar) as $col) {
            if($cnt == 1){
                $activeSheet->getColumnDimension($col)->setAutoSize(true); 
            }
            else{
                $activeSheet->getColumnDimension($col)->setWidth(10); 
            }
            $cnt++;
        }
        $headerCols = 'A'.$dataHeaderRowIndex.':'.$endChar.$dataHeaderRowIndex;
        $activeSheet->getStyle($headerCols)->applyFromArray(
            [
                'font'  => [
                    'bold'  =>  true
                ],
                'alignment' => [
                    'horizontal' => $alignment::HORIZONTAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]
        );
        $activeSheet->getStyle($headerCols)->getAlignment()->setWrapText(true);

        $dataStartIndex = $dataHeaderRowIndex + 1;
        $dataRows = $dataStartIndex;
        foreach ($data as $key => $datum) {
            $cell = $dataRows;
            
            $item = (Array)$datum;

            $charNum = 1;

            foreach ($headerProps as $index => $value) {
                $currChar = Yii::$app->view->convertIntToExcelColumn($charNum);

                if(isset($item[$value])){
                    $activeSheet->setCellValue($currChar.$cell, $item[$value]);
                }
                $charNum++;
            }
            $dataRows++;
        }

        $styleRows = $dataRows;
        for ($i=$dataStartIndex; $i < $styleRows; $i++) { 

            $activeSheet->getStyle('A'.$i.':'.$endChar.$i)->applyFromArray(
                [
                    'alignment' => [
                        'horizontal' => $alignment::HORIZONTAL_RIGHT
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]
            );
        }
        $activeSheet->getStyle('A'.$dataStartIndex.':'.'A'.$styleRows)->applyFromArray(
            [
                'alignment' => [
                    'horizontal' => $alignment::HORIZONTAL_LEFT
                ],
            ]
        );

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $writer->save("php://output");
        exit();
    }

    public function actionViewParticulars(){
        $this->layout = 'main-vue';

        $filter  = ['category' => ['LOAN', 'SAVINGS', 'SHARE', 'OTHERS']];
        $orderBy = "name ASC";
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);
        $memberList = MemberHelper::getMemberList(null, true);

        $pageData = [
            'particularList' => $getParticular,
            'memberList' => $memberList
        ];

        return $this->render('view-particular', [
            'pageData'    => $pageData
        ]);
    }

    public function actionGetPaymentParticular(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $filter = $post;
            $paymentRecordList = PaymentHelper::getPaymentsParticular($filter);

            return ['data' => $paymentRecordList];
        }
    }
}
