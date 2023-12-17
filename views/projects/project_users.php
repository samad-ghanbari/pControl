<?php

/* @var $this yii\web\View */
/* @var $this yii\web\View */
/* @var  $usersSM */
/* @var  $usersDP */
/* @var  $project \app\models\PcProjects */
/* @var  $projects \app\models\PcProjects */


$this->title = 'PDCP|projects details';
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$this->registerCssFile(Yii::$app->request->baseUrl.'/web/css/sidebar.css');
?>


<div class="layout-wrapper bg-gradient">

        <div class="layout-narrow-panel setting-sidebar">
            <?= \app\components\SidebarWidget::widget(['index'=>4, "projectId"=>$pId]); ?>
        </div>


        <div class="layout-wide-panel">

            <div style="width:95%; margin:auto;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/projects/project_users",
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

                    <i class="fa fa-users fa-2x " style="color:white; display: block; text-align:center; margin:auto;" ></i>
                    <h3 style="text-align: center;color:white;"><?= ' کابران پروژه '.$project['project']; ?></h3>
                    <h4 style="text-align: center;color:white;"><?= $project['office']; ?></h4>

                    <p>
                        <?= Html::a('افزودن کاربر جدید', ['new_user'], ['class' => 'btn btn-success', 'title'=>"افزودن کاربر جدید به پروژه"]) ?>
                        <?= Html::a('ویرایش اطلاعات پایه', ['site_edit'], ['class' => 'btn btn-warning', 'title'=>"دسترسی ویرایش اطلاعات پایه سایت"]) ?>
                    </p>

                    <?php Pjax::begin(['id'=>'pj-up', 'enablePushState' => false]); ?>

                    <?= GridView::widget([
                        'tableOptions'=>['id'=>"users-Table",'style'=>"direction:rtl;background-color:#eee;", 'class'=>'table table-striped table-bordered table-hover text-center'],
                        //'headerRowOptions'=>['class'=>'bg-info text-center'],
                        'rowOptions' =>function ($model, $key, $index, $grid) {return ['id'=>'row'.$model['id'], 'rec-id'=>$model['id'], 'class'=>'table_row', 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>'userModalShow(this.getAttribute("rec-id"));', "oncontextmenu" =>"event.preventDefault();userModalShow(this.getAttribute('rec-id'));"];},
                        'dataProvider' => $usersDP,
                        'filterModel' => $usersSM,
                        'filterRowOptions' =>['style'=>"direction:ltr"],
                        'summary' => "<b style='color:white;'>{begin} - {end} / {totalCount}</b> ",
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
                            //9

                            [
                                'attribute' =>'user_office',
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
                                'format'=>'html',
                                'value'=>function($data){if(empty($data['area'])) return '<i class="fa fa-certificate" style="color:goldenrod;"></i>'; else return $data['area'];},
                                'headerOptions' => ['class' => 'bg-info text-center'],
                                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'منطقه'],
                            ],
                            [
                                'attribute' =>'exchange',
                                'format'=>'html',
                                'value'=>function($data){if(empty($data['exchange_id'])) return '<i class="fa fa-certificate" style="color:goldenrod;"></i>'; else return $data['exchange'];},
                                'headerOptions' => ['class' => 'bg-info text-center'],
                                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'مرکز'],
                            ],
                            [
                                'attribute' =>'rw',
                                'headerOptions' => ['class' => 'bg-info text-center'],
                                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'نوع دسترسی '],
                                'format'=>'html',
                                'value'=>function($data){if($data['rw'] == 1) return 'ویرایش'; else return 'مشاهده';}
                            ],
                            [
                                'attribute' =>'site_editable',
                                'headerOptions' => ['class' => 'bg-info text-center'],
                                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'ویرایش اطلاعات پایه'],
                                'format'=>'html',
                                'value'=>function($data){if($data['site_editable'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                            ],
                            [
                                'attribute' =>'enabled',
                                'headerOptions' => ['class' => 'bg-info text-center'],
                                'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'فعال '],
                                'format'=>'html',
                                'value'=>function($data){if($data['enabled'] == true) return '<i class="fa fa-check text-success"></i>'; else return '<i class="fa fa-times text-danger"></i>';}
                            ],
                        ],

                    ]);

                    ?>
                    <?php Pjax::end(); ?>
                    <br />


                    <!-- context -->
                    <div class="box_shadow modal fade" id="userContextModal" role="dialog" >
                        <div class="modal-dialog modal-sm ">
                            <div class="modal-content">
                                <div class="modal-body" style="font-size: 14px;">
                                    <a href="#" style="cursor: pointer; text-align: right;" id="userContextModify" class="list-group-item">  ویرایش دسترسی کاربر  <i class="fa fa-edit fa-lg text-success"></i></a>
                                    <a href="#" style="cursor: pointer; text-align: right;" id="userContextRemove" class="list-group-item">  حذف کاربر از پروژه  <i class="fa fa-times fa-lg text-danger"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- context -->

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

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>