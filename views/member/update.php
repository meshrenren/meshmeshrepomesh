<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\member\models\Member */

$this->title = 'Update Member: ' . $model->memberID;
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->memberID, 'url' => ['view', 'id' => $model->memberID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="member-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
