<?php

/* @var $this yii\web\View */
/* @var $projectName */
/* @var $projects */
/* @var $lom */
/* @var $modelArray */ // [ area=>[..] ... ]   >>['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];



$this->title = 'PDCP|Dedication';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>
    <div class="topic-cover bg-gradient enFont">
        <div style="width:100% ; padding: 20px; color:white;">
            <h3 style="text-align: center; color:#fff;">چارت تخصیص تجهیزات</h3>
            <i class="fa fa-chart-pie" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>

            <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/stat/dedication",
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
            <br />
                <?php if( ($pId > -1) && (sizeof($lom) > 0)){ ?>
            <div style="width:100%; margin:auto; display:flex; flex-wrap:wrap; justify-content:center; background-color:rgba(100,100,100,0.5); color:white; border-radius: 10px;padding:10px; direction:ltr;" >
                <h4 style="text-align:center; display:block; width:100%;">تخصیص تجهیزات</h4>
                <?php
                foreach($lom as $lm)
                {
                   echo \app\components\LomWidget::widget(['model'=>$lm, 'area'=>$area]);
                }
                ?>
                <br style="clear:both;" />
            </div>

                <div style="width: 100%; margin:auto;">
                    <?php
                    foreach ($modelArray as $area=>$lom)
                    {
                        if($area > 1) { ?>
                    <div style="width:100%; display:flex; flex-wrap: wrap; justify-items: center; margin-top:30px; background-color:rgba(100,100,100,0.5); color:white; border-radius: 10px;padding:10px; direction:ltr;" >
                    <h2 style="padding:20px;display:block; border-radius:10px;width:90%; height:100px; line-height:60px; margin: 20px auto;color:white; text-align:center;"><?= " منطقه ".$area; ?></h2>
                        <?php } else if($area == 0){?>
                     <div style="width:100%; display:flex; flex-wrap: wrap; justify-items: center; margin-top:30px; background-color:rgba(100,100,100,0.5); color:white; border-radius: 10px;padding:10px; direction:ltr;" >
                         <h2 style="padding:20px;display:block; border-radius:10px;width:90%; height:100px; line-height:60px; margin: 20px auto;color:white; text-align:center;">کلیه مناطق</h2>
                        <?php } ?>

                        <div style="width: 98%; display: flex; justify-items: center; align-content: center;flex-wrap: wrap;">
                        <?php
                        foreach ($lom as $param)
                        {
                        $convasId = $param['convas'];
                        ?>
                        <div style="width:100%; max-width:500px; min-width:300px;margin:20px auto;color:white;">
                            <h4 class="text-center" style="color:white;"><?= $param['equipment']; ?></h4>
                            <canvas style="width:90%; margin:auto;" id="<?= $convasId; ?>" align="center" ></canvas>
                        </div>
                        <?php
                         }
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>


            </div>
            <?php } else { ?>
                    <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
                <?php } ?>

    </div>
    <br />


<?php
$this->registerJsFile(Yii::$app->request->baseUrl.'/web/js/Chart.min.js');
$this->registerJsFile(Yii::$app->request->baseUrl.'/web/js/chartjs-plugin-datalabels.min.js');

$jsonModel = json_encode($modelArray);

$script =<<< JS

var jsonModel = $jsonModel;
var array = [];
var i = 0;
for(area in jsonModel)
    {
        for(lom in jsonModel[area])
            {
                var config = 
                    {
                        type: 'pie',
                        data: {
                            datasets: [{
                                data: jsonModel[area][lom]['data'],
                                backgroundColor: jsonModel[area][lom]['colors'],
                            }],
                            labels: jsonModel[area][lom]['labels']
                        },
                        options: {
                            responsive: true,    
                            legend: {
                                   labels: {
                                     fontColor: '#ffffff',
                                     fontSize: 14
                                  }
                               },  
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
    
        array.push([config,jsonModel[area][lom]['convas'], 'pie'+i ]);
        i = i +1;
            }
    }
// array: config convas pie
window.onload = function()
{
    for(chart in array)
        { 
            var piechart = document.getElementById(array[chart][1]).getContext('2d');
            window.pie = new Chart(piechart, array[chart][0]);
        }
};

JS;

$this->registerJs($script, Yii\web\View::POS_END);
?>