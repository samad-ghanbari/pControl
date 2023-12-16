<?php

/* @var $this yii\web\View */
/* @var  $op1Stat */
/* @var  $op3Stat */
/* @var  $areas */
/* @var $project */
/* @var $phaseNo */
/* @var $searchParams */
/* @var $exchanges */
/* @var $areaSelection */


$this->title = 'PDCP|Statistics';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>

    <div class="topic-cover ">
        <div style="width:100% ; padding: 20px; color:white;">
            <h3 style="text-align: center; color:#fff;">گزارش کلی</h3>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/excel.png'?>" style="height: 100px; width:auto; display: block; margin:auto;">

            <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/report/total",
                    'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:auto;']]); ?>
                <label for="prj-form" style="display: block;text-align:center;">انتخاب پروژه</label>
                <select name="id" onchange="this.form.submit()" style="width: 100%;" class="form-control">
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
        </div>

        <br />
        <?php if($pId > -1)
        { ?>
            <div style="width:95%; margin:auto; margin-top:-50px;z-index: 2; background-color: whitesmoke; border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">

                <br />
                <div style="width: 80%; max-width: 640px;margin:auto;">
                    <?php $form = ActiveForm::begin(['method'=>"post",'action'=>Yii::$app->request->baseUrl.'/report/export_total?id='.$pId, 'options' => ['style' => "direction:rtl;"]]); ?>

                    <div style="float: right; width:50%;margin:10px;max-width: 300px;">
                        <label style='margin-top:10px;width:90%; display: block;'  for="area-input" >منطقه</label>
                        <?= Html::dropDownList('search[area]','', $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px; width:90%;"]); ?>
                    </div>

                    <div style="float: right; width:50%;margin:10px; max-width: 300px;">
                        <label style='margin-top:10px;width:90%; display: block;'  for="center-id" >مرکز اصلی</label>
                        <?= Html::dropDownList('search[exchange_id]', '',[] ,['id'=>'eselect', 'style'=>"height:40px;width:90%;"]); ?>
                    </div>

                    <div style="float: right; width:50%;margin:10px; max-width: 300px;">
                        <label style='margin-top:10px;width:90%; display: block;' for="phase-input" >فاز</label>
                        <?= Html::dropDownList('search[phaseNo]','', [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px; width:90%']); ?>
                    </div>

                    <div style="float: right; width:50%;margin:10px; max-width: 300px;">
                        <label style='margin-top:10px;width:90%; display: block;' for="reptype-input" >نوع گزارش</label>
                        <?= Html::dropDownList('search[repType]','', [1=>'گزارش افقی', 2=>'گزارش عمودی'],['id'=>'repType-input', 'style'=>'height:40px;width:90%;']); ?>
                        <br />
                    </div>

                    <br style="clear: both;" />
                    <br />
                    <button type="submit" class="btn btn-success" style="height:38px; float:left;"><i class="fa fa-search text-white" ></i> تایید </button>
                    <br style="clear: both;" />
                    <?php ActiveForm::end(); ?>
                </div>
                <br />
            </div>
            <?php
        }
        else { ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>
    </div>


<?php
$exchs = json_encode($exchanges);
$script =<<< JS
var exchs = $exchs;
function areaChanged(obj)
{
    var area  = $(obj).val();
    var eselect = $("#eselect");
    $(eselect).empty();
    var exchanges = exchs[area];

    for(var id in exchanges)
        {
            var o = new Option(exchanges[id], id);
            $(eselect).append(o);
        }
    
    $(eselect).val("-1");
}




JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>