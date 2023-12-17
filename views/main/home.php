<?php

/* @var $this yii\web\View */
/* @var  $userProjects */
/* @var  $notiPages */
/* @var  $bulletin */
/* @var  $admin */

$this->title = 'PDCP|Home';
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
Yii::$app->formatter->nullDisplay = "";


Yii::$app->formatter->nullDisplay = "";
$user = ['name'=>'', 'lastname'=>''];
$sessoin = Yii::$app->session;
if(isset($sessoin['user']))
    $user= $sessoin['user'];

?>

    <div class="topic-cover bg-gradient" >
        <div class="layout-wrapper">
            <div class="layout-wide-dim-panel">
                <img style="width: 80px; height:auto; display:block; margin:auto;" src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>">
                <h3 style="text-align: center; color:white;">پروژه های من</h3>
                <hr />
                <br />
                <!-- flex-wrap:wrap;  -->
                <!-- <div style="display: flex;flex-direction:column; align-items:center; justify-content: center; overflow: hidden; margin-bottom: 50px;"> -->
                <div id="gwcontainer" style="overflow:auto; background-color:#fff; direction:rtl; height: 100%; font-size: 14px;">

                    <?php
                    $url = Yii::$app->request->baseUrl.'/project/index?id=';
                    if(empty($searchModel))
                    {
                        echo "<h3 style='color:#721c24; text-align: center; direction:rtl;'>پروژه ای برای شما تعریف نشده است.</h3>";
                    }
                    else
                    {
                        echo GridView::widget([
                            'tableOptions'=>['id'=>"users-Table", 'class'=>'table table-striped table-bordered table-hover text-center '],
                            //'headerRowOptions'=>['class'=>'bg-info text-center'],
                            'rowOptions' =>function ($model, $key, $index, $grid) {return ['id'=>'row'.$model['project_id'], 'project-id'=>$model['project_id'], 'class'=>'table_row', 'style'=>"cursor:pointer", 'onclick'=>'activateRow(this.getAttribute("id"));', 'ondblclick'=>'showProject(this.getAttribute("project-id"));'];},
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'filterRowOptions' =>['style'=>"direction:ltr"],
                            //'summary' => 'نمایش <b>{begin} تا {end}</b> از <b>{totalCount}</b> ',
                            'summary'=>"",
                            //        'pager'=>['options'=>['align'=>"center", 'class'=>"pagination"]],
                            'layout' => "{summary}\n{items}\n<div align='center' >{pager}</div>",
                            'columns' => [
                                //'project_id, project, office, ts, project_weight, contract_subject, contract_company, contract_date, contract_duration'
                                //0
                                [
                                    'attribute' =>'project_id',
                                    'visible'=>0,
                                ],
                                //1
                                [
                                    'attribute' =>'project',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center text-success', 'style'=>"vertical-align: middle;min-width:150px;font-size:16px;", 'title'=>'پروژه'],
                                ],
                                //3
                                [
                                    'attribute' =>'office',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'اداره کل'],
                                ],
                                //۴
                                [
                                    'attribute' =>'ts',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'زمان'],
                                    'visible'=>0,
                                ],
                                //8
                                [
                                    'attribute' =>'project_weight',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'وزن پروژه'],
                                    'visible'=>0
                                ],
                                //9
                                
                                [
                                    'attribute' =>'contract_subject',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'موضوع قرارداد'],
                                ],
                                [
                                    'attribute' =>'contract_company',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'شرکت طرف قرارداد'],
                                ],
                                [
                                    'attribute' =>'contract_date',
                                    'headerOptions' => ['class' => 'bg-success text-center', 'style'=>'height:80px; line-height:80px;'],
                                    'contentOptions' => ['class' => 'text-center', 'style'=>"vertical-align: middle;min-width:80px;", 'title'=>'تاریخ عقد قرارداد'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{project_detail}',
                                    'header'=>"پارامترهای پروژه",
                                    'headerOptions' => ['class' => 'bg-success text-center text-info', 'style'=>'height:80px; line-height:80px;'],
                                    'buttons' => ['project_detail' => function($url, $model, $key){ return "<a href=\"$url\"><i class='fa fa-info-circle text-info'></i></a>";}],
                                    'urlCreator' => function ($action, $model, $key, $index)
                                        {
                                            $url = Yii::$app->request->baseUrl.'/project/attributes?pid='.$model->project_id;
                                                return $url;
                                        }
                                ],
                            ],
                        ]);
                        
                    }
                    ?>

                    <br style="clear:both;" />
                </div>
            </div>
        </div>
    </div>


    <?php
$bPath = Yii::$app->request->baseUrl ;
$url = Yii::$app->request->baseUrl.'/project/index?id=';

$script =<<< JS

function activateRow(rowId)
{
    $(".selectedRowHome").removeClass("selectedRowHome");
    $("#"+rowId).addClass("selectedRowHome");
}

function showProject(id)
{
    activateRow("row"+id);
    //go to project-id
    let url = "$url"+id;
    window.location.href = url;
}


JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>