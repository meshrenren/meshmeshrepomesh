<?php

namespace app\helpers;

use Yii;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


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
}