<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcProjects */
/* @var $projects \app\models\PcProjects */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use faravaghi\jalaliDatePicker\jalaliDatePicker;

$this->title = 'PDCP|New Project';
?>

<p class="backicon">
    <a href="<?= Yii::$app->request->referrer; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>

<div class="topic-cover bg-gradient">
<div class="row" style="width:100%;">
    <div class="col-md-8">
        <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
            <h4 class="text-center text-primary" style="direction: rtl;color:white;">برای ایجاد پروژه جدید فرم زیر را تکمیل نمایید.</h4>
            <?php
            $form = ActiveForm::begin([
                'id'=>"recForm",
                'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']
            ]); ?>
            <?= $form->field($model, 'project', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['maxlength' => true, 'style'=>"direction:rtl", 'placeholder' => "نام پروژه"]) ?>
            <?= $form->field($model, 'office', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "پروژه اداره کل"]); ?>
            <?= $form->field($model, 'contract_subject', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "موضوع قرارداد/پروژه"]); ?>
            <?= $form->field($model, 'contract_company', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "طرف قرارداد"]); ?>
            <?= $form->field($model, 'contract_date', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "تاریخ قرارداد"]); ?>
            <?= $form->field($model, 'contract_duration', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "مدت زمان اجرای قرارداد"]); ?>
            <?= $form->field($model, 'enabled', ['labelOptions'=>['style'=>"color:white;"]])->dropDownList(["1"=>"فعال", "0"=>"غیرفعال"]); ?>

            <hr />
            <div class="form-group">
                <label for="template" class="text-right col-form-label text-white  ">الگوبرداری از پروژه</label>
                <div class="p-0">
                    <?= Html::dropDownList("template",-1,$projects,['id'=>'templateCB', 'required'=>true, 'class'=>"w-100 form-control en-font"]); ?>
                </div>
           </div>
           <br />


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