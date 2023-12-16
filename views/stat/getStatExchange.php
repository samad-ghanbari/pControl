<?php

/* @var $this yii\web\View */
/* @var  $areas */
/* @var  $exchanges */

$this->title = 'pControl|Area';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
?>

<div style="width: 100%; max-width: 700px; margin: auto;">
    <img src="<?= Yii::$app->request->baseUrl.'/web/images/pie.png'; ?>" style="width:80%; max-width: 100px; margin:auto; display: block;">
    <br />
    <h3 class="text-center text-primary">مرکز مورد نظر خود را انتخاب نمایید</h3>
    <?php $form = ActiveForm::begin(['options' => ['style' => "width:90%; max-width:300px; margin:auto; display:block; direction:rtl;"]]); ?>
        <?= Html::dropDownList('area','منطقه', $areas,['value'=>-1, 'class'=>'form-control','onchange'=>"areaChanged(this)", 'style'=>"color:dodgerblue;"]); ?>
        <?= Html::dropDownList('exchange_id','مرکز',[],['id'=>'eselect', 'class'=>'form-control', 'style'=>"color:dodgerblue;", 'required'=>true]); ?>

    <div class="form-group" style="width:100px;margin:auto;">
        <br />
        <button type="submit" class="btn btn-info"><i class="fa fa-check text-white" ></i> تایید </button>
    </div>
    <?php ActiveForm::end(); ?>
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