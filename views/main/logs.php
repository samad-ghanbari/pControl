<?php

/* @var $this yii\web\View */
/* @var  $dProvider */
/* @var  $sModel */
/* @var  $project */

$this->title = 'pControl|logs';
use yii\helpers\Html;
use yii\grid\GridView;
Yii::$app->formatter->nullDisplay = "";
?>

<i class="fa fa-history fa-2x text-primary" style="display: block; text-align:center; margin:auto;" ></i>
<h3 style="text-align: center;color:mediumvioletred;">تاریخچه تغییرات</h3>
<hr style="border:1px solid lightgray;" />

<div id="historycontainer" style="overflow:auto; direction:rtl; height: 100%; font-size: 14px;">
    <i class="fa fa-arrow-right" style="position: fixed; right:0px; color:mediumvioletred; cursor: pointer;" onclick="scRight()" ></i>
    <i class="fa fa-arrow-left" style="position:fixed; left:0px; color:mediumvioletred; cursor: pointer;" onclick="scLeft()"></i>

    <?= GridView::widget([
        'tableOptions'=>['id'=>"users-Table", 'class'=>'table table-striped table-bordered table-hover text-center'],
        //'headerRowOptions'=>['class'=>'bg-info text-center'],
        'rowOptions' =>function ($model, $key, $index, $grid) {return ['id'=>'row'.$model['id'], 'rec-id'=>$model['id'], 'class'=>'table_row', 'onclick'=>'activateRow(this.getAttribute("id"));'];},
        'dataProvider' => $dProvider,
        'filterModel' => $sModel,
        'filterRowOptions' =>['style'=>"direction:ltr"],
        'summary' => 'نمایش <b>{begin} تا {end}</b> از <b>{totalCount}</b> ',
        'layout' => "{summary}\n{items}\n<div align='center' >{pager}</div>",
        'columns' => [

            [
                'attribute' =>'name',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'نام'],
            ],
            //3
            [
                'attribute' =>'lastname',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'نام خانوادگی'],
            ],
            [
                'attribute' =>'office',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'اداره کل '],
            ],
            [
                'attribute' =>'post',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'سمت'],
            ],
            [
                'attribute' =>'area',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'منطقه'],
            ],
            [
                'attribute' =>'exchange',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'مرکز '],
            ],
            [
                'attribute' =>'site_id',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'شناسه سایت '],
            ],
            [
                'attribute' =>'kv_code',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'کد کافو'],
            ],
            [
                'attribute' =>'action',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'عملیات '],
            ],
            [
                'attribute' =>'ts',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'زمان '],
                'format'=>'html',
                'value'=>function($data){return \app\components\Jdf::jdate("Y/m/d h:i", $data['ts']);}
            ],
            [
                'attribute' =>'project',
                'headerOptions' => ['class' => 'bg-info text-center'],
                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'پروژه '],
            ],

        ],

    ]);

    ?>

</div>




<?php
$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS

function activateRow(rowId)
{
$(".selectedRow").removeClass("selectedRow");
$("#"+rowId).addClass("selectedRow");
}

function scRight()
{
    $("#gwcontainer").animate({scrollLeft: "+=800px"}, "slow");
}

function scLeft()
{
    $("#gwcontainer").animate({scrollLeft: "-=800px"}, "slow");
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>
<br />