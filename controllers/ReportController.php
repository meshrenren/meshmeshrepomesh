<?php

namespace app\controllers;

use Yii;
use app\helpers\ReportHelper;

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

            //$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            ob_end_clean();
            $writer->save("php://output");
            exit();
        }
    }

}
