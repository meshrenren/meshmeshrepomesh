<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use \Mpdf\Mpdf;

use app\helpers\particulars\ParticularHelper;
use app\helpers\accounts\SavingsHelper;
use app\helpers\accounts\ShareHelper;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'beginning-of-day', 'begin-the-day'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['beginning-of-day', 'begin-the-day'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_begin_the_day_","_view") ){
                                return true;
                            }
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $member = new \app\models\Member();
        $countAll = $member->getMemberCount(0, 0, 'All');
        $countLastYear = $member->getMemberCount("2019-01-01", "2019-12-31");
        
        $connection = \Yii::$app->db;
        $command = $connection->createCommand("select * from calendar c where c.date = CURDATE() and is_current=1 limit 1");
        $result = $command->queryOne();

        $currentDate = ParticularHelper::getCurrentDay();
        $currDate = date("m/d/Y", strtotime($currentDate));
        
        
        return $this->render('index',[
            'countAll'          => $countAll,
            'countLastYear'     => $countLastYear,
        	'calendarDate'      => $result,
            'currentDate'       => $currDate
        ]);
        
        
        
    }
    
    
    
   
    

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'main-login';
        if (!Yii::$app->user->isGuest) 
        {
            return $this->goHome();
        }


        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) 
        {
            return $this->goBack();
        }
        
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        @session_regenerate_id(true);
        Yii::$app->user->logout();
        Yii::$app->getSession()->destroy();
        
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }


     /**
     * Error page.
     *
     * @return string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }

    
    public function actionBeginningOfDay()
    {
        $this->layout = 'main-vue';

        $currentDate = ParticularHelper::getCurrentDay();
        
        return $this->render('beginningofday', ['currentDate' => $currentDate]);
        
    }
    public function actionBeginTheDay()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if(\Yii::$app->getRequest()->getBodyParams()){
            
            $post = \Yii::$app->getRequest()->getBodyParams();
            
            $helper = ParticularHelper::processBeginning();
            return $helper;
        }
        
    }



    public function actionPrintList(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){

            $postData = \Yii::$app->getRequest()->getBodyParams();
            $accType = $postData['accountType'];
            if($accType == 'Savings')
                $template = SavingsHelper::printList($postData['data']);
            else if($accType == 'Share')
                $template = ShareHelper::printList($postData['data']);
            
            $type = $postData['type'];
            if($type == "pdf"){
                // Set up MPDF configuration
                $config = [
                    'mode' => '+utf-8', 
                    "allowCJKoverflow" => true, 
                    "autoScriptToLang" => true,
                    "allow_charset_conversion" => false,
                    "autoLangToFont" => true,
                    'orientation' => 'L'
                ];
                $mpdf = new Mpdf($config);
                $mpdf->WriteHTML($template);

                // Download the PDF file
                $mpdf->Output();
                exit();
            }
            else{
                return [ 'data' => $template];
            }
        }
        
    }
}
