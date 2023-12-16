<?php

/* @var $this yii\web\View */
/* @var  $pages */
/* @var  $operations */
/* @var $choices */
/* @var  $records */
/* @var $searchParams */
/* @var $project */
/* @var $userProject */
/* @var $totalCount */
/* @var $areaSelection */

$this->title = 'PDCP|Project';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$rw = $userProject['rw'];
Yii::$app->formatter->nullDisplay = "";

$user = ['name'=>'', 'lastname'=>''];
$sessoin = Yii::$app->session;
if(isset($sessoin['user']))
    $user= $sessoin['user'];
?>
    <div id="project-index">

        <div style="width:95%; margin:auto; margin-top:0px;z-index: 2; background-color: whitesmoke; border-radius: 10px;padding:10px;" class="box-shadow-dark">
            <!-- search-->
            <?php $form = ActiveForm::begin(['method'=>"get", 'layout'=>'horizontal', 'options' => ['style' => "direction:rtl;"]]); ?>

            <?= Html::hiddenInput('page', 0); ?>


            <label for="area-input" >منطقه</label>
            <?= Html::dropDownList('search[area]',$searchParams['area'], $areaSelection,['style'=>'height:40px;width:100px; margin:0 5px;', 'id'=>'area-input']); ?>

            <label for="name-input" >نام مرکز/سایت</label>
            <?= Html::textInput('search[name]', $searchParams['name'],['id'=>"name-input", 'style'=>'height:40px; width:150px; margin:0 5px;']); ?>

            <label for="center-input" >مرکز اصلی</label>
            <?= Html::textInput('search[center_name]', $searchParams['center_name'],['id'=>"center-input", 'style'=>'height:40px; width:150px; margin:0 5px;']); ?>

            <label for="site-id-input"  >شناسه سایت</label>
            <?= Html::textInput('search[site_id]', $searchParams['site_id'],['id'=>"site-id-input", 'style'=>'height:40px; width:100px; margin:0 5px;']); ?>

            <label for="kv-code-input" >کد کافو</label>
            <?= Html::textInput('search[kv_code]', $searchParams['kv_code'],['id'=>"kv-code-input", 'style'=>'height:40px; width:100px; margin:0 5px;']); ?>

            <label for="phase-input" >فاز</label>
            <?= Html::dropDownList('search[phaseNo]',$searchParams['phaseNo'], [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100px;']); ?>
            <button type="submit" class="btn btn-primary" style="height:38px;"><i class="fa fa-search text-white" ></i> جستجو </button>

            <?php ActiveForm::end(); ?>
            <!--search-->
            <hr style="border-top: 1px dotted white; margin: 2px;">
<div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
            <?php
            if(sizeof($records) > 0)
                echo '<h4 style="direction: rtl;color:mediumvioletred;"> تعداد مورد یافت شده: '.$totalCount.'</h4>';
            if(empty($records))
                echo '<h4 class="text-danger" style="direction: rtl;">موردی یافت نشد.</h4>';

            ?>

            <h4 style="color:#1b6d85;direction: rtl;"><?= ' پروژه '.$project['project']; ?></h4>
</div>
<?php if($rw == 1){ ?>
           <p>
             <?= Html::a(' افزودن مرکز / سایت <i class="fa fa-plus text-white" ></i>', ['new_record'], ['style'=>'display:block; margin:auto; width:170px;','class'=>'btn btn-success']) ?>
           </p>
<?php } ?>



            <i class="fa fa-arrow-up" style="position: fixed; left:0px;top:150px; color:#28a4c9; cursor: pointer;" onclick="scTop()" ></i>
            <i class="fa fa-arrow-down" style="position:fixed; left:0px;top:180px; color:#28a4c9; cursor: pointer;" onclick="scDown()"></i>

            <i class="fa fa-arrow-up" style="position: fixed; right:0px;top:150px; color:#28a4c9; cursor: pointer;" onclick="scTop()" ></i>
            <i class="fa fa-arrow-down" style="position:fixed; right:0px;top:180px; color:#28a4c9; cursor: pointer;" onclick="scDown()"></i>
       <br style="clear:both;">
        </div>

        <div style="width:100%;">
            <?php
            foreach($records as $rec)
            {
                echo \app\components\RecordWidget::widget(['record'=>$rec, 'operations'=>$operations, 'choices'=>$choices, 'searchParams'=>$searchParams]);
            }

            ?>

            <br style="clear: both;">
            <?= yii\widgets\LinkPager::widget([
                'pagination' => $pages,
                'options'=>['style'=>'float:left;', 'class'=>'pagination']
            ]);

            ?>

            <br style="clear: both">
            <br />
        </div>
    </div>
<?php
$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS

function scTop()
{
$("html, body").animate({scrollTop: "-=400px"}, "slow");
}

function scDown()
{
$("html, body").animate({scrollTop: "+=400px"}, "slow");
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>