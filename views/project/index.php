<?php

/* @var $this yii\web\View */
/* @var  $pages */
/* @var  $operations */
/* @var $choices */
/* @var  $records */
/* @var $searchParams */
/* @var $project */
/* @var $projects */
/* @var $userProject */
/* @var $totalCount */
/* @var $areaSelection */

$this->title = 'PDCP|Project';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$rw = $userProject['rw'];
Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];


$user = ['name'=>'', 'lastname'=>''];
$sessoin = Yii::$app->session;
if(isset($sessoin['user']))
    $user= $sessoin['user'];
?>
    <i class="fa fa-arrow-up" style="position: fixed; left:0px;top:150px; color:#28a4c9; cursor: pointer;" onclick="scTop()" ></i>
    <i class="fa fa-arrow-down" style="position:fixed; left:0px;top:180px; color:#28a4c9; cursor: pointer;" onclick="scDown()"></i>

    <div class="layout-wrapper bg-gradient">

    <div class="layout-narrow-panel" id="toolbarPanel" style="padding:5px;">
                <!--   collapse    -->
            <button id="collapse-btn" style="border: none; background-color: transparent; width:100%; margin:auto;" title="جمع شدن و باز شدن منو" collapse-val="0" onclick="checkCollapse(this.getAttribute('collapse-val'))">
                <i class="fa  fa-chevron-circle-right" style="color:white; font-size: 32px;"></i>
            </button>

            <?php if($rw == 1){ ?>
                <hr />
                <p>
                    <?= Html::a('<i class="fa fa-plus text-white" ></i><span class="dis-text"> آیتم جدید </span>', ['new_record'], ['style'=>'display:block; margin:auto; width:95%;','class'=>'btn btn-success']) ?>
                </p>
            <?php } ?>

                 <!-- search-->
                <div id="searchDiv" style="min-width: 150px;">
                    <br />
                    <h5 style="text-align: center;color:white;" >جستجوی آیتم</h5>
                    <?php $form = ActiveForm::begin(['method'=>"get", 'layout'=>'horizontal', 'options' => ['style' => "direction:rtl;"]]); ?>
                    <?= Html::hiddenInput('page', 0); ?>
                    <label for="area-input" style="display: block;color:white;" >منطقه</label>
                    <?= Html::dropDownList('search[area]',$searchParams['area'], $areaSelection,['style'=>'height:40px;width:95%; margin:0 5px;', 'id'=>'area-input']); ?>
                    <label for="name-input"  style="display: block; margin-top: 10px;color:white;" >نام مرکز/سایت</label>
                    <?= Html::textInput('search[name]', $searchParams['name'],['id'=>"name-input",'class'=>"enFont", 'style'=>'height:40px; width:95%; margin:0 5px;']); ?>
                    <label for="center-input" style="display: block; margin-top: 10px;color:white;" >مرکز اصلی</label>
                    <?= Html::textInput('search[center_name]', $searchParams['center_name'],['id'=>"center-input", 'style'=>'height:40px; width:95%; margin:0 5px;']); ?>
                    <label for="site-id-input" style="display: block; margin-top: 10px;color:white;"  >شناسه سایت</label>
                    <?= Html::textInput('search[site_id]', $searchParams['site_id'],['id'=>"site-id-input",'class'=>"enFont", 'style'=>'height:40px; width:95%; margin:0 5px;']); ?>
                    <label for="kv-code-input" style="display: block; margin-top: 10px;color:white;" >کد کافو</label>
                    <?= Html::textInput('search[kv_code]', $searchParams['kv_code'],['id'=>"kv-code-input", 'class'=>"enFont",'style'=>'height:40px; width:95%; margin:0 5px;']); ?>
                    <label for="phase-input" style="display: block; margin-top: 10px;color:white;" >فاز</label>
                    <?= Html::dropDownList('search[phaseNo]',$searchParams['phaseNo'], [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:95%;']); ?>
                    <br style="clear:both;" />
                    <br />
                    <button type="submit" class="btn btn-primary" style="height:38px; width:95%;"><i class="fa fa-search text-white" ></i> جستجو </button>
                    <?php ActiveForm::end(); ?>
                </div>
                <br style="clear:both;">
            <hr />
            <a href="<?= Yii::$app->request->baseUrl.'/main/home'; ?>" style="display: block; margin:auto; width: 30%; min-width: 32px;">
            <img style="width:100%;" src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>">
            </a>
        </div>

        
        <div class="layout-wide-panel">

            <div style="width:95%;height:100%; margin:auto;">

                        <?php
                        $form = ActiveForm::begin([
                            'id'=>"projectsForm",
                            'method' => 'GET',
                            'action' => Yii::$app->request->baseUrl."/project/index",
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
                        <?php
                        if(sizeof($records) > 0)
                            echo '<h4 style="text-align:right; direction: rtl;color:white;"> تعداد مورد یافت شده: '.$totalCount.'</h4>';
                        if(empty($records))
                            echo '<h4 style="color:white; text-align:right;direction: rtl;">موردی یافت نشد.</h4>';
                        ?>
                        <?php ActiveForm::end(); ?>

            </div>

            <hr />
            <div style="width:100%; overflow: hidden; height:100%;">
                <?php
                foreach($records as $rec)
                    echo \app\components\RecordWidget::widget(['record'=>$rec, 'operations'=>$operations, 'choices'=>$choices, 'searchParams'=>$searchParams]);
                ?>
                <br style="clear: both;">
                <?= yii\widgets\LinkPager::widget([
                    'pagination' => $pages,
                    'options'=>['style'=>'float:left;margin:10px;', 'class'=>'pagination']
                ]);
                ?>
                <br style="clear: both">
                <br />
            </div>
        </div>

    </div>

<?php
$bPath = Yii::$app->request->baseUrl ;
$script =<<< JS

function checkCollapse(colVal)
{
    if(colVal == 0)
        {
            // collapse
            $("#collapse-btn").attr("collapse-val", 1);
            $("#searchDiv").hide();
            $(".dis-text").hide();
            $("#toolbarPanel").css("width", "80px");
            $("#collapse-btn").html("<i class='fa  fa-chevron-circle-left' style='color:white; font-size: 32px;'></i>");
        }
    else 
        {
            // open
            $("#collapse-btn").attr("collapse-val", 0);
            $("#searchDiv").show();
            $(".dis-text").show();
            $("#toolbarPanel").css("width", "200px");
            $("#collapse-btn").html("<i class='fa  fa-chevron-circle-right' style='color:white; font-size: 32px;'></i>");
        }
}

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