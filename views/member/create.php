<?php
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\member\models\Member */

$this->title = 'Member List';
//$this->params['breadcrumbs'][] = $this->title;

$member = array();
$member['first_name'] = null;
$member['middle_name'] = null;
$member['last_name'] = null;
$member['image_path'] = null;
$member['mem_date'] = null;
$member['birthday'] = null;
$member['branch_id'] = null;
$member['member_type_id'] = null;
$member['station_id'] = null;
$member['division_id'] = null;
$member['employee_no'] = null;
$member['position'] = null;
$member['gender'] = null;
$member['civil_status'] = null;
$member['salary'] = null;
$member['gsis_no'] = null;
$member['telephone'] = null;

$user = array();
$user['username'] = null;
$user['password'] = null;
$user['email'] = null;

$family = array();
$family['name'] = null;
$family['relation'] = null;
$family['fam_address'] = null;
$family['contact_no'] = null;

$address = array();
$address['con_address'] = null;
$address['city'] = null;
$address['province'] = null;

    $member = yii\helpers\Html::encode(json_encode($member, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $user = yii\helpers\Html::encode(json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $family = yii\helpers\Html::encode(json_encode($family, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $address = yii\helpers\Html::encode(json_encode($address, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $stationList = yii\helpers\Html::encode(json_encode($stationList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $divisionList = yii\helpers\Html::encode(json_encode($divisionList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $typeList = yii\helpers\Html::encode(json_encode($typeList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $memberList = yii\helpers\Html::encode(json_encode($memberList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
?>
<member-form
	:data-member='<?= $member ?>'
	:data-user='<?= $user ?>'
	:data-family='<?= $family ?>'
	:data-address='<?= $address ?>'
	:data-station-list='<?= $stationList ?>'
	:data-division-list='<?= $divisionList ?>'
	:data-type-list='<?= $typeList ?>'
	:data-member-list='<?= $memberList ?>'
	:data-view='<?= json_encode($view) ?>'
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>' >
</member-form>
