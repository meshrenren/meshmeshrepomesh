<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Shareaccount */


$share = array();

$shareAcct = array();
$shareAcct['fk_memid'] = null;
$shareAcct['date_created'] = null;
$shareAcct['is_active'] = null;
$shareAcct['no_of_shares'] = 782;
$shareAcct['totalSubscription'] = null;
$shareAcct['balance'] = null;
$shareAcct['fk_share_product'] = null;
$shareAcct['isWithDeposit'] = null;
$shareAcct['Deposit'] = 0;


$this->title = 'Create Shareaccount';
$this->params['breadcrumbs'][] = ['label' => 'Shareaccounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$share = yii\helpers\Html::encode(json_encode($shareProducts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
$shareAcct = yii\helpers\Html::encode(json_encode($shareAcct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>

	<share-account-form
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'
	:share-product='<?= $share ?>'
	:share-account-details='<?= $shareAcct?>'
	>
	
	
	</share-account-form>

