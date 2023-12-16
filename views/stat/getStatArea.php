<?php

/* @var $this yii\web\View */
/* @var  $areas */

$this->title = 'pControl|Area';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
?>

<div style="width: 100%; max-width: 700px; margin: auto;">
    <img src="<?= Yii::$app->request->baseUrl.'/web/images/pie.png'; ?>" style="width:80%; max-width: 100px; margin:auto; display: block;">
    <br />
    <h3 class="text-center text-primary">منطقه مورد نظر خود را انتخاب نمایید</h3>
    <?php $form = ActiveForm::begin(['options' => ['style' => "width:90%; max-width:300px; margin:auto; display:block; direction:rtl;"]]); ?>
        <?= Html::dropDownList('area','منطقه', $areas,['class'=>'form-control', 'style'=>"color:dodgerblue;"]); ?>
    <div class="form-group" style="width:100px;margin:auto;">
        <br />
        <button type="submit" class="btn btn-info"><i class="fa fa-check text-white" ></i> تایید </button>
    </div>
    <?php ActiveForm::end(); ?>
</div>

