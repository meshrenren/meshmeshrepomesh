<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_settings_","_view") ){
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

    	return $this->render('permission', [
        		'levels'			=> $levels,
        		'superAdminAccess'	=> $superAdminAccess,
        		'adminAccess'		=> $adminAccess,
        		'managerAccess'		=> $managerAccess,
        		'normalAccess'		=> $normalAccess,
        		'limitedAccess'		=> $limitedAccess,
        	]);
    }

}
