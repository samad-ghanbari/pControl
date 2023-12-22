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

$this->title = 'PDCP| user Project';
?>

<p class="backicon">
    <a href="<?= 'project_users?id='.$project['id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">

<h4 style="text-align: center; color:white; direction:rtl;"><?= ' افزودن کاربر جدید به پروژه '.$project['project']; ?></h4>
<br >
<div class="row" style="width:100%;">
    <div class="col-md-8">
        <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
            <?php $form = ActiveForm::begin(['options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']]); ?>

            <?= $form->field($model, 'user_id', ['labelOptions'=>['style'=>'color:white;']])->dropDownList($users,['prompt'=>"انتخاب کاربر"]); ?>

            <?= $form->field($model, 'area', ['labelOptions'=>['style'=>'color:white;']])->dropDownList($areas,['value'=>-1,'onchange'=>"areaChanged(this)"]); ?>
            <?= $form->field($model, 'exchange_id', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([], ['id'=>'eselect', 'value'=>-1]); ?>
            <?= $form->field($model, 'rw', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'دسترسی مشاهده' , 1=>'دسترسی ویرایش']); ?>
            <?= $form->field($model, 'site_editable', ['labelOptions'=>['style'=>'color:white;']])->dropDownList([0=>'عدم امکان ویرایش' , 1=>'قابلیت ویرایش']); ?>

            <div class="form-group">
                <br/><br/><?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
            </div>
            <br/><br/>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
    <div class="col-md-4">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/user.png'; ?>" style="display: block;width:90%; max-width:300px;height:auto; margin:auto;">
    </div>
</div>
<br/>
</div>

<?php
$json = json_encode($exchanges);
$script =<<< JS
var json = $json;
function areaChanged(obj)
{
    var area  = $(obj).val();
    var eselect = $("#eselect");
    $(eselect).empty();
    var exchanges = json[area];

    for(var id in exchanges)
        {
            var o = new Option(exchanges[id], id);
            $(eselect).append(o);
        }
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>


