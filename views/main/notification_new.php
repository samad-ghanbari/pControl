<?php

/* @var $this yii\web\View */
/* @var $model */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use dosamigos\ckeditor\CKEditor;

$this->title = 'PDCP|New notification';
?>
<p class="backicon">
    <a href="notifications"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">
<div class="row" style="width:100%;  max-width:1000px; margin:auto;">
    <div class="col-md-9">
        <div style="margin:auto; padding: 20px;" >
            <h4 class="text-center" style="direction: rtl;color:white;">برای ایجاد اعلان جدید فرم زیر را تکمیل نمایید.</h4>
            <?php
            $form = ActiveForm::begin([
                'id'=>"notiForm",
                'options'=>['style'=>'direction:rtl; width:90%; max-width:600px;min-width:100px; margin:auto']
            ]); ?>

            <?= $form->field($model, 'panel_color', ['options'=>['style'=>''],'labelOptions'=>['style'=>'color:white;']])->dropDownList([ 'olivedrab'=>'سبز', 'mediumvioletred'=>'قرمز', 'dodgerblue'=>'آبی'] ); ?>
            <?= $form->field($model, 'title', ['options'=>['style'=>''],'labelOptions'=>['style'=>'color:white;']])->textInput(['maxlength' => true, 'style'=>"direction:rtl", 'placeholder' => "تیتر"]) ?>
            <?= $form->field($model, 'description')->widget(CKEditor::className(), ['options' => ['id'=>'ticket' ,'rows' => 10, 'required'=>true], 'preset' => 'full',
                'clientOptions' => ['language' => 'fa',
                    'allowedContent' => true,
                    'filebrowserUploadUrl' => yii\helpers\Url::to(['/main/img_upload']),
                    'filebrowserBrowseUrl' => Yii::$app->request->baseUrl.'/main/img_browse',
                ] ]) ?>

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
    <div class="col-md-3">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/alarm.png'; ?>" style="display: block;width:90%; max-width:200px;height:auto;padding-top: 45%; margin:auto;">
    </div>
</div>
<br/>
</div>