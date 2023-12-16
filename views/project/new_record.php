<?php

/* @var $this yii\web\View */
/* @var $model */
/* @var $areas */
/* @var $exchanges */
use yii\bootstrap\ActiveForm;

$this->title = 'PDCP|New Record';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";
$session = Yii::$app->session;
$projectName = "";
if(isset($session['project']))
    $projectName = $session['project']['project'];

?>
    <p class="backicon">
        <a href="index"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
    </p>
    <div class="topic-cover bg-gradient">
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/newexch.png'; ?>" style="width:200px;display:block; margin:5px auto;">
            <h3 style="text-align: center; color:#fff;direction: rtl;"><?= ' پروژه '.$projectName; ?></h3>
    <br />


    <hr style="border-top:1px solid white;"/>
    <?php
    $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl."/project/add_record",
        'id'=>"recForm",
        'options'=>['style'=>'direction:rtl;max-width:500px;min-width:100px; margin:auto']
    ]); ?>

    <?= $form->field($model, 'area', ['labelOptions' => ['style' => 'color:white;'],'options'=>['style'=>'float:right; width: 30%;']])->dropDownList($areas, ['onchange'=>"areaChanged();",'id'=>'areaCB']); ?>
    <?= $form->field($model, 'name', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'float:left; width: 70%;']])->textInput(['maxlength' => true, 'style'=>"direction:rtl"]) ?>

    <?= $form->field($model, 'type', ['labelOptions' => ['style' => 'color:white;'],'options'=>['style'=>'float:right; width: 30%;']])->dropDownList([2=>"مرکز", 3=>"سایت"], ['onchange'=>"typeChanged();",'id'=>'typeCB']); ?>
    <?= $form->field($model, 'center_id', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'float:left; width:70%;', 'class'=>'siteFrame']])->dropDownList($exchanges[$model->area], ['id'=>'centerCB']); ?>


    <?= $form->field($model, 'site_id', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'float:right; width: 33%;', 'class'=>'siteFrame']])->textInput(); ?>
    <?= $form->field($model, 'kv_code', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'float:right; width: 33%;', 'class'=>'siteFrame']])->textInput(); ?>
    <?= $form->field($model, 'phase', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'float:right; width: 33%;', 'class'=>'siteFrame']])->dropDownList([1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10']) ?>

    <?= $form->field($model, 'address', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'clear:both;']])->textarea(['rows'=>2]); ?>
    <?= $form->field($model, 'position', ['labelOptions' => ['style' => 'color:white;'], 'options'=>['style'=>'clear:both;']])->textInput(['maxlength' => true, 'style'=>"direction:ltr"]); ?>

    <div class="form-group" style="clear:both;">
        <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
    </div>
    <?php ActiveForm::end(); ?>

        <br style="clear:both; margin-bottom:50px;" />
    </div>

<?php
$aexJason = json_encode($exchanges);

$script =<<< JS
function areaChanged()
{ 
    var center = $("#centerCB");
    $(center).empty();
    $(center).append('<option value=-1></option>');
    var type = $("#typeCB").val();
    if(type == 3)
        {
            var area = $("#areaCB").val();
            if(area > 0)
                {
                    var json = $aexJason;
                    json = json[area];
                    for(var id in json)
                    {
                        $(center).append('<option value='+id+'>'+json[id]+'</option>');
                    }
              }   
        }
}

function typeChanged()
{
    var type = $("#typeCB").val();
    var center = $("#centerCB");
    $(center).empty();
    if(type == 3)
        {
            var siteFrame = $(".siteFrame").css('display', 'block');
            areaChanged();
        }
    else 
        {
            var siteFrame = $(".siteFrame").css('display', 'none');
        }
}
typeChanged();
JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>