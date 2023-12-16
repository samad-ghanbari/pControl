<?php

/* @var $this yii\web\View */
/* @var  $op1Stat */
/* @var  $op3Stat */
/* @var  $areas */
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
    <div class="topic-cover bg-gradient">
        <div style="width:100% ; padding: 20px; color:white;">
            <h3 style="text-align: center; color:#fff;">نمودارهای آماری</h3>
            <i class="fa fa-chart-pie" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>

            <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/stat/piechart",
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
        <?php if($pId > -1)
        { ?>
            <div style="width:95%; margin:auto; background-color: rgba(100,100,100,0.8); border-radius: 10px;padding:10px;" class="box-shadow-dark">

            <!-- search-->
            <?php $form = ActiveForm::begin(['method'=>"POST",'action'=>Yii::$app->request->baseUrl.'/stat/piechart?id='.$pId, 'layout'=>'horizontal', 'options' => ['style' => "direction:rtl; padding:10px;"]]); ?>

            <label for="area-input"  style="color:white;">منطقه</label>
            <?= Html::dropDownList('search[area]',$searchParams['area'], $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px;"]); ?>

            <label for="center-id"  style="color:white;">مرکز اصلی</label>
            <?= Html::dropDownList('search[exchange_id]', $searchParams['exchange_id'],$exchanges[$searchParams['area']] ,['id'=>'eselect', 'style'=>"height:40px;"]); ?>

            <label for="phase-input"  style="color:white;">فاز</label>
            <?= Html::dropDownList('search[phaseNo]',$searchParams['phaseNo'], [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100px;']); ?>
            <button type="submit" class="btn btn-success" style="height:38px;"><i class="fa fa-search text-white" ></i> جستجو </button>
            <?php ActiveForm::end(); ?>
            <!--search-->

            <br />

                <?php if($searchParams['area'] == -1)
                    echo "<div style='width: 100%; max-width: 700px; background-color:#eee;padding:10px; border-radius:10px; margin: auto;display: block' >";
                else
                    echo "<div style='width: 100%; max-width: 700px; margin: auto;display: none' >";
                ?>
                <h4 class="text-center text-primary">آمار مناطق</h4>
                <canvas id="area-piechart" align="center" ></canvas>
                 <?php echo "</div>";

                 if(empty($op1Stat))
                 { ?>
                    <div style="width: 100%; max-width: 700px; margin: auto;">
                        <h4 class="text-center" style="direction: rtl;color:white;">رکوردی یافت نشد.</h4>
                    </div>
                   <?php
                 } else
                     { ?>
                       <div style="width: 98%; display: flex; justify-items: center; align-content: center;flex-wrap: wrap;">
                    <?php
                    foreach ($op1Stat as $title=>$chart)
                    {
                        $convasId = $chart[3];
                        ?>
                        <div style="width: 48%;margin: 5px auto;background-color: #eee; border-radius:10px;">
                            <h4 class="text-center text-primary"><?= $title; ?></h4>
                            <canvas id="<?= $convasId; ?>" align="center" ></canvas>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                     <?php
                     } ?>
            </div>
            <?php
        }
        else { ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>
    </div>


<?php
$this->registerJsFile(Yii::$app->request->baseUrl.'/web/js/Chart.min.js');
$this->registerJsFile(Yii::$app->request->baseUrl.'/web/js/chartjs-plugin-datalabels.min.js');

$jsonAreas = json_encode($areas);
$jsonop1Stat = json_encode($op1Stat);

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


//areas

var op1Stat = $jsonop1Stat;

var json = $jsonAreas;
var Data = json[0];
var Color = json[1];
var Label = json[2];

var configAreas = {
    type: 'pie',
    data: {
        datasets: [{
            data: Data,
            backgroundColor: Color,
        }],
        labels: Label
    },
    options: {
        responsive: true,      
        plugins:
         {
            datalabels: {
                anchor:'center',
                align:'end',
                color: 'mediumvioletred',   
                backgroundColor: 'white',
                formatter: function(value, context) {
                    return [context.chart.data.labels[context.dataIndex], value + '%'];
                }
            }
        }
    }
};



// operation pie
var array = [];
var i = 0;

for(param in op1Stat)
    {
        var config = 
        {
            type: 'pie',
            data: {
                datasets: [{
                    data: op1Stat[param][0],
                    backgroundColor: op1Stat[param][1],
                }],
                labels: op1Stat[param][2]
            },
            options: {
                responsive: true,      
                plugins:
                 {
                    datalabels: {
                        anchor:'center',
                        align:'end',
                        color: 'mediumvioletred',   
                        backgroundColor: 'white',
                        formatter: function(value, context) {
                            return [context.chart.data.labels[context.dataIndex], value + '%'];
                        }
                    }
                }
            }
        };
    
        array.push([config,op1Stat[param][3], 'pie'+i ]);
        i = i +1;
    }

// array: config convas pie
window.onload = function()
{

    var areaPieChart = document.getElementById('area-piechart').getContext('2d');
    window.areaPie = new Chart(areaPieChart, configAreas);
    
    for(chart in array)
        {
            var piechart = document.getElementById(array[chart][1]).getContext('2d');
            window.pie = new Chart(piechart, array[chart][0]);
        }
};



JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>