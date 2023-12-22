<?php
/* @var $this yii\web\View */
/* @var $this yii\web\View */
/* @var  $model */
/* @var  $project */


$this->title = 'PDCP|Project Edit';
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\bootstrap\ActiveForm;

use yii\widgets\Pjax;

Yii::$app->formatter->nullDisplay = "";
$this->registerCssFile(Yii::$app->request->baseUrl.'/web/css/sidebar.css');
?>

<div class="side-bar-container">
    <div class="side-bar">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/setting.png'; ?>">
        <ul>
            <li class="active" title=" پروژه ">
                <a href="<?= Yii::$app->request->baseurl.'/projects/edit_project?id='.$project['id']; ?>" ><span class="li-text"> پروژه </span><i class='fas fa-edit text-success'></i></a>
            </li>

            <li  title=" پارامترهای پروژه ">
                <a href="<?= Yii::$app->request->baseurl.'/projects/setting?id='.$project['id']; ?>" ><span class="li-text"> پارامترهای پروژه </span><i class='fa fa-tasks text-primary'></i></a>
            </li>

            <li  title=" کاربران پروژه ">
                <a href="<?= Yii::$app->request->baseurl.'/projects/project_users?id='.$project['id']; ?>" ><span class="li-text"> کاربران پروژه </span><i class='fas fa-users text-primary'></i></a>
            </li>

            <li  title=" حذف پروژه ">
                <a href="<?= Yii::$app->request->baseurl.'/projects/remove_project?id='.$project['id']; ?>" ><span class="li-text"> حذف پروژه </span><i class='fa fa-times text-danger'></i></a>
            </li>

        </ul>
    </div>

    <i class="fa fa-edit fa-2x " style="color:mediumvioletred; display: block; text-align:center; margin:auto;" ></i>
    <h3 style="text-align: center;"><?= ' پروژه '.$project['project']; ?></h3>
    <h4 style="text-align: center;"><?= $project['office']; ?></h4>
    <hr style="border:1px dotted lightgray;" />


    <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
                <h4 class="text-center text-primary" style="direction: rtl;">ویرایش پروژه</h4>
                <?php
                $form = ActiveForm::begin([
                    'id'=>"recForm",
                    'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto', 'onsubmit'=>"return checkDedication();"],

                ]); ?>

                <?= $form->field($model, 'project', ['labelOptions'=>['class'=>'default-color']])->textInput(['maxlength' => true, 'style'=>"direction:rtl", 'placeholder' => "نام پروژه"]) ?>
                <?= $form->field($model, 'office', ['labelOptions'=>['class'=>'default-color']])->textInput(['placeholder' => "پروژه اداره کل"]); ?>
                <?= $form->field($model, 'device_type', ['labelOptions'=>['class'=>'default-color']])->textInput(['placeholder' => "نوع تجهیز"]); ?>
                <?= $form->field($model, 'amount', ['labelOptions'=>['class'=>'default-color']])->textInput(['id'=>'amount', 'type'=>'number', 'placeholder' => "تعداد کل تجهیز"]); ?>
                <hr />
                <h4 class="text-center text-primary" style="direction: rtl;">LOM</h4>
                <?php
                $json = $model->dedication;
                ?>
                <?= Html::label('منطقه ۲: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area2]', $json['area2'], ['id'=>'area2_q', 'type'=>'number', 'class'=>"form-control"]); ?>
                <?= Html::label('منطقه ۳: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area3]', $json['area3'], ['id'=>'area3_q', 'type'=>'number', 'class'=>"form-control"])?>
                <?= Html::label('منطقه ۴: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area4]', $json['area4'], ['id'=>'area4_q', 'type'=>'number', 'class'=>"form-control"]); ?>
                <?= Html::label('منطقه ۵: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area5]', $json['area5'], ['id'=>'area5_q','type'=>'number', 'class'=>"form-control"]); ?>
                <?= Html::label('منطقه ۶: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area6]', $json['area6'], ['id'=>'area6_q','type'=>'number', 'class'=>"form-control"]); ?>
                <?= Html::label('منطقه ۷: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area7]', $json['area7'], ['id'=>'area7_q','type'=>'number', 'class'=>"form-control"]); ?>
                <?= Html::label('منطقه ۸: ', 'area2_q', ['class' => '']) ?>
                <?= Html::textInput('PcProjects[Area8]', $json['area8'], ['id'=>'area8_q','type'=>'number', 'class'=>"form-control"]); ?>

                <div class="form-group">
                    <br/>
                    <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
                </div>
                <br/>
                <?php ActiveForm::end(); ?>

            </div>




    <img src="<?= Yii::$app->request->baseUrl.'/web/images/update.png'; ?>" style="display: block;width:90%; max-width:200px;height:auto; margin:auto;">

    <br/>
</div>

<?php
$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS
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

