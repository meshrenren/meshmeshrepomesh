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
}

?>