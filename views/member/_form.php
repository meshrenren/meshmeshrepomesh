<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\MembershipType;
use app\models\Branch;
use app\models\Division;
use app\models\Station;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\modules\member\models\Member */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-form">
    <?php
        $form = kartik\widgets\ActiveForm::begin();
    ?>

    <div class = "row">
        <div class = "col-md-6">
            <?php
                echo $form->field($model, 'first_name')->textInput(['maxlength' => true]);

                echo $form->field($model, 'middle_name')->textInput(['maxlength' => true]);

                echo $form->field($model, 'last_name')->textInput(['maxlength' => true]);

                echo $form->field($model, 'birthday')->widget(DatePicker::classname(), [
                        'pluginOptions'     => [
                            'format'            => 'yyyy-mm-dd',
                            'todayHighlight'    => true,
                            'autoclose'         =>true,
                        ]
                ]); 

                echo $form->field($model, 'mem_date')->widget(DatePicker::classname(), [
                        'pluginOptions'     => [
                            'format'            => 'yyyy-mm-dd',
                            'todayHighlight'    => true,
                            'autoclose'         =>true,
                        ]
                ]);

                $gender = ["Male" => "Male", "Female" => "Female"];
                echo $form->field($model, 'gender')->widget(kartik\widgets\Select2::classname(), [
                                'data'      => $gender,
                                'options'   => ['placeholder' => 'Select gender ...'],
                                        ]);


                $civil_status = ["Single" => "Single", "Married" => "Married", "Widowed" => "Widowed", "Divorced" => "Divorced",  "Separated" => "Separated"];
                echo $form->field($model, 'civil_status')->widget(kartik\widgets\Select2::classname(), [
                                'data'      => $civil_status,
                                'options'   => ['placeholder' => 'Select branch ...'],
                                        ]);


                echo $form->field($model, 'telephone')->textInput();

                echo $form->field($model, 'gsis_no')->textInput();
            ?>
        </div>

        <div class = "col-md-6">
            <?php

                echo $form->field($model, 'image')->widget(kartik\widgets\FileInput::classname(), [
                    'pluginOptions'         => 
                    [
                        'showUpload'            => true,
                        'initialPreview'        =>[ $model->image_path],
                        'initialPreviewAsData'  =>true,
                    ],
                    'options' => ['accept' => 'image/*'],
                ])->label('Upload employee\'s profile picture');

                echo $form->field($model, 'salary');

                echo $form->field($model, 'position')->textInput();

                echo $form->field($model, 'employee_no')->textInput();

                $station_list = yii\helpers\ArrayHelper::map(Station::find()->all(), 'id', 'name');
                echo $form->field($model, 'station_id')->widget(kartik\widgets\Select2::classname(), [
                                'data'      => $station_list,
                                'options'   => ['placeholder' => 'Select station ...'],
                                        ]);


                $division_list = yii\helpers\ArrayHelper::map(Division::find()->all(), 'id', 'name');
                echo $form->field($model, 'division_id')->widget(kartik\widgets\Select2::classname(), [
                                'data'      => $division_list,
                                'options'   => ['placeholder' => 'Select division ...'],
                                        ]);

                $branch_list = yii\helpers\ArrayHelper::map(Branch::find()->all(), 'id', 'branch_desc');
                echo $form->field($model, 'branch_id')->widget(kartik\widgets\Select2::classname(), [
                                'data'      => $branch_list,
                                'options'   => ['placeholder' => 'Select branch ...'],
                                        ]);


                $type_list = yii\helpers\ArrayHelper::map(MembershipType::find()->all(), 'id', 'description');
                echo $form->field($model, 'member_type_id')->widget(kartik\widgets\Select2::classname(), [
                                'data'      => $type_list,
                                'options'   => ['placeholder' => 'Select type ...'],
                                        ]);

            ?>
        </div>

        <div class = "col-md-12">
            <div class="form-group" style = "text-align: center;">
                <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php kartik\widgets\ActiveForm::end(); ?>

</div>
