<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcLom */
/* @var $project \app\models\PcProjects */

use yii\bootstrap\ActiveForm;

$this->title = 'PDCP|New LOM';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";
?>
    <p class="backicon">
        <a href="<?= Yii::$app->request->baseUrl.'/owner/edit_project?pid='.$project->id; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
    </p>

    <div class="topic-cover bg-gradient " >
        <div style="padding:20px; direction: rtl; width:100%;">
            <h3 style="text-align: center; color:white;"><?= ' پروژه '.$project->project; ?></h3>
            <h4 style="text-align:center; color:white;">
            افزودن تجهیز جدید به LOM
            </h4>
            <i class="fa fa-plus fa-2x" style="text-align:center; color:white; display:block;margin:auto;"></i>
        </div>
        <hr style="border-top:1px solid white;"/>
        <?php
        $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl."/owner/add_lom",
            'id'=>"lomForm",
            'options'=>['style'=>'direction:rtl;max-width:500px;min-width:100px; margin:auto', 'onsubmit'=>"return checkDedication();"]
        ]); ?>

        <?= $form->field($model, 'project_id')->hiddenInput()->label(false); ?>

        <?= $form->field($model, 'equipment', ['labelOptions'=>['style'=>'color:white;'],'options'=>['class'=>'enFont']])->textInput(); ?>
        <?= $form->field($model, 'quantity', ['labelOptions'=>['style'=>'color:white;'], 'options'=>['style'=>'']])->textInput(['id'=>'qnty','type'=>"number"]); ?>

        <?= $form->field($model, 'description',['labelOptions'=>['style'=>'color:white;']])->textarea(["row"=>2, 'option'=>['style'=>"direction:rtl;"]]); ?>

        <h4 style="text-align: center; line-height: 40px; height:40px;clear: both; color:white;" >تخصیص مناطق</h4>
        <br />
        <div style="clear:both;">
            <?= $form->field($model, 'area2', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a2','type' => "number"]); ?>
            <?= $form->field($model, 'area3', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a3','type' => "number"]); ?>
        </div>
        <br />
        <div  style="clear:both;">
            <?= $form->field($model, 'area4', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a4','type' => "number"]); ?>
            <?= $form->field($model, 'area5', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a5','type' => "number"]); ?>
        </div>
        <br />
        <div style="clear:both;">
            <?= $form->field($model, 'area6', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a6','type' => "number"]); ?>
            <?= $form->field($model, 'area7', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a7','type' => "number"]); ?>
        </div>
        <br />
        <div  style="clear:both;">
            <?= $form->field($model, 'area8', ['labelOptions'=>['style'=>'color:white;'],'options'=>['style'=>'float:right; width: 50%;']])->textInput(['id'=>'a8','type' => "number"]); ?>
        </div>
        <div class="form-group" style="clear:both;">
            <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
        </div>
        <?php ActiveForm::end(); ?>

        <br style="clear:both;" />
        <br />
    </div>


<?php

$script =<<< JS
function checkDedication()
{
    var quantity = $("#qnty").val() * 1;
    
    var a2 = $("#a2").val() * 1;
    var a3 = $("#a3").val() * 1;
    var a4 = $("#a4").val() * 1;
    var a5 = $("#a5").val() * 1;
    var a6 = $("#a6").val() * 1;
    var a7 = $("#a7").val() * 1;
    var a8 = $("#a8").val() * 1;
    
    var aa = a2 + a3 + a4 + a5 + a6 + a7 + a8;
    if(aa > quantity)
        {
            alert("مقادیر تخصیص یافته به مناطق از تعداد کل بیشتر است. ");
            return false;
        }
    else 
        return true;
}
JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>