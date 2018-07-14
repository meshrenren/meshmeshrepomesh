<?php

namespace app\controllers;

class LoanController extends \yii\web\Controller
{
    public function actionIndex()
    {
    	$this->layout = 'main-vue';

    	$loandProduct  = \app\models\LoanProduct::find()
    		->asArray()->all();
    	
        return $this->render('index', [
        	'loandProduct'		=> $loandProduct,
        ]);
    }

    public function actionGetLoanAccountInfo(){

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
        	$member_id = $post['member_id'];
        	$model = new \app\models\LoanAccount;
    	
	    	$accountList = $model->getAccountListByMemberID($member_id);
	    	return $accountList;
        	
        }

    }

    public function actionEvaluateLoan(){
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
        	$member_id = $post['member_id'];
        	$model = new \app\models\LoanAccount;
    	
	    	$accountList = $model->getAccountListByMemberID($member_id);
	    	return $accountList;
        	
        }
    }
}
