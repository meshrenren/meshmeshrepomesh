<?php

namespace app\controllers;

use app\helpers\particulars\ParticularHelper;
use app\helpers\voucher\VoucherHelper;
use app\helpers\payment\PaymentHelper;
use app\helpers\journal\JournalHelper;
use app\models\AccountParticulars;
use app\models\VoucherDetails;

class GeneralVoucherController extends \yii\web\Controller
{
    public function actionIndex()
    {
    	$this->layout = 'main-vue';
        $voucher = new \app\models\GeneralVoucher;
        $voucherModel = $voucher->getAttributes();

        $details = new \app\models\VoucherDetails;
        $detailsModel = $details->getAttributes();

        $filter  = ['category' => ['OTHERS', 'SAVINGS', 'SHARE', 'LOAN', 'TIME_DEPOSIT']];
        $orderBy = [new \yii\db\Expression('FIELD (category,"OTHERS","LOAN","SAVINGS","SHARE","TIME_DEPOSIT"), name ASC')];
        $getParticular = ParticularHelper::getParticulars($filter, $orderBy);

        return $this->render('index', [
        	'voucherModel'      => $voucherModel,
            'detailsModel'      => $detailsModel,
            'particularList'    => $getParticular
        ]);
    }

    public function actionGetName(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $member  = \app\models\Member::find()->asArray()->all();
            $division  = \app\models\Division::find()->asArray()->all();
            $station  = \app\models\Station::find()->asArray()->all();

            return [
                'member' => $member,
                'division' => $division,
                'station' => $station,
            ];
        }
    }


    public function actionSaveVoucherEntries(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $success = false;
            $error = '';
            $data = null;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = \Yii::$app->getRequest()->getBodyParams();
                $voucherModel = $post['voucherModel'];
                $entryList = $post['entryList'];
                $success = false;
                $error = '';
                $data = null;

                //Check GV Number if exist
                $gv_num = $voucherModel['gv_num'];
                $getGV = \app\models\JournalHeader::find()->where(['reference_no' => $gv_num])->one();
                if($getGV){
                    $getGV = VoucherHelper::saveVoucher($voucherModel);
                }
                else{
                    if($getGV){
                        //Entries
                        $insertSuccess = VoucherHelper::insertEntries($entryList, $saveGV->id, 'OTHERS');
                        if($insertSuccess){
                            $success = true;
                        }
                        else{
                            $success = false;
                            $transaction->rollBack();
                        }
                    }
                    else{
                        $success = false;
                        $transaction->rollBack();
                    }

                     if($success && $saveGV){
                        $journalHeader = new \app\models\JournalHeader;
                        $journalHeaderData = $journalHeader->getAttributes();
                        $journalHeaderData['reference_no'] = $saveGV->gv_num;
                        $journalHeaderData['posting_date'] = $saveGV->date_transact;
                        $journalHeaderData['total_amount'] = 0;
                        $journalHeaderData['trans_type'] = 'GeneralVoucher';
                        $journalHeaderData['remarks'] = '';

                        $saveJournal = JournalHelper::saveJournalHeader($journalHeaderData);
                        if($saveJournal){
                            //Entries

                            $journalList = new \app\models\JournalDetails;
                            $journalListAttr = $journalList->getAttributes();
                            $lists = array();
                            $totalAmount = 0;
                            $totalCredit = 0;
                            $totalDebit = 0;
                            foreach ($entryList as $acct) {
                                if($acct['debit'] && (float)$acct['debit'] > 0){
                                    $arr = $journalListAttr;
                                    $arr['amount'] = $acct['debit'];
                                    $arr['particular_id'] = $acct['particular_id'];
                                    $arr['entry_type'] = "DEBIT";
                                    array_push($lists, $arr);

                                    $totalAmount += $acct['debit'];
                                }

                                if($acct['credit'] && (float)$acct['credit'] > 0){
                                    $arr = $journalListAttr;
                                    $arr['amount'] = $acct['credit'];
                                    $arr['particular_id'] = $acct['particular_id'];
                                    $arr['entry_type'] = "CREDIT";
                                    array_push($lists, $arr);
                                }
                                
                            }

                            $insertSuccess = JournalHelper::insertJournal($lists, $saveJournal->reference_no);
                            if($insertSuccess){
                                $saveJournal->total_amount = $totalAmount;
                                $saveJournal->save();
                                
                                $success = true;
                            }
                            else{
                                $success = false;
                                $transaction->rollBack();
                            }
                        }
                        else{
                            $success = false;
                            $transaction->rollBack();
                        }
                    }
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


    public function actionView(){
        $this->layout = 'main-vue';
        //$voucherList = $this->getVoucherList(null, 100);

        return $this->render('view', [
            'voucherList'   => []
        ]);
    }

    public function actionGetVoucher(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(\Yii::$app->getRequest()->getBodyParams())
        {
            $post = \Yii::$app->getRequest()->getBodyParams();
            $filter = $post['filter'];
            $getVoucher  = \app\models\GeneralVoucher::find()->where(['gv_num' => $filter['gv_num']])->asArray()->one();
            $voucherList = [];
            $success = false;
            if($getVoucher){
                $listFilter = ['voucher_id' => $getVoucher['id']];
                $voucherList = VoucherHelper::getVoucherList($listFilter, null);
                $success = true;
            }

            return [
                'success'   => $success,
                'voucher'   => $getVoucher,
                'list'      => $voucherList
            ];
        }
    }

    public function actionGetAllVoucher(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $getVouchers  = \app\models\GeneralVoucher::find()->asArray()->all();
        return [
            'data' => $getVouchers
        ];
    }
    
    public function actionViewSummary(){
    	$this->layout = 'main-vue';
    	//$voucherList = $this->getVoucherList(null, 100);
    	
    	return $this->render('view-summary', [
    			'voucherList'   => []
    	]);
    }
    
    public function actionGetParticulars() {
    	
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	return AccountParticulars::find()->select(['id', 'name'])->asArray()->all();
    	
    }
    
    public function actionGetVoucherSummaryPerParticulars() {
    	
    	try {
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		
    		$model = new VoucherDetails();
    		$post = \Yii::$app->getRequest()->getBodyParams();
    		
    		
    		
    		
    		//$modelx = $model->find()->joinWith(['voucher', 'particular'])->where(['particular_id'=>99])->andWhere(['like', 'general_voucher.name', 'MENDOZA,%', false])->asArray()->all();
    		
    		$modelx = $model->find()->joinWith(['voucher', 'particular'])->where(['particular_id'=>$post['particular_id']])->andWhere(['>=', 'general_voucher.date_transact', $post['date_from']])->andWhere(['<=', 'general_voucher.date_transact', $post['date_to']]);
    		
    		
    		
    		
    		if($post['name']!=='')
    			$modelx = $modelx->andWhere(['like', 'general_voucher.name', $post['name'].'%', false]);
    			
    			
    			
    			$modelx = $modelx->orderBy([
    					'general_voucher.date_transact' => SORT_ASC
    			])->asArray()->all();
    			
    			
    			return $modelx;
    	} catch (Exception $e) {
    		
    		return $e->getMessage();
    	}
    }
    

}
