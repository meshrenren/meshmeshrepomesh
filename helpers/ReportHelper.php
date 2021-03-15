<?php

namespace app\helpers;

use Yii;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \app\models\Shareaccount;
use \app\models\ShareTransaction;

use app\helpers\accounts\LoanHelper;


class ReportHelper 
{

	public static function getExportStyle(){
		$mainHeader = [
            'font' => [
                'bold' => true,
                'size' => 18
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],

        ];

        $topColumn = [
            'font' => [
                'bold' => true,
                'size' => 18
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $mainColumn = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ];

        $body = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $secondaryHeader = [
            /*'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => 'E5E4E2' )
            ],*/
            'font'  => [
                'bold'  =>  true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        $secondaryBody =  [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => 'E5E4E2' )
            ],
            'font'  => [
                'bold'  =>  true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];

        $result = [
        	'mainHeader' 	      => $mainHeader,
        	'topColumn' 	      => $topColumn,
        	'mainColumn' 	      => $mainColumn,
            'body'                => $body,
            'secondaryHeader'     => $secondaryHeader,
            'secondaryBody'       => $secondaryBody  
        ];

		return $result;
    }

    /**
     * Setup and returns Mpdf configuration.
     *
     * @param $orientation <string> - PDF orientation type
     * @return Array
     */
    public static function getMpdfConfiguration(
        $orientation = 'portrait', 
        $annotMargin = 0
    ) {
        $config = [
            'mode' => '+utf-8', 
            "allowCJKoverflow" => true,
            "autoScriptToLang" => true,
            "allow_charset_conversion" => false,
            "autoLangToFont" => true,
            'tempDir' => dirname(__DIR__, 2) . '/web/mpdfTempDir'
        ];
        
        if ($orientation === 'landscape') {
            $config['orientation'] = 'L';
        }

        if ($annotMargin > 0) {
            $config['annotMargin'] = $annotMargin;
        }

        return $config;
    }



    /**
     * Set Rows for export file
     * @param $columns array -  each array must contain [label,field]
     * @param $startRow start of row count
     * @param $startCol start of column letter
     * @return $result object - contains [sheet, endRow ]
    */

    public static function setHeader($sheet, $columns, $startRow, $startCol){
        $let = $startCol;
        $rowCount = $startRow;

        $exportStyle = static::getExportStyle();

        foreach ($columns as $key => $col) {
            $ind = $col['label'];
            $label = $col['label'];
            $cell = $let.$rowCount.'';
            $sheet->setCellValue($cell, $label)->getStyle($cell)->applyFromArray($exportStyle['mainColumn']);
            $sheet->getColumnDimension($let)->setAutoSize(true);
            $chr = Yii::$app->view->incrementLetter($let, 1);
            $let = $chr;
        }
        $rowCount++;
        $result = [
            'sheet'     => $sheet,
            'endRow'    => $rowCount
        ];

        return $result;
    }

    /**
     * Set Rows for export file
     * @param $columns array -  each array must contain [label,field]
     * @param $dataRows array
     * @param $startRow start of row count
     * @param $startCol start of column letter
     * @return $result object - contains [sheet, endRow ]
    */
    public static function setRows($sheet, $columns, $dataRows, $startRow, $startCol){
        
        $rowCount = $startRow;

        $exportStyle = static::getExportStyle();

        foreach ($dataRows as $row) {
            $let = $startCol;
            foreach ($columns as $key => $col) {
                $ind = $col['prop'];
                $label = "";
                if(isset($row[$ind])){
                    $label = $row[$ind];
                }
                $cell = $let.$rowCount.'';
                $sheet->setCellValue($cell, $label)->getStyle($cell)->applyFromArray($exportStyle['body']);
                $sheet->getColumnDimension($let)->setAutoSize(true);
                $chr = Yii::$app->view->incrementLetter($let, 1);
                $let = $chr;
            }
            $rowCount++;
        }
        $result = [
            'sheet'     => $sheet,
            'endRow'    => $rowCount
        ];

        return $result;
    }


    public static function printLoanAging($postData){
        $date = $postData['date'];
        $loanList = $postData['loanList'];
        $header = $postData['header'];
        $grandTotal = $postData['grandTotal'];

        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">SUMMARY OF AGING VARIOUS LOAN</div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">AS OF '.$date.'</div></tr>
        </table>';


        foreach ($loanList as $loan) {

            if(count($loan['stationAging']) > 0){
                $transTable = "<div class = 'mb-10'>";
                $transTable .= "<label style = 'font-size: 14px;'><strong>". $loan['product_name'] ."</strong></label>";
                foreach ($loan['stationAging'] as $station) {
                    $transTable .= "<div class = 'mb-5'>";
                    $transTable .= "<span style = 'font-size: 14px; text-decoration: underline;'>". $station['station_name'] ."</span>";
                    if(count($station['list']) > 0){
                        /*$tableList = static::tableList($header, $station['list']);
                        $transTable .= $tableList;*/

                        $tableList = '<table class = "list-table table table-bordered mt-10" width = "100%">
                            <tr>
                                <th width = "20%">Name</th>
                                <th width = "20%">1-3 Months</th>
                                <th width = "20%">4-6 Months</th>
                                <th width = "20%">7-12 Months</th>
                                <th width = "20%">Over 1 Year</th>
                            </tr>';

                        $total = ['one_three' => 0, 'four_six' => 0, 'seven_twelve' => 0, 'over_one_year' => 0];
                        foreach ($station['list'] as $trans) {
                            $one_three = isset($trans['one_three']) && floatval($trans['one_three']) > 0 ? floatval($trans['one_three']) : 0;
                            $four_six = isset($trans['four_six']) && floatval($trans['four_six']) > 0 ? floatval($trans['four_six']) : 0;
                            $seven_twelve = isset($trans['seven_twelve']) && floatval($trans['seven_twelve']) > 0 ? floatval($trans['seven_twelve']) : 0;
                            $over_one_year = isset($trans['over_one_year']) && floatval($trans['over_one_year']) > 0 ? floatval($trans['over_one_year']) : 0;

                            $total['one_three'] += $one_three;
                            $total['four_six'] += $four_six;
                            $total['seven_twelve'] += $seven_twelve;
                            $total['over_one_year'] += $over_one_year;

                            $one_three_display = floatval($one_three) > 0 ? Yii::$app->view->formatNumber($one_three) : "";
                            $four_six_display = floatval($four_six) > 0 ? Yii::$app->view->formatNumber($four_six) : "";
                            $seven_twelve_display = floatval($seven_twelve) > 0 ? Yii::$app->view->formatNumber($seven_twelve) : "";
                            $over_one_year_display = floatval($over_one_year) > 0 ? Yii::$app->view->formatNumber($over_one_year) : "";
                            $tableList .= '<tr>
                                <td width = "20%">'.$trans['fullname'].'</td>
                                <td width = "20%">'.$one_three_display.'</td>
                                <td width = "20%">'.$four_six_display.'</td>
                                <td width = "20%">'.$seven_twelve_display.'</td>
                                <td width = "20%">'.$over_one_year_display.'</td>
                            </tr>';

                        }
                        $tableList .= '<tr style = "font-weight: bolf;">
                                <td width = "20%">TOTAL</td>
                                <td width = "20%">'.Yii::$app->view->formatNumber($total['one_three']).'</td>
                                <td width = "20%">'.Yii::$app->view->formatNumber($total['four_six']).'</td>
                                <td width = "20%">'.Yii::$app->view->formatNumber($total['seven_twelve']).'</td>
                                <td width = "20%">'.Yii::$app->view->formatNumber($total['over_one_year']).'</td>
                            </tr>
                        </table>';
                        $transTable .= $tableList;

                    }
                    $transTable .= "</div>";
                }
                
                $subTotal = $loan['totalAging'];
                $transTable .= '<table style = "font-weight: bold; color : #0483ce;" class = "list-table table mt-10" width = "100%">
                        <tr>
                            <td width = "20%">SUB TOTAL</td>
                            <td width = "20%">'.Yii::$app->view->formatNumber($subTotal['one_three']).'</td>
                            <td width = "20%">'.Yii::$app->view->formatNumber($subTotal['four_six']).'</td>
                            <td width = "20%">'.Yii::$app->view->formatNumber($subTotal['seven_twelve']).'</td>
                            <td width = "20%">'.Yii::$app->view->formatNumber($subTotal['over_one_year']).'</td>
                        </tr>
                    </table>';
                $transTable .= "</div><hr>";
                $listTemplate = $listTemplate . $transTable;
            }
        }

        $listTemplate .= '<table style = "font-weight: bold; color : #e43939;" class = "list-table table mt-10" width = "100%">
            <tr>
                <td width = "20%">GRAND TOTAL</td>
                <td width = "20%">'.Yii::$app->view->formatNumber($grandTotal['one_three']).'</td>
                <td width = "20%">'.Yii::$app->view->formatNumber($grandTotal['four_six']).'</td>
                <td width = "20%">'.Yii::$app->view->formatNumber($grandTotal['seven_twelve']).'</td>
                <td width = "20%">'.Yii::$app->view->formatNumber($grandTotal['over_one_year']).'</td>
            </tr>
        </table>';

        return $listTemplate;
    }

    public static function printLoanArrears($postData){
        $date = $postData['date'];
        $loanList = $postData['loanList'];
        $header = $postData['header'];

        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">SUMMARY OF AGING VARIOUS LOAN</div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">AS OF '.$date.'</div></tr>
        </table>';


        foreach ($loanList as $loan) {

            if(count($loan['arrearsList']) > 0){
                $transTable = "<div class = 'mb-10'>";
                $transTable .= "<label style = 'font-size: 14px;'><strong>". $loan['product_name'] ."</strong></label>";

                $tableList = static::tableList($header, $loan['arrearsList'], true);
                $transTable .= $tableList;

                $transTable .= "</div>";
                $listTemplate = $listTemplate . $transTable;
            }
        }

        return $listTemplate;
    }

    public static function printTableList($postData){
        $title = $postData['title'];
        $date = $postData['date'];
        $dataList = $postData['dataList'];
        $header = $postData['header'];

        $listTemplate = '<table width = "100%">
            <tr><td width = "100%" align = "center"><div>DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS<div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">'.$title.'</div></tr>
            <tr><td width = "100%" align = "center"><div style = "font-size: 18px;">AS OF '.$date.'</div></tr>
        </table>';


        foreach ($dataList as $data) {

            if(isset($data['tableList']) && count($data['tableList']) > 0){
                $transTable = "<div class = 'mb-10'>";
                $transTable .= "<label style = 'font-size: 14px;'><strong>". $data['name'] ."</strong></label>";

                $tableList = static::tableList($header, $data['tableList'], true);
                $transTable .= $tableList;

                $transTable .= "</div>";
                $listTemplate = $listTemplate . $transTable;
            }
        }

        return $listTemplate;
    }

    public static function tableList($header, $list, $getTotal = false){
        $transTable = '<table class = "list-table table table-bordered mt-10" width = "100%">
            <tr>';
        foreach ($header as $key => $value) {
            $transTable .= '<th>'.$value['label'].'</th>';
        }

        $transTable .= '</tr>';
        $total = array();
        foreach ($list as $trans) {
            $transTable .= '<tr>';
            foreach ($header as $key => $head) {
                $val = isset($trans[$head['prop']]) ? $trans[$head['prop']] : null;
                if(isset($head['type'])){
                    if($head['type'] == 'number'){
                       $val = $val && floatval($val) > 0 ? Yii::$app->view->formatNumber($val) : null;
                       if(!isset($total[$head['prop']])){
                            $total[$head['prop']] = 0;
                       }
                       $total[$head['prop']] += floatval($val);
                    }
                }
                $val = $val !== null ? $val : "";
                $transTable .= '<td>'.$val.'</td>';
            }
            $transTable .= '</tr>';

        }

        $transTable .= '<tr style = "font-weight: bolf;">';
        foreach ($header as $key => $head) {
            if($key == 0){
                $transTable .= '<td><strong>TOTAL</strong></td>';
                continue;
            }

            if(isset($total[$head['prop']])){
                $transTable .= '<td><strong>'.$total[$head['prop']].'</strong></td>';
            }else{
                $transTable .= '<td></td>';
            }
        }
        $transTable .= '</tr>';

        $transTable .= '</table>';

        return $transTable;
    }

    public static function getDividendRefund(){

        $cutOff = Yii::$app->view->getCutOff();
        $cutOffYear = date("Y", strtotime($cutOff));
        $year = $cutOffYear;

        //Get member that has share capital
        $getAccounts = Shareaccount::find()->innerJoinWith([/*'product', */'member'])
            ->joinWith(['transaction' => function ($query) use ($year) {
                $query->where("DATE_FORMAT(transaction_date, '%Y') = '$year'")
                ->orderBy('transaction_date ASC');
                }
            ])
            ->with(['loanAccounts' => function ($query){
                $query->select('DISTINCT(loanaccount.loan_id) as loan_id, loanaccount.member_id')
                /*->select(['loanaccount.loan_id', 'loanaccount.member_id'])*/;
                }
            , ])
            ->orderBy('member.last_name ASC')
            ->asArray()->all();

        $accountList = array();
        foreach ($getAccounts as $acc) {
            $transactionList = $acc['transaction'];
            $lastBalance = 0;
            $totalBalance = 0;
            $totalAmount = 0;
            $countTrans = count($transactionList);
            if($countTrans > 0){
                foreach ($transactionList as $trans) {
                    $lastBalance = $trans['running_balance'];
                    $totalBalance += $trans['running_balance'];
                    $totalAmount += $trans['amount'];
                }
                $acc['average_running_balance'] = $totalBalance / $countTrans ;
                $acc['total_running_balance'] = $totalBalance;
                $acc['count_transaction'] = $countTrans;
                $acc['totalAmount'] = $totalAmount;
            }

            $acc['transaction'] = $transactionList;
            $acc['last_running_balance'] = $lastBalance;

            //$acc['loanAccountsOld'] = $acc['loanAccounts'];
            $getLoanInterestEarned = LoanHelper::getLoanInterestEarned($acc['loanAccounts'], $year);
            $acc['loanAccounts'] = $getLoanInterestEarned['accountList'];
            $acc['totalLoanInterest'] = $getLoanInterestEarned['totalLoanInterest'];

            array_push($accountList, $acc);
        }

        return $accountList;
    }
}