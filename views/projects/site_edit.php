<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcUserProjects */
/* @var $project */
/* @var $projects */
/* @var $users */
/* @var $areas */
/* @var $exchanges */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'PDCP|Site Edit';
?>

<p class="backicon">
    <a href="<?= 'project_users?id='.$project['id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">

<h4 style="text-align: center; color:white; direction:rtl;"><?= ' امکان ویرایش اطلاعات پایه پروژه '.$project['project']; ?></h4>
<br >
<img src="<?= Yii::$app->request->baseUrl.'/web/images/lock.png'; ?>" style="display: block; max-width:200px;height:auto; margin:auto;">
<br />
<div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
    <?php $form = ActiveForm::begin(['options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']]); ?>
    <?= $form->field($model, 'project_id')->hiddenInput()->label(false); ?>

    <?= $form->field($model, 'area', ['labelOptions'=>['style'=>'color:white;']])->dropDownList($areas,['value'=>-1,'onchange'=>"areaChanged(this)"]); ?>
    <?= $form->field($model, 'site_editable', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'عدم امکان ویرایش' , 1=>'امکان ویرایش']); ?>

    <div class="form-group">
        <br/><br/><?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
    </div>
    <br/><br/>
    <?php ActiveForm::end(); ?>

</div>
<br/>
</div>


