<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\member\models\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Members';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Member', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>   
    <?php
        $gridColumns = [
        ];
    ?>

<?= kartik\grid\GridView::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'columns'           => [
            'memberID',
            'firstname',
            'middlename',
            'lastname',
            'mem_date',
            // 'birthday',
            // 'fk_branch',
            // 'mem_type',
            // 'isActive',

            ['class'        => 'yii\grid\ActionColumn'],
        ],
        'responsive'        =>true,
        'hover'             =>true
    ]); ?>
<?php Pjax::end(); ?></div>
