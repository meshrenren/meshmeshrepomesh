<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\helpers\settings\ProductHelper;

class SettingsController extends \yii\web\Controller
{
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'permission-access', 'products'],
                'rules' => [
                    [
                        'actions' => ['index', 'permission-access'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_settings_","_view") ){
                                    return true;
                            }
                        }
                    ],
                    [
                        'actions' => ['products'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_product_settings_","_view") ){
                                    return true;
                            }
                        }
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPermissionAccess(){
    	$this->layout = 'main-vue';

    	$levels = \app\models\Levels::find()->all();

    	$superAdminAccess  = \app\models\LevelFunctions::find()
    		->joinWith(['function'])
    		->where(['level_id' => 1])
    		->asArray()->all();

    	$adminAccess  = \app\models\LevelFunctions::find()
    		->joinWith(['function'])
    		->where(['level_id' => 2])
    		->asArray()->all();

    	$managerAccess  = \app\models\LevelFunctions::find()
    		->joinWith(['function'])
    		->where(['level_id' => 3])
    		->asArray()->all();

    	$normalAccess  = \app\models\LevelFunctions::find()
    		->joinWith(['function'])
    		->where(['level_id' => 4])
    		->asArray()->all();

    	$limitedAccess  = \app\models\LevelFunctions::find()
    		->joinWith(['function'])
    		->where(['level_id' => 5])
    		->asArray()->all();

    	$accessList = [
    		'superAdmin' => $superAdminAccess,
    		'admin' => $adminAccess,
    		'manager' => $managerAccess,
    		'normal' => $normalAccess,
    		'limited' => $limitedAccess,
    	];

    	return $this->render('permission', [
        		'levels'			=> $levels,
        		'accessList'		=> $accessList,
        	]);
    }

    public function actionProducts(){

    	$this->layout = 'main-vue';

    	$loanProduct = ProductHelper::getActiveLoanProducts();
    	$loanData = [
    		'productList' => $loanProduct
    	];

    	$pageData = [
    		'loan' => $loanData
    	];

        return $this->render('product', ['pageData' => $pageData]);
    }

}
