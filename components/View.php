<?php

namespace app\components;

use Yii;
use kartik\mpdf\Pdf;

class View extends \yii\web\View
{
	public function topdf($content, $title, $filename) {

        $pdf = new Pdf([
            // set to use core fonts only
            // 'mode' => Pdf::MODE_CORE, 
            'mode' => Pdf::MODE_BLANK, 
            // A4 paper format
            'format' => Pdf::FORMAT_A4, 
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT, 
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER, 
            // your html content input
            'content' => $content,  
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting 
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:10px}', 
            'defaultFontSize' => '10',
             // set mPDF properties on the fly
            'options' => ['title' => $title],
             // call mPDF methods on the fly
            'filename' => $filename,
            'methods' => [ 
                'SetHeader'=>["DILG XI EMPC"], 
            ]
        ]);

        return $pdf;
    }

    //This is a current date base on system date from calendar Table
    public function current_date() {

        $calendar = \app\models\Calendar::find()->where(['is_current' => 1])->one();
        if($calendar){
            return $calendar->date;
        }

        return date('Y-m-d'); //Just ue the current date in case
    }

    public function convertIntToExcelColumn($num = 1){
        $count = intdiv($num, 26);
        $char = $num - ($count * 26);
        $isLastChar = false;

        if($char == 0){
            $char = 26;

            $count--;
        }

        return $count > 0 ? chr($count + 64) . chr($char+64) : chr($char+64);
    }

    /* 
        FOR CUT OFF YEAR 
        E.G.
        Current Year: 2021

    */

    /* 
        Base the example above
        return 2020-12-31 
    */
    public function getCutOff() {
        $lastYear = intval(date('Y')) - 1;
        $cutOff = date($lastYear.'-12-31');
       
        return $cutOff;
    } 

    /* 
        Base the example above
        return 2021-12-31 
    */
    public function getCutOffThisYear() {
        $cutOff = date('Y-12-31');
       
        return $cutOff;
    } 

    /* 
        Base the example above
        return 2019-12-31 
    */
    public function getCutOffPrevYear() {
        $prevYear = intval(date('Y')) - 2;
        $cutOff = date($prevYear.'-12-31');
       
        return $cutOff;
    } 

    public function formatNumber($num){
        $num = floatval($num);
        $format_number = number_format($num, 2, '.', ',');
        return $format_number;
    }

    public function getVersion($date){
        $vr = '1'; // For old calculation

        $release_date = $date;
        $new_policy = "2020-08-10";
        if($release_date >= $new_policy){
            $vr = '1-2020.08'; //New policy update from August 2020. Check documentatoin of the new policy
        }

        return $vr;
    }

    public function incrementLetter($curr_letter, $count = 1)
        {
            $strlen = strlen($curr_letter);

            $firstAscii = ord("A");
            $lastAscii = ord("A");

            $first_letter = '';
            $last_letter = '';

            if($strlen == 1){
                $ascii = ord($curr_letter);
                $ascii2 = $ascii + $count;

                $last_letter = chr($ascii2);
            }

            if($strlen == 2){
                $arrLetter = str_split($curr_letter);
                $fLetter = $arrLetter[0];
                $lLetter = $arrLetter[1];
                
                $ascii = ord($lLetter);
                $ascii2 = $ascii + $count;

                $firstAscii = ord($fLetter);

                $first_letter = $fLetter;
                $last_letter = chr($ascii2);
            }

            $minus = $ascii2 - 90;
            $whole = 0;
            if($ascii2 > 90){
                $mod = $minus % 26; // For second letter
                $temp = $strlen > 1 ? floor(($ascii + $firstAscii - 90) / 26) - 1: floor($minus / 26) ;
                $whole = $temp; // For first letter'
                $fAscii = $firstAscii + $whole;
                $lAscii = $lastAscii + ($mod - 1); // Include previous letter
                $first_letter = chr($fAscii);
                $last_letter = chr($lAscii);
            }
            return $first_letter . $last_letter;
        }
}

?>