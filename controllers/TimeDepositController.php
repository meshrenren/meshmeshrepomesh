<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf; 
use app\models\TimeDepositAccount;
use app\models\TimeDepositRateTable;

class TimeDepositController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate()
    {
    	$this->layout = 'main-vue';

    	$account = new \app\models\TimeDepositAccount;
    	$tdAccount = $account->attributes();

    	$transaction = new \app\models\TimeDepositTransaction;
    	$tdTransaction = $transaction->attributes();

        $tdlist = \app\models\TimeDepositAccount::find()->joinWith(['member'])
            ->asArray()->all();
        
        $tdRates = TimeDepositRateTable::find()->asArray()->all();

    	$tdProduct  = \app\models\TimeDepositProduct::find()
    		->joinWith(['ratetable'])
    		->asArray()->all();
    	
        return $this->render('create', [
            'tdlist'        => $tdlist,
        	'tdProduct'		=> $tdProduct,
        	'tdAccount'		=> $tdAccount,
        	'tdTransaction'	=> $tdTransaction,
        	'tdRates'		=> $tdRates
        ]);
    }
    
    public function actionShowtd()
    {
    	$model = new TimeDepositAccount();
    	$model->checkMaturity();
    }

    public function actionSaveTdAccount(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();

        	$today = date("Y-m-d");
        	$tdaccount = $post['accountDetails'];
        	$tdaccount = (array)$tdaccount;
        	$model = new \app\models\TimeDepositAccount;
        	$model->attributes = $tdaccount;
			$model->service_charge = $tdaccount['service_charge'];
        	$product = \app\models\TimeDepositProduct::find()->where(['id' => $tdaccount['fk_td_product']])->one();

        	$trans_serial = $product->trans_serial + 1;
        	$trans_serial_pad = str_pad($trans_serial, 6, '0', STR_PAD_LEFT);
        	$model->accountnumber = $product->id . "-" . $trans_serial_pad;

        	$mature_days = date('Y-m-d', strtotime($today. ' + '. $tdaccount['term'] . ' days'));

        	$model->maturity_date = $mature_days;
        	$model->date_created = date('Y-m-d H:i:s');
        	$model->account_status = 'ACTIVE';
	        $model->created_by = \Yii::$app->user->identity->id;
	        $model->balance = $tdaccount['amount'];
            $model->type = $tdaccount['type'];
            if($tdaccount['type'] == "Group"){
                $model->account_name = $tdaccount['account_name'];
                $model->member_id = 0;
            }
	        //var_dump($model->attributes);
	        if($model->save()){
                $product->trans_serial = $trans_serial;
                $product->save();
                //var_dampa($model is model duhhh!!)
        		$tdTransaction = new \app\models\TimeDepositTransaction;
        		$tdTransaction->fk_account_number = $model->accountnumber;
        		$tdTransaction->transaction_type = 'TDCASHDEP';
        		$tdTransaction->amount = $model->amount;
        		$tdTransaction->balance = $model->amount;
        		$tdTransaction->transaction_date = date('Y-m-d H:i:s');
        		$tdTransaction->transacted_by = \Yii::$app->user->identity->id;
        		$tdTransaction->save();

                if($model->type == "Group" && isset($post['signatoryList'])){
                    $signatories = $post['signatoryList'];
                    foreach ($signatories as $sign) {
                        $newSign = new \app\models\SaGroupSignatory;
                        $newSign->td_account = $model->accountnumber;
                        $newSign->member_id = $sign['id'];
                        $newSign->save();
                    }
                }

        		//var_dump($tdTransaction->getErrors());
        		return [
	        		'success'	=> true,
        				'account' => $tdaccount
        		
	        	];
	        }
	        else{
	        	//var_dump($model->getErrors());
	        	return [
	        		'success'	=> false
	        	];
	        }
	    }
    }

    public function actionGetTdAccounts(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(isset($_POST)){
        	$member_id = $_POST['member_id'];
        	$model = new \app\models\TimeDepositAccount;
    	
	    	$accountList = $model->getAccountListByMemberID($member_id);
	    	return $accountList;
        	
        }
    }

    public function actionList(){
        $this->layout = 'main-vue';
        
        $date = date("Y-m-d");
        $tdlist = \app\models\TimeDepositAccount::find()->joinWith(['member'])
            ->asArray()->all();
        return $this->render('list', [
            'tdAccounts'    => $tdlist,
            'typeList'      => 'td_list',
            'header'        => 'All Accounts'
        ]);

    }

    public function actionMaturedAccounts(){
        $this->layout = 'main-vue';

        $date = date("Y-m-d");
        $maturedtdlist = \app\models\TimeDepositAccount::find()->joinWith(['member'])
            ->where("maturity_date <= '" .$date . "'")
            ->asArray()->all();
        return $this->render('list', [
            'tdAccounts'    => $maturedtdlist,
            'typeList'      => 'td_matured',
            'header'        => 'Matured Accounts'
        ]);

    }

    public function actionGetMaturedAccounts(){
        $date = date("Y-m-d");
        $model = \app\models\TimeDepositAccount::find()->where(['maturity_date' => $date])->all();
        foreach ($model as $timedeposit) {
            $totalInterest = $timedeposit->getMatureDays($timedeposit->maturity_date, $timedeposit->amount, $timedeposit->interest_rate, $timedeposit->term);
            $totalAmount = $timedeposit->amount + $totalInterest;
        }
    }

    public function actionCertificate($accountnumber){
        $model = \app\models\TimeDepositAccount::find()->where(['accountnumber' => $accountnumber])->joinWith(['member'])->one();
        if($model){
            /*$transaction = \app\models\SavingsAccount::find()->where(['account_no' => $model->account_no])->one();
            $account_no = $model->account_no;
            $account_name = $model->member->fullname;
            $last_transaction = "";
            if($model->lastTransaction)
                $last_transaction = date('M d, Y', strtotime($model->lastTransaction->transaction_date));
            $balance = number_format($model->balance, 2, '.', ',');

            $settings  = new \app\models\DefaultSettings;
            $penalty = number_format($settings->getValue('savings_penalty'), 2, '.', ',');;*/
            //$account_balance = $model->account->account_balance;

            $template = Yii::$app->params['formTemplate']['timedeposit_certificate'];

            /*$template = str_replace('[account_name]', $account_name, $template);
            $template = str_replace('[account_number]', $account_no, $template);
            $template = str_replace('[last_transaction]', $last_transaction , $template);
            $template = str_replace('[balance]', $balance, $template);
            $template = str_replace('[penalty]', $penalty, $template);*/

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
                'content' => $template,  
                // format content from your own css file if needed or use the
                // enhanced bootstrap css built by Krajee for mPDF formatting 
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
                // any css to be embedded if required
                'cssInline' => '.kv-heading-1{font-size:10px}', 
                'defaultFontSize' => '10',
                 // set mPDF properties on the fly
                'options' => '',
                 // call mPDF methods on the fly
                'filename' => 'withdral.pdf'
            ]);

            //return $pdf;

            //$topdf = Yii::$app->view->topdf($template, 'Savings', 'withdral.pdf');

            //$topdf->getApi()->SetJS('this.print();');
            // return the pdf output as per the destination setting
            return $pdf->render();
        }
        
    }

}
