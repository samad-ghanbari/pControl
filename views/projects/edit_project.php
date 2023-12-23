<?php
/* @var $this yii\web\View */
/* @var $this yii\web\View */
/* @var  $model \app\models\PcProjects */
/* @var  $lom \app\models\PcLom */
/* @var  $project */
/* @var  $projects */

$this->title = 'PDCP|Project Edit';
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\bootstrap\ActiveForm;

use yii\widgets\Pjax;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];

?>

<div class="layout-wrapper bg-gradient" >
    <div class="layout-narrow-panel setting-sidebar">
        <?= \app\components\SidebarWidget::widget(['index'=>1, "projectId"=>$pId]); ?>
    </div>


    <div class="layout-wide-panel">

        <div style="width:95%; margin:auto;">
            <?php
            $form = ActiveForm::begin([
                'id'=>"projectsForm",
                'method' => 'GET',
                'action' => Yii::$app->request->baseUrl."/projects/edit_project",
                'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:10px auto;']]); ?>
            <label for="prj-form" style="display: block;color:white;">انتخاب پروژه</label>
            <select name="id" onchange="this.form.submit()" style="width: 300px;" class="form-control">
                <option value="-1" disabled <?php if($pId==-1) echo "selected"; ?> ></option>
                <?php
                foreach ($projects as $prj)
                {
                    $sel = "";
                    if($pId==$prj['id']) $sel="selected";
                    echo "<option value='".$prj['id']."' $sel >".$prj['project']."</option>";
                }
                ?>
            </select>

            <?php ActiveForm::end(); ?>
        </div>

        <hr />

        <?php
            if($pId > -1)
            {
                ?>
                <div style="width:95%; margin:auto;">
                    <h4 style="text-align: right;margin:10px;color:white;">ویرایش پروژه</h4>
                    <?php
                    $form = ActiveForm::begin([
                        'id'=>"prjForm",
                        'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; overflow:auto; margin:auto', 'onsubmit'=>"return checkDedication();"],

                    ]); ?>

                    <?= $form->field($model, 'project', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['maxlength' => true, 'style'=>"direction:rtl", 'placeholder' => "نام پروژه"]) ?>
                    <?= $form->field($model, 'office', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "پروژه اداره کل"]); ?>
                    <?= $form->field($model, 'contract_subject', ['labelOptions'=>['style'=>"color:white;"]])->textarea(['placeholder' => "موضوع قرارداد/پروژه"]); ?>
                    <?= $form->field($model, 'contract_company', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "طرف قرارداد"]); ?>
                    <?= $form->field($model, 'contract_date', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "تاریخ قرارداد"]); ?>
                    <?= $form->field($model, 'contract_duration', ['labelOptions'=>['style'=>"color:white;"]])->textInput(['placeholder' => "مدت زمان اجرای قرارداد"]); ?>

                    <?php
                    if($model->enabled == true)
                        $model->enabled = 1;
                    else
                        $model->enabled = 0;
                    ?>
                    <?= $form->field($model, 'enabled', ['labelOptions'=>['style'=>"color:white;"]])->dropDownList(["1"=>"فعال", "0"=>"غیرفعال"]); ?>
                    <div class="form-group">
                        <br/>
                        <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
                    </div>
                    <br/>
                    <?php ActiveForm::end(); ?>

                    <hr />
                    <h4 class="text-center text-primary" style="direction: rtl;color:white;">LOM</h4>

                    <?php
                    $form = ActiveForm::begin([
                        'id'=>"addEquipForm",
                        'method'=>"POST",
                        'action' => Yii::$app->request->baseUrl.'/projects/lom_action',
                        'options'=>['style'=>'float:left;padding:0;margin:0;'],

                    ]); ?>
                        <input type="hidden" name="csrf_param" value="csrf_token" />
                        <input type="hidden" name="pId" value="<?= $pId; ?>" />
                    <div style="padding:0;margin:0;float:left;" class="form-group">
                        <?= Html::submitButton("تجهیز جدید", ['name'=>'btnAction', 'value'=>'add', 'style'=>"direction:rtl;margin:0;", 'class' => 'btn btn-primary pull-left']) ?>
                    </div>

                    <br />
                    <?php ActiveForm::end(); ?>
                    <?= Html::a("فایل نمونه ورود LOM", ['projects/lom_template'] ,['style'=>"border-radius:0 8px 8px 0; direction:rtl;margin:0;float:right;", 'class' => 'btn btn-primary']) ?>
                    <?= Html::button("ورود LOM", [ 'onclick'=>"importLom($pId)", 'style'=>"float:right; border-radius:8px 0 0 8px; direction:rtl;",  'class' => 'btn btn-primary']) ?>
                    <br style="clear:both; "/>
                    <br />

                    <div style="display: flex; flex-wrap: wrap; justify-items: center;">
                        <?php
                        foreach ($lom as $LOM)
                            echo \app\components\LomWidget::widget(["model"=>$LOM, 'area'=>-1, 'edit'=>true, 'admin'=>true]);
                        ?>
                    </div>

                </div>
        <?php
            }
            else
            {
                // no selected
                ?>
                <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php
            }
        ?>
    </div>
</div>

<?php

require_once("getLomModal.php");

$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS

function importLom(pId)
{
    $("#project-id").val(pId);
    $("#getLomModal").modal("show");
}

function checkDedication()
{
    var amount = $("#amount").val() * 1;
    
    var a2 = $("#area2_q").val() * 1;
    var a3 = $("#area3_q").val() * 1;
    var a4 = $("#area4_q").val() * 1;
    var a5 = $("#area5_q").val() * 1;
    var a6 = $("#area6_q").val() * 1;
    var a7 = $("#area7_q").val() * 1;
    var a8 = $("#area8_q").val() * 1;
    
    var aa = a2 + a3 + a4 + a5 + a6 + a7 + a8;
    console.log(aa);
    console.log(amount);
    if(aa > amount)
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

