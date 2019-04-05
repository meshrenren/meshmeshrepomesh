<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

use app\models\Shareaccount;
use app\models\ShareaccountSearch;
use app\models\ShareProduct;
use app\models\JournalHeader;
use app\models\JournalDetails;

use app\helpers\journal\JournalHelper;
use app\helpers\accounts\ShareHelper;
use app\helpers\particulars\ParticularHelper;


/**
 * ShareaccountController implements the CRUD actions for Shareaccount model.
 */
class ShareaccountController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'deposit', 'list'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_share_account_","_add") ){
                                    return true;
                            }
                        }
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_share_account_","_view") ){
                                    return true;
                            }
                        }
                    ],
                    [
                        'actions' => ['deposit'],
                        'allow' => true,
                        'matchCallback' => function() {
                            if( Yii::$app->user->identity->checkUserAccess("_share_account_","_edit") ){
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

    /**
     * Lists all Shareaccount models.
     * @return mixed
     */
    public function actionIndex()
    {

        $this->layout = 'main-vue';

        $model = new Shareaccount();
        $shareAcct = $model->getAttributes();

        $shareProducts = $model->getShareProducts();

        $accountList = ShareHelper::getAccountShareInfo();
        
        return $this->render('index', [
            'accountList'   => $accountList,
            'shareProducts' => $shareProducts,
            'shareAcct'     => $shareAcct
        ]);
    }
    
    public function actionCreateaccount()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = false;
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();

                $account = $post['shareaccount'];

                $hasAccount = Shareaccount::find()->where(['fk_memid' => $account['fk_memid'], 'fk_share_product' => $account['fk_share_product']])->one();
                if($hasAccount != null){
                    return [
                        'success'   => $success,
                        'error'     => 'HAS_ACCOUNT',
                        'data'      => $data
                    ];
                }
            	
                $shareProd = ShareProduct::find()->where(['id' => $account['fk_share_product']])->one();
            	$shareProd->transaction_serial = $shareProd->transaction_serial + 1;
            	$shareProd->update();
            	
            	
            	$model = new Shareaccount();
            	$model->accountnumber = $account['fk_share_product']."-".str_pad($shareProd->transaction_serial, 6, '0', STR_PAD_LEFT);
            	$model->fk_memid = $account['fk_memid'];
            	$model->date_created = date('Y-m-d H:i:s');
        	    $model->is_active = 1;
        	    $model->no_of_shares = $account['no_of_shares'];
        	    $model->totalSubscription = $shareProd->amount_per_share * $account['no_of_shares'];
        	    $model->balance=0;
        	    $model->status="Active";
                $model->fk_share_product = $account['fk_share_product'];

                if($model->save())
                {
                    $success = true;
                }
                
                if($success){
                    $transaction->commit();
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return [
                'success'   => $success,
                'error'     => $error,
                'data'      => $data
            ];
        }
    }

    public function actionGetAccounts(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){
            $accountList = ShareHelper::getAccountShareInfo();
            return $accountList;
        }

    }

    public function actionGetTransaction(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){
            
            $post = \Yii::$app->getRequest()->getBodyParams();

            $fk_share_id = $post['fk_share_id'];
            
            $accountList = ShareHelper::getTransaction($fk_share_id);
            return $accountList;
        }
    }

    /**
     * Deposit Shareaccount tarnsaction.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionDeposit()
    {
    	$this->layout = 'main-vue';
        $transaction = new \app\models\ShareTransaction;
        $savingsTransaction = $transaction->getAttributes();
        
        return $this->render('deposit', [ 'savingsTransaction' => $savingsTransaction]);
    }

    public function actionList()
    {
        $this->layout = 'main-vue';
        
        return $this->render('list');
    }


    /*
     This transaction id for Deposit only.
     As share only has deposit transaction
    */
    public function actionSaveTransaction(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams()){
            $success = false;
            $error = '';
            $errorMessage = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();
                $acct_transaction = $post['accountTransaction'];
                $product = $post['product'];
                $product_particularid = $product['particular_id'];

                //Check Reference Number if exist
                $reference_no = $acct_transaction['reference_number'];
                $getJH = JournalHeader::find()->where(['reference_no' => $reference_no])->one();
                if($getJH){
                    return [
                        'success'   => false,
                        'error'     => 'ERROR_HASRN',
                        'errorMessage' => 'Error processing the transaction. Please try again'
                    ];
                }


                $getShareAccount = Shareaccount::findOne($acct_transaction['fk_share_id']);

                $trans_type = "Payment";
                $coh_entrytype = "DEBIT"; // entry_type for Cash On Hand
                $acct_entrytype = "CREDIT"; // entry_type for the Account
                
                $running_balance = $getShareAccount->balance + $acct_transaction['amount'];
                $acct_transaction['running_balance'] = $running_balance;
                
                if($running_balance >= 0)
                {
                    $saveSD = ShareHelper::saveShareTransaction($acct_transaction);
                    if($saveSD){
                        $getShareAccount->balance = $running_balance;
                        if($getShareAccount->save()){
                            $success = true;
                        }
                        else{
                            $success = false;
                        }
                        $data = $saveSD->id;

                    }
                    else{
                        $success = false;
                        $error = "SD_ERROR";
                        $errorMessage = 'Error processing the transaction. Please try again';
                        $transaction->rollBack();
                    }

                    //Save to Journal
                    if($success && $saveSD){
                        $journalHeader = new JournalHeader;
                        $journalHeaderData = $journalHeader->getAttributes();
                        $journalHeaderData['reference_no'] = $saveSD->reference_number;
                        $journalHeaderData['posting_date'] = $saveSD->transaction_date;
                        $journalHeaderData['total_amount'] = $saveSD->amount;
                        $journalHeaderData['trans_type'] = $trans_type;
                        $journalHeaderData['remarks'] = $saveSD->remarks;

                        $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                        if($saveJournal){
                            //Entries

                            $journalList = new JournalDetails;
                            $journalListAttr = $journalList->getAttributes();
                            $lists = array();

                            $coh_id= ParticularHelper::getParticular(['name' => 'Cash On Hand']);//Cash on Hand particular id
                            // Account
                            $arr = $journalListAttr;
                            $arr['amount'] = $saveSD->amount;
                            $arr['particular_id'] = $product['particular_id'];
                            $arr['entry_type'] = $acct_entrytype;
                            array_push($lists, $arr);

                            // Cash On Hand                            
                            $arr = $journalListAttr;
                            $arr['amount'] = $saveSD->amount;
                            $arr['particular_id'] = $coh_id->id;
                            $arr['entry_type'] = $coh_entrytype;
                            array_push($lists, $arr);


                            $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->reference_no);
                            if($insertSuccess){                                
                                $success = true;
                            }
                            else{
                                $success = false;
                                $error = "SD_ERROR";
                                $errorMessage = 'Error processing the transaction in Journal List. Please try again';
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $error = "SD_ERROR";
                            $errorMessage = 'Error processing the transaction in Journal Header. Please try again';
                            $transaction->rollBack();
                        }
                    }
                    
                }
                else
                {
                    $success = false;
                    $error = "SD_NEGATIVE";
                    $errorMessage = 'Share Deposit cannot be negative';
                    $transaction->rollBack();
                }

                if($success){
                    $transaction->commit();
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return [
                'success'       => $success,
                'error'         => $error,
                'errorMessage'  => $errorMessage,
                'data'          => $data,
            ];

            
        }
    }
}
