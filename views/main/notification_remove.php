<?php

/* @var $this yii\web\View */
/* @var $model $model */
$this->title = 'PDCP|Remove Notification';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


Yii::$app->formatter->nullDisplay = "";
?>
<p class="backicon">
    <a href="notifications"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">

<div class="record-remove" style="direction:rtl;">
    <h3 class="text-center" style="color:white;">حذف اعلان</h3>
    <br />
<?php
    $form = ActiveForm::begin([
    'id'=>"recForm",
    'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto','onsubmit' => "return confirm('آیا از حذف اعلان اطمینان دارید؟');"]
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
        <?= Html::submitButton('<i style="color:red;" class="fa fa-trash  fa-3x" title="حذف اعلان"></i>', ['class'=>'hvr-bounce-in', 'style' =>'display:block; margin:auto;border:none; background:transparent;']) ?>
    </div>
    <br/>
    <?php ActiveForm::end(); ?>

    <!--  bulletin  -->
    <div class="box-shadow-dark" style="width: 200px; margin:10px auto; background-color: <?= $model->panel_color; ?>; color:#fff; height: 50px; text-align: center; font-weight: bold; line-height: 50px; ">
        <?= \app\components\Jdf::jdate("Y/m/d", $model->ts); ?>
    </div>
    <br />
    <h4 style="color: <?= $model->panel_color; ?>; text-align: center; direction: rtl; margin: 10px auto;"><?= $model->title; ?></h4>
    <hr style="border-top:2px solid <?= $model->panel_color; ?>">
    <div style="direction: rtl; text-align: justify;color:white; padding: 10px; margin:10px auto; width: 100%; ">
        <?= $model->description; ?>
    </div>



</div>
<br />
</div>