<?php

namespace app\controllers;

use Yii;
use app\models\Shareaccount;
use app\models\ShareaccountSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\ShareProduct;

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
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
        $searchModel = new ShareaccountSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionCreateaccount()
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	$account =json_decode($_POST['shareaccount']);
    	
    	$connection = \Yii::$app->db;
    	$transaction = $connection->beginTransaction();
    	
    	
    	$modelShareProduct = new ShareProduct();
    	$shareProd = $modelShareProduct->findOne($account->fk_share_product);
    	$shareProd->transaction_serial = $shareProd->transaction_serial + 1;
    	$shareProd->update();
    	
    	
    	$model = new Shareaccount();
    	$model->accountnumber = str_pad($account->fk_share_product, 3, '0', STR_PAD_LEFT)."-".str_pad($shareProd->transaction_serial, 5, '0', STR_PAD_LEFT);
    	$model->fk_memid = $account->fk_memid;
    	$model->date_created = date('Y-m-d H:i:s');
	    $model->is_active = 1;
	    $model->no_of_shares = $account->no_of_shares;
	    $model->totalSubscription = $shareProd->amount_per_share * $account->no_of_shares;
	    $model->balance=0;
	    $model->status="ACTIVE";
	    $model->fk_share_product = $account->fk_share_product;
	    
    	
    
    	
    	if($model->save())
    	{+
    	-
    		$transaction->commit();
    		return "success";
    	}
    	else{ 
    		$transaction->rollBack();
    		return $model->errors; }
    	
    	
    	
    	//return $account;
    	//return $account->fk_memid;
    }

    /**
     * Displays a single Shareaccount model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Shareaccount model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
    	$this->layout = 'main-vue';
        $model = new Shareaccount();
        $shareProducts = $model->getShareProducts();

       /* if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->accountnumber]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        } */
        
        
        return $this->render('create', [
        		'model' => $model, 'shareProducts' => $shareProducts
        ]);
    }

    /**
     * Updates an existing Shareaccount model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->accountnumber]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Shareaccount model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Shareaccount model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Shareaccount the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Shareaccount::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
