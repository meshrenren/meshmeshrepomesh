<?php

namespace app\controllers;

use Yii;
use app\helpers\ReportHelper;
use app\helpers\accounts\LoanHelper;
use app\helpers\settings\SettingsHelper;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Pdf;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLoanAging(){
        $this->layout = 'main-vue';

        $loanProducts = LoanHelper::getProductList(['is_active' => 1], true);
        $stationList = SettingsHelper::getStation();

        //$memberList = MemberHelper::getMemberList(null, true);

        $pageData = [
            'loanProducts' => $loanProducts,
            'stationList'   => $stationList
        ];

        return $this->render('loan-aging', [
            'pageData'    => $pageData
        ]);
    }

    public function actionGetLoanAging(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = Yii::$app->getRequest()->getBodyParams();
            $transaction = LoanHelper::getActiveLoans();

            return ['data' => $transaction];
        }

        return false;
    }

    public function actionPrintLoanAging(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            
            $template = ReportHelper::printLoanAging($postData['data']);
            $type = $postData['type'];
            if($type == "pdf"){
                // Set up MPDF configuration
                $config = [
                    'mode' => '+utf-8', 
                    "allowCJKoverflow" => true, 
                    "autoScriptToLang" => true,
                    "allow_charset_conversion" => false,
                    "autoLangToFont" => true,
                    'orientation' => 'L'
                ];
                $mpdf = new Mpdf($config);
                $mpdf->WriteHTML($template);

                // Download the PDF file
                $mpdf->Output();
                exit();
            }
            else{
                return [ 'data' => $template];
            }
        }
    }



    public function actionPrintLoanArrears(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            
            $template = ReportHelper::printLoanArrears($postData['data']);
            $type = $postData['type'];
            if($type == "pdf"){
                // Set up MPDF configuration
                $config = [
                    'mode' => '+utf-8', 
                    "allowCJKoverflow" => true, 
                    "autoScriptToLang" => true,
                    "allow_charset_conversion" => false,
                    "autoLangToFont" => true,
                    'orientation' => 'L'
                ];
                $mpdf = new Mpdf($config);
                $mpdf->WriteHTML($template);

                // Download the PDF file
                $mpdf->Output();
                exit();
            }
            else{
                return [ 'data' => $template];
            }
        }
    }


    public function actionLoanArrears(){
        $this->layout = 'main-vue';

        $loanProducts = LoanHelper::getProductList(['is_active' => 1], true);
        $stationList = SettingsHelper::getStation();

        //$memberList = MemberHelper::getMemberList(null, true);

        $pageData = [
            'loanProducts' => $loanProducts,
            'stationList'   => $stationList
        ];

        return $this->render('loan-arrears', [
            'pageData'    => $pageData
        ]);
    }

    public function actionGetLoanArrears(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = Yii::$app->getRequest()->getBodyParams();
            $transaction = LoanHelper::getLoanArrears();

            return ['data' => $transaction];
        }

        return false;
    }

    public function actionDefaultExcelExport(){

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = Yii::$app->getRequest()->getBodyParams();
            $title = $post['title'];
            $headerTitle = isset($post['headerTitle']) ? $post['headerTitle'] : "";
            $columns = $post['headers'];
            $data = $post['data'];

            $exportStyle = ReportHelper::getExportStyle();
            $colCount = count($columns);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($title);


            $let = 'A';
            $letRow = 1;

            //Set Main Title
            $cell = 'A'.$letRow;
            $sheet->setCellValue($cell, "DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS")->getStyle($cell)->applyFromArray($exportStyle['mainHeader']);
            $ascii = ord($let);
            $chr = chr($ascii + ($colCount-1));
            $cell2 = $chr.$letRow;
            $sheet->mergeCells($cell.':'.$cell2);
            $letRow++;

            //Set Title
            $cell = 'A'.$letRow;
            $sheet->setCellValue($cell, $title)->getStyle($cell)->applyFromArray($exportStyle['secondaryHeader']);
            $ascii = ord($let);
            $chr = chr($ascii + ($colCount-1));
            $cell2 = $chr.$letRow;
            $sheet->mergeCells($cell.':'.$cell2);

            

            $letRow += 2;

            if(count($data) > 0){
                //Set header
                $resultHeader = ReportHelper::setHeader($sheet, $columns, $letRow, $let);
                $sheet = $resultHeader['sheet'];
                $letRow = $resultHeader['endRow'];

                //Set Data
                $result = ReportHelper::setRows($sheet, $columns, $data, $letRow, $let);
                $sheet = $result['sheet'];
                $letRow = $result['endRow'];

                $letRow = $letRow + 2;
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
            //$writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            ob_end_clean();
            $writer->save("php://output");
            exit();
        }
    }


    public function actionSavingsList(){
        $this->layout = 'main-vue';

        $stationList = SettingsHelper::getStation();
       /* $model = new \app\models\SavingAccounts;
        $accountList = $model->getAccountList();*/

        $pageData = [
            'stationList'   => $stationList,
            'accountList'   => []
        ];

        return $this->render('savings-list', [
            'pageData'    => $pageData
        ]);
    }

    public function actionPrintTableList(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            
            $template = ReportHelper::printTableList($postData['data']);
            $type = $postData['type'];
            if($type == "pdf"){
                // Set up MPDF configuration
                $config = [
                    'mode' => '+utf-8', 
                    "allowCJKoverflow" => true, 
                    "autoScriptToLang" => true,
                    "allow_charset_conversion" => false,
                    "autoLangToFont" => true,
                    'orientation' => 'L'
                ];
                $mpdf = new Mpdf($config);
                $mpdf->WriteHTML($template);

                // Download the PDF file
                $mpdf->Output();
                exit();
            }
            else{
                return [ 'data' => $template];
            }
        }
    }

    public function actionDividendRefund(){
        $this->layout = 'main-vue';

        $loandProduct  = \app\models\LoanProduct::find()->joinWith(['serviceCharge'])->where(['is_active' => 1])
            ->asArray()->all();
        $stationList = SettingsHelper::getStation();
        $pageData = [
            'stationList'   => $stationList,
            'loandProducts' => $loandProduct
        ];

        return $this->render('dividen-refund', [
            'pageData'    => $pageData
        ]);
    }

    public function actionGetDividendRefund(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->getRequest()->getBodyParams();
        $accountList = ReportHelper::getDividendRefund();

        return ['data' => $accountList];
    }

    public function actionPrintInterestEarned(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            
            $template = ReportHelper::printInterstEarned($postData['data']);
            $type = $postData['type'];
            if($type == "pdf"){
                // Set up MPDF configuration
                $config = [
                    'mode' => '+utf-8', 
                    "allowCJKoverflow" => true, 
                    "autoScriptToLang" => true,
                    "allow_charset_conversion" => false,
                    "autoLangToFont" => true,
                    'orientation' => 'L'
                ];
                $mpdf = new Mpdf($config);
                $mpdf->WriteHTML($template);

                // Download the PDF file
                $mpdf->Output();
                exit();
            }
            else{
                return [ 'data' => $template];
            }
        }
    }


}
