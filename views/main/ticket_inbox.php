<?php

/* @var $this yii\web\View */
/* @var $dataProvider */
/* @var $searchModel */
/* @var $admin */

$this->title = 'PDCP|Ticket Inbox';
use yii\helpers\Html;
use yii\grid\GridView;
Yii::$app->formatter->nullDisplay = "";
use yii\widgets\Pjax;

?>
<p class="backicon">
    <a href="<?= Yii::$app->request->baseUrl.'/main/home'; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">
        <h2 style="text-align: center; color:#fff;"> P D C P </h2>
        <h4 style="text-align: center; color:#fff;"> صندوق درخواست های پشتیبانی </h4>
    <div style="width:95%; margin:auto; padding:10px 10px 100px 10px; background-color: #eee; border-radius: 10px; min-height:30vh;" class="box-shadow-dark">
        <br />
        <a href="<?= Yii::$app->request->baseUrl.'/main/ticket_insert'; ?>" class="btn btn-success">درخواست جدید</a>
        <?php
        if(empty($dataProvider))
        {
            echo "<h4 style='color:#721c24; text-align: center; direction:rtl;'>درخواستی در صندوق شما موجود نیست.</h4>";
        }
        else
        {
            Pjax::begin(['id' => 'ticket-inbox', 'enablePushState' => false]);
            echo "<div style='overflow:auto; direction:rtl; height: 100%; font-size: 14px;'>";

            echo GridView::widget([
                'tableOptions'=>['id'=>"users-Table", 'class'=>'table table-striped table-bordered table-hover text-center'],
                'rowOptions' =>function ($model, $key, $index, $grid) use($admin) { $bgColor=""; if(($admin && !$model['read']) || (!$admin && $model['new_reply'])) $bgColor="background-color:lightgreen;";  return ['id'=>'row'.$model['id'], 'rec-id'=>$model['id'], 'class'=>'table_row', 'style'=>$bgColor, 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>'dbClicked(this.getAttribute("rec-id"));'];},
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterRowOptions' =>['style'=>"direction:ltr"],
                'summary' => 'نمایش <b>{begin} تا {end}</b> از <b>{totalCount}</b> ',
                'layout' => "{summary}\n{items}\n<div align='center' >{pager}</div>",
                'columns' => [
                    //0
                    [
                        'attribute' =>'id',
                        'visible'=>0,
                    ],
                    //1
                    [
                        'attribute' =>'name',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'نام'],
                        'visible' => $admin
                    ],
                    //3
                    [
                        'attribute' =>'lastname',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'نام خانوادگی'],
                        'visible' => $admin
                    ],
                    //۴
                    [
                        'attribute' =>'office',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'اداره کل'],
                        'visible' => $admin
                    ],
                    //8
                    [
                        'attribute' =>'project',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'پروژه'],
                    ],
                    //9

                    [
                        'attribute' =>'office',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'اداره کل '],
                    ],
                    [
                        'attribute' =>'ts',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'زمان ثبت'],
                        'value'=>function($data){ return \app\components\Jdf::jdate('Y/m/d H:i', $data['ts']);}
                    ],
                    [
                        'attribute' =>'title',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'عنوان'],
                    ],
                ],

            ]);
            echo "</div>";
            Pjax::end();
        }
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

function dbClicked(id)
{
  activateRow("op-row"+id);
  window.location.href = "$bPath/main/ticket_view?id="+id;
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>


