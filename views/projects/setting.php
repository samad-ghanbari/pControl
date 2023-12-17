<?php

/* @var $this yii\web\View */
/* @var $this yii\web\View */
/* @var  $project \app\models\PcProjects */
/* @var  $projects \app\models\PcProjects */
/* @var  $operationsDP */
/* @var  $opType */
/* @var  $colors */
/* @var  $project_weight */


$this->title = 'PDCP|setting';
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>

<div class="layout-wrapper bg-gradient">

    <div class="layout-narrow-panel setting-sidebar">
        <?= \app\components\SidebarWidget::widget(['index'=>2, "projectId"=>$pId]); ?>
    </div>


    <div class="layout-wide-panel">

        <div style="width:95%; margin:auto;">
            <?php
            $form = ActiveForm::begin([
                'id'=>"projectsForm",
                'method' => 'GET',
                'action' => Yii::$app->request->baseUrl."/projects/setting",
                'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:10px auto;']]); ?>
            <label for="prj-form" style="display: block;color:white;">انتخاب پروژه</label>
            <select name="id" onchange="this.form.submit()" style="width: 300px;" class="form-control">
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

        <?php
        if($pId > -1)
        {
            ?>
            <div style="width:95%; margin:auto;">
                <i class="fa fa-cogs fa-2x " style="color:mediumvioletred; display: block; text-align:center; margin:auto;" ></i>
                <h3 style="text-align: center;color:white; direction:rtl;"><?= ' ویژگی های پروژه '.$project['project']; ?></h3>
                <h4 style="text-align: center;color:white;"><?= $project['office']; ?></h4>

                <a href="<?= Yii::$app->request->baseUrl.'/project/update_weight?id='.$project['id']; ?>" style="display:block; background-color:#888;padding:10px; width:200px;margin:auto; border-radius:5px; color:#fff;font-size:18px;text-align:center;" >
                    <i class="fa fa-2x fa-sync" style="color:lightgreen;"></i>
                    <br />
                    بروزرسانی وزن‌ها
                </a>

                <hr style="border:1px dotted lightgray;" />

                <table class="table table-striped" style="width: 500px; margin:auto; background-color: #fff; direction: rtl;">
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

                <p>
                    <?= Html::a('افزودن ویژگی جدید', ['new_op'], ['class' => 'btn btn-success', 'title'=>"افزودن ویژگی جدید به پروژه"]) ?>
                </p>
                <h3 style="text-align: center;color:white; direction: rtl;"><?= ' وزن کل پروژه '.$project_weight; ?></h3>

                <?php Pjax::begin(['id'=>'pj-op', 'enablePushState' => false]); ?>
                <?= GridView::widget([
                    'tableOptions'=>['id'=>"op-Table",'style'=>"direction:rtl; background-color:#eee;", 'class'=>'table table-striped table-bordered table-hover text-center'],
                    'rowOptions' =>function ($model, $key, $index, $grid) use ($colors)
                    {
                        $color = $colors[$model['id']];
                        return ['id'=>'op-row'.$model['id'], 'rec-id'=>$model['id'], 'type-id'=>$model['type_id'], 'class'=>'table_row', 'style'=>$color, 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>"opDbClicked(this.getAttribute('rec-id'), this.getAttribute('type-id') );", "oncontextmenu" =>"event.preventDefault();opModalShow(this.getAttribute('rec-id'), this.getAttribute('type-id'));"];
                    },
                    'dataProvider' => $operationsDP,
                    'summary' => "<b style='color:white;' >{begin} - {end} / {totalCount}</b> ",
                    //'summaryOptions'=>['style'=>"color:white;"],
                    'layout' => "{summary}\n{items}\n<div align='center' >{pager}</div>",
                    'columns' => [
                        //0
                        [
                            'attribute' =>'id',
                            'visible'=>0,
                        ],
                        [
                            'attribute' =>'project_id',
                            'visible'=>0,
                        ],
                        //1
                        [
                            'attribute' =>'operation',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:50px;", 'title'=>'ویژگی پروژه'],
                        ],
                        //3
                        [
                            'attribute' =>'type_id',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'value'=>function($data)use($opType){return $opType[$data['type_id']]; },
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'نوع داده'],
                        ],
                        [
                            'attribute' =>'op_weight',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'وزن ویژگی'],
                        ],
                        //9

                        [
                            'attribute' =>'priority',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'اولویت'],
                        ],

                        [
                            'attribute' =>'design_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه طراحی'],
                            'format'=>'html',
                            'value'=>function($data){if($data['design_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],

                        [
                            'attribute' =>'install_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه نصب'],
                            'format'=>'html',
                            'value'=>function($data){if($data['install_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],

                        [
                            'attribute' =>'operation_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه عملیات'],
                            'format'=>'html',
                            'value'=>function($data){if($data['operation_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],

                        [
                            'attribute' =>'test_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه نظارت'],
                            'format'=>'html',
                            'value'=>function($data){if($data['test_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],


                        [
                            'attribute' =>'district_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه منطقه'],
                            'format'=>'html',
                            'value'=>function($data){if($data['district_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],

                        [
                            'attribute' =>'it_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه فناوری'],
                            'format'=>'html',
                            'value'=>function($data){if($data['it_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],

                        [
                            'attribute' =>'planning_role',
                            'headerOptions' => ['class' => 'bg-info text-center'],
                            'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'حیطه برنامه‌ریزی'],
                            'format'=>'html',
                            'value'=>function($data){if($data['planning_role'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                        ],
                    ],

                ]);

                ?>

                <?php Pjax::end(); ?>

                <br />

            </div>
            <?php
        }
        else
        {
            // no selected
            ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
            <?php
        }
        ?>

    </div>


</div>

<!-- context -->
<div class="box_shadow modal fade" id="opContextModal" role="dialog" >
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-body" style="font-size: 14px;">
                <a href="#" style="cursor: pointer; text-align: right;" id="opContextModify" class="list-group-item">  ویرایش ویژگی  <i class="fa fa-edit fa-lg text-success"></i></a>
                <a href="#" style="cursor: pointer; text-align: right;" id="opContextChoices" class="list-group-item">  ویرایش حالت های انتخاب  <i class="fa fa-bars fa-lg text-primary"></i></a>
                <a href="#" style="cursor: pointer; text-align: right;" id="opContextRemove" class="list-group-item" onclick="return confirm('آیا از حذف آیتم مطمین هستید؟')">  حذف ویژگی  <i class="fa fa-times fa-lg text-danger"></i></a>
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

function userModalShow(id)
{
  activateRow("row"+id);
  $("#userContextModify").attr("href","$bPath/projects/edit_user_project?id="+id);
  $("#userContextRemove").attr("href","$bPath/projects/remove_user_project?id="+id);
  $('#userContextModal').modal('show');
}

function opModalShow(id, type_id)
{
  activateRow("op-row"+id);
  $("#opContextModify").attr("href","$bPath/projects/edit_project_op?id="+id);
  $("#opContextRemove").attr("href","$bPath/projects/remove_project_op?id="+id);
  if(type_id == 1)
      {
          $("#opContextChoices").show();
          $("#opContextChoices").attr("href","$bPath/projects/edit_op_choices?id="+id);
      }
  else 
      $("#opContextChoices").hide();
  
  $('#opContextModal').modal('show');
}

function opDbClicked(id, type_id)
{
  activateRow("op-row"+id);
  if(type_id == 1)
      {
          // goto choices
          window.location.href = "$bPath/projects/edit_op_choices?id="+id;
      }
  else 
      {
          //go to edit
          window.location.href = "$bPath/projects/edit_project_op?id="+id;
      }
}

JS;
    $this->registerJs($script, Yii\web\View::POS_END);
    ?>


