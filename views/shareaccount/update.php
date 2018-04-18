<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Shareaccount */

$this->title = 'Update Shareaccount: ' . $model->accountnumber;
$this->params['breadcrumbs'][] = ['label' => 'Shareaccounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->accountnumber, 'url' => ['view', 'id' => $model->accountnumber]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="shareaccount-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
