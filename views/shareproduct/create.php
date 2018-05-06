<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ShareProduct */

$this->title = 'Create Share Product';
$this->params['breadcrumbs'][] = ['label' => 'Share Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
