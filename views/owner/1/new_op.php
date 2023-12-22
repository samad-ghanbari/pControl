<?php

/* @var $this yii\web\View */
/* @var $model */
/* @var $project */
/* @var $opType */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use faravaghi\jalaliDatePicker\jalaliDatePicker;

$this->title = 'PDCP|New Operation';
?>

<p class="backicon">
    <a href="<?= 'setting?id='.$project['id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">
<h3 style="text-align: center; color:white; direction:rtl;"><?= ' پروژه '.$project['project']; ?></h3>
<h4 style="text-align: center; color:white; direction:rtl;"> افزودن عملیات / ویژگی جدید</h4>

<div class="row" style="width:100%;">
    <div class="col-md-8">
        <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
            <?php
            $form = ActiveForm::begin([
                'id'=>"recForm",
                'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']
            ]); ?>
            <?= $form->field($model, 'project_id')->hiddenInput()->label(false); ?>
            <?= $form->field($model, 'operation', ['labelOptions'=>['style'=>'color:white;']])->textInput(['placeholder' => "عنوان عملیات / ویژگی"]); ?>
            <?= $form->field($model, 'type_id', ['labelOptions'=>['style'=>'color:white;']])->dropDownList($opType); ?>
            <?= $form->field($model, 'op_weight', ['labelOptions'=>['style'=>'color:white;']])->textInput(['type'=>'number', 'value'=>0, 'min'=>0]); ?>
            <?= $form->field($model, 'priority', ['labelOptions'=>['style'=>'color:white;']])->textInput(['type'=>'number', 'value'=>1]); ?>

            <?= $form->field($model, 'design_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>
            <?= $form->field($model, 'install_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>
            <?= $form->field($model, 'operation_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>
            <?= $form->field($model, 'test_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>
            <?= $form->field($model, 'it_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>
            <?= $form->field($model, 'district_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>
            <?= $form->field($model, 'planning_role', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'نمی‌باشد' , 1=>'می‌باشد']); ?>


            <div class="form-group">
                <br/>
                <br/>
                <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>

            </div>
            <br/>
            <br/>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
    <div class="col-md-4">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/project.png'; ?>" style="display: block;width:90%; max-width:400px;height:auto; margin:auto;">
    </div>
</div>
<br/>
</div>