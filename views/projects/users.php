<?php

/* @var $this yii\web\View */
/* @var  $dataProvider */
/* @var  $searchModel */

$this->title = 'pControl|users';
use yii\helpers\Html;
use yii\grid\GridView;
Yii::$app->formatter->nullDisplay = "";

?>


<div class="layout-wrapper  bg-gradient">


    <div class="layout-narrow-panel setting-sidebar">
        <?= \app\components\SidebarWidget::widget(['index'=>3, "projectId"=>-1]); ?>
    </div>

    <div class="layout-wide-panel">
        <i class="fa fa-users fa-2x" style="display: block; text-align:center; margin:10px auto;color:white;" ></i>
        <h3 style="text-align: center;color:white;">مدیریت کاربران </h3>
        <hr style="border:1px solid lightgray;" />

        <p>
            <?= Html::a('افزودن کاربر جدید', ['user_new'], ['class' => 'btn btn-success', 'title'=>"افزودن کاربر جدید"]) ?>
        </p>

        <div id="gwcontainer" style="overflow:auto; background-color:#eee; direction:rtl; height: 100%; font-size: 14px;">
            <i class="fa fa-arrow-right" style="position: fixed; right:0px; color:#28a4c9; cursor: pointer;" onclick="scRight()" ></i>
            <i class="fa fa-arrow-left" style="position:fixed; left:0px; color:#28a4c9; cursor: pointer;" onclick="scLeft()"></i>

            <?= GridView::widget([
                'tableOptions'=>['id'=>"users-Table", 'class'=>'table table-striped table-bordered table-hover text-center'],
                //'headerRowOptions'=>['class'=>'bg-info text-center'],
                'rowOptions' =>function ($model, $key, $index, $grid) {return ['id'=>'row'.$model['id'], 'rec-id'=>$model['id'], 'class'=>'table_row', 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>'modalShow(this.getAttribute("rec-id"));', "oncontextmenu" =>"event.preventDefault();modalShow(this.getAttribute('rec-id'));"];},
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterRowOptions' =>['style'=>"direction:ltr"],
                //        'headerRowOptions'=>['style=>"potision:fixed; top:200px;'],
                'summary' => 'نمایش <b>{begin} تا {end}</b> از <b>{totalCount}</b> ',
                //        'pager'=>['options'=>['align'=>"center", 'class'=>"pagination"]],
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
                    ],
                    //3
                    [
                        'attribute' =>'lastname',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'نام خانوادگی'],
                    ],
                    //۴
                    [
                        'attribute' =>'nid',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'کد ملی'],
                    ],
                    //8
                    [
                        'attribute' =>'employee_code',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'کد مستخدمی'],
                    ],
                    //9
                    
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
                        'attribute' =>'tel',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'شماره تماس'],
                    ],
                    [
                        'attribute' =>'enabled',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'فعال '],
                        'format'=>'html',
                        'value'=>function($data){if($data['enabled'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                    ],
                    [
                        'attribute' =>'admin',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'ادمین سیستم '],
                        'format'=>'html',
                        'value'=>function($data){if($data['admin'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                    ],
                    [
                        'attribute' =>'action_role',
                        'headerOptions' => ['class' => 'bg-info text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'نقش فعالیت '],
                    ],
                ],
            ]);
            ?>
        </div>


    </div>

</div>


<!-- context -->
<div class="box_shadow modal fade" id="contextModal" role="dialog" >
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-body" style="font-size: 14px;">
                <a href="#" style="cursor: pointer; text-align: right;" id="contextModify" class="list-group-item">  ویرایش کاربر  <i class="fa fa-edit fa-lg text-success"></i></a>
                <a href="#" style="cursor: pointer; text-align: right;" id="contextRemove" class="list-group-item">  حذف کاربر  <i class="fa fa-times fa-lg text-danger"></i></a>
            </div>
        </div>
    </div>
</div>
<!-- context -->

<?php
$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS

function activateRow(rowId)
{
$(".selectedRow").removeClass("selectedRow");
$("#"+rowId).addClass("selectedRow");
}

function modalShow(id)
{
  activateRow("row"+id);
  $("#contextModify").attr("href","$bPath/projects/user_edit?id="+id);
  $("#contextRemove").attr("href","$bPath/projects/user_remove?id="+id);
  $('#contextModal').modal('show');
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