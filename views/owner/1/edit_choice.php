<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcChoices */
/* @var $project */
/* @var $operation */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use faravaghi\jalaliDatePicker\jalaliDatePicker;

$this->title = 'PDCP|Edit Choice';
?>

<p class="backicon">
    <a href="<?= 'edit_op_choices?id='.$operation['id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>

<div class="topic-cover bg-gradient">
<h3 style="text-align: center; color:white; direction:rtl;"><?= ' پروژه '.$project['project']; ?></h3>
<h4 style="text-align: center; color:white; direction:rtl;"> ویرایش آیتم انتخاب </h4>
<h4 style="text-align: center; color:white; direction:rtl;"><?= $operation['operation']; ?></h4>

<div class="row" style="width:100%">
    <div class="col-md-8">
        <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
            <?php
            $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl.'/projects/editchoice' , 'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto'] ]); ?>

            <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
            <?= $form->field($model, 'op_id')->hiddenInput()->label(false); ?>
            <?= $form->field($model, 'choice', ['labelOptions'=>['style'=>'color:white;']])->textInput(); ?>
            <?= $form->field($model, 'choice_weight', ['labelOptions'=>['style'=>'color:white;']])->textInput(['type'=>'number', 'min'=>0, 'max'=>$operation['op_weight']]); ?>
            <?= $form->field($model, 'default', ['labelOptions'=>['style'=>'display:block;color:white;']])->checkbox([],false); ?>
            <div class="form-group">
                <br/>
                <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>

            </div>
            <br/>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
    <div class="col-md-4">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/update.png'; ?>" style="display: block;width:90%; max-width:300px;height:auto; margin:auto;">
    </div>
</div>
<br/>
</div>