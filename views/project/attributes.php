<?php

/* @var $this yii\web\View */
/* @var $this yii\web\View */
/* @var  $project \app\models\PcProjects */
/* @var  $operationsDP */
/* @var  $opType */
/* @var  $colors */
/* @var  $project_weight */


$this->title = 'PDCP|Attributes';
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\grid\GridView;
use yii\widgets\Pjax;

Yii::$app->formatter->nullDisplay = "";
?>
<div class="backicon">
    <a style="display: inline-block;" href="<?= Yii::$app->request->baseUrl.'/main/home'; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</div>

<div class="topic-cover bg-gradient">
<i class="fa fa-project-diagram fa-2x text-primary" style="display: block; text-align:center; margin:auto;" ></i>
<h3 style="text-align: center; direction: rtl;color:white;"><?= ' ویژگی های پروژه '.$project['project']; ?></h3>
<h4 style="text-align: center; direction: rtl;color:white;"><?= $project['office']; ?></h4>
<hr style="border:1px dotted lightgray;" />

    <a href="<?= Yii::$app->request->baseUrl.'/project/update_weight?id='.$project['id']; ?>" style="display:block; background-color:#888;padding:10px; width:200px;margin:auto; border-radius:5px; color:#fff;font-size:18px;text-align:center;" >
        <i class="fa fa-2x fa-sync" style="color:lightgreen;"></i>
        <br />
        بروزرسانی وزن‌ها
    </a>


<div style="width: 80%; max-width: 700px; margin: auto;">
<h4 style="text-align: center;color:white; direction: rtl;"><?= ' وزن کل پروژه '.$project_weight; ?></h4>

    <table class="table table-striped" style="width: 500px; margin:auto; background-color: #eee; direction: rtl;">
        <tr>
            <td>رنگ نوشته</td>
            <td style="background-color: darkred;width:20px;"></td>
            <td>ویژگی انتخابی دارای مقدار پیش فرض نمی باشد.</td>
        </tr>

        <tr>
            <td>رنگ پس زمینه</td>
            <td style="background-color: lightgreen; width:20px;"></td>
            <td>ویژگی دارای وزن معتبر است.</td>
        </tr>

        <tr>
            <td>رنگ پس زمینه</td>
            <td style="background-color: orange;width:20px;"></td>
            <td>ویژگی دارای وزن نامعتبر است.</td>
        </tr>

    </table>
<?= GridView::widget([
    'tableOptions'=>['id'=>"op-Table",'style'=>"direction:rtl;background-color:#eee;", 'class'=>'table table-striped table-bordered table-hover text-center'],
    'rowOptions' =>function ($model, $key, $index, $grid) use ($colors)
    {
        $color = $colors[$model['id']];
        return ['id'=>'op-row'.$model['id'], 'rec-id'=>$model['id'], 'type-id'=>$model['type_id'], 'class'=>'table_row', 'style'=>$color, 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>"opDbClicked(this.getAttribute('rec-id'), this.getAttribute('type-id') );", "oncontextmenu" =>"event.preventDefault();opModalShow(this.getAttribute('rec-id'), this.getAttribute('type-id'));"];
    },
    'dataProvider' => $operationsDP,
    'summary' => '<b>{begin} - {end}</b> / <b>{totalCount}</b> ',
    'layout' => "{summary}\n{items}\n<div align='center' >{pager}</div>",
    'columns' => [
        [
            'attribute' =>'operation',
            'headerOptions' => ['class' => 'bg-info text-center'],
            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'ویژگی پروژه'],
        ],
        [
            'attribute' =>'op_weight',
            'headerOptions' => ['class' => 'bg-info text-center'],
            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'وزن ویژگی'],
        ],

    ],

]);
?>
</div>
</div>
<?php
$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS

function activateRow(rowId)
{
$(".selectedRow").removeClass("selectedRow");
$("#"+rowId).addClass("selectedRow");
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>


