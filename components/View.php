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
}

?>