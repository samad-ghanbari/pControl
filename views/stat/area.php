<?php

/* @var $this yii\web\View */
/* @var  $op1Stat */
/* @var $op3Stat */
/* @var  $area */
/* @var  $projectName */
/* @var $phaseNo */


$this->title = 'pControl|Statistics';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
?>

    <div style="width: 100%; max-width: 700px; margin: auto;">
        <h3 class="text-center text-primary"><?= $projectName; ?></h3>
        <h4 class="text-center text-primary"><?= ' آمار منطقه '.$area; ?></h4>

    </div>

    <hr style="border-top: 1px dotted white;">
    <!-- phase-->
<?php $form = ActiveForm::begin(['layout'=>'horizontal', 'options' => ['style' => "direction:rtl;"]]); ?>
<?= Html::hiddenInput('area', $area); ?>
<?= Html::dropDownList('phaseNo',$phaseNo, [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['style'=>'height:40px;width:100px;']); ?>
    <button type="submit" class="btn btn-success" style="height:38px;"><i class="fa fa-search text-white" ></i> آمار فاز پروژه </button>
<?php ActiveForm::end(); ?>
    <!--phase-->

    <hr style="border-top: 1px dotted white;">

<?php if(empty($op3Stat) || empty($op1Stat))
    echo '<h4 class="text-center text-danger">در این فاز رکوردی ثبت نشده است</h4>';
?>

    <div style="width:80%; margin:auto; background-color: whitesmoke;" >
        <table class="table table-hover table-striped " style="direction: rtl;">
            <?php
            foreach ($op3Stat as $op3)
            {
                echo "<tr>";
                echo "<td>".$op3[0]."</td>";
                echo "<td>".$op3[1]."</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <br />

    <div style="width: 98%; display: flex; justify-items: center; align-content: center;flex-wrap: wrap;">
        <?php
        foreach ($op1Stat as $title=>$chart)
        {
            $convasId = $chart[3];
            ?>
            <div style="width: 48%;margin: 5px auto;background-color: whitesmoke;">
                <h4 class="text-center text-primary"><?= $title; ?></h4>
                <canvas id="<?= $convasId; ?>" align="center" ></canvas>
            </div>
            <?php
        }
        ?>
    </div>


<?php
$this->registerJsFile(Yii::$app->request->baseUrl.'/web/js/Chart.min.js');
$this->registerJsFile(Yii::$app->request->baseUrl.'/web/js/chartjs-plugin-datalabels.min.js');

$jsonop1Stat = json_encode($op1Stat);
$script =<<< JS
var op1Stat = $jsonop1Stat;
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
    for(chart in array)
        {
            var piechart = document.getElementById(array[chart][1]).getContext('2d');
            window.pie = new Chart(piechart, array[chart][0]);
        }
};

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>