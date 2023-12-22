<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcOperations */
/* @var $operation */
/* @var $project */
/* @var $choices */


use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'PDCP| Project Attributes';
?>

<p class="backicon">
    <a href="<?= 'setting?id='.$project['id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">
<h3 style="text-align: center; color:white; direction:rtl;"> ویرایش حالت های انتخاب </h3>
<h4 style="text-align: center; color:white; direction:rtl;"><?= ' پروژه '.$project['project']; ?></h4>
<h4 style="text-align: center; color:white; direction:rtl;"><?= $operation['operation']; ?></h4>

<br >
<p>
    <?= Html::a('افزودن آیتم انتخاب جدید', ['new_choice?id='.$operation['id']], ['class' => 'btn btn-success', 'title'=>"افزودن ویژگی جدید به پروژه"]) ?>
</p>

<?= GridView::widget([
    'tableOptions'=>['id'=>"op-Table",'style'=>"direction:rtl;background-color:#eee;", 'class'=>'table table-striped table-bordered table-hover text-center'],
    'rowOptions' =>function ($model, $key, $index, $grid) {return ['id'=>'op-row'.$model['id'], 'rec-id'=>$model['id'], 'class'=>'table_row', 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>"chDbClicked(this.getAttribute('rec-id') );", "oncontextmenu" =>"event.preventDefault();chModalShow(this.getAttribute('rec-id'));"];},
    'dataProvider' => $choices,
    'summary' => '<b>{begin} - {end}</b> / <b>{totalCount}</b> ',
    'layout' => "{summary}\n{items}\n<div align='center' >{pager}</div>",
    'columns' => [
        //0
        [
            'attribute' =>'id',
            'visible'=>0,
        ],
        [
            'attribute' =>'op_id',
            'visible'=>0,
        ],
        //1
        [
            'attribute' =>'choice',
            'headerOptions' => ['class' => 'bg-info text-center'],
            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'آیتم انتخابی'],
        ],
        [
            'attribute' =>'choice_weight',
            'headerOptions' => ['class' => 'bg-info text-center'],
            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'وزن آیتم انتخابی'],
        ],
        [
            'attribute' =>'default',
            'headerOptions' => ['class' => 'bg-info text-center'],
            'format'=>'html',
            'value'=>function($data){if($data['default'] == true) return '<i class="fa fa-check" style="color:green;"></i>'; else return ""; },
            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'آیتم پیشفرض'],
        ],
    ],

]);

?>

<br />
</div>

<!-- context -->
<div class="box_shadow modal fade" id="chContextModal" role="dialog" >
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-body" style="font-size: 14px;">
                <a href="#" style="cursor: pointer; text-align: right;" id="chContextModify" class="list-group-item">  ویرایش آیتم انتخاب  <i class="fa fa-edit fa-lg text-success"></i></a>
                <a href="#" style="cursor: pointer; text-align: right;" id="chContextRemove" class="list-group-item" onclick="return confirm('آیا از حذف آیتم مطمین هستید؟')">  حذف آیتم انتخاب  <i class="fa fa-times fa-lg text-danger"></i></a>
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

function chModalShow(id)
{
  activateRow("row"+id);
  $("#chContextModify").attr("href","$bPath/projects/edit_choice?id="+id);
  $("#chContextRemove").attr("href","$bPath/projects/remove_choice?id="+id);
  $('#chContextModal').modal('show');
}

function chDbClicked(id)
{
  activateRow("op-row"+id);
  //go to edit
  window.location.href = "$bPath/projects/edit_choice?id="+id;
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>
<br />

