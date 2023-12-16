<?php

/* @var $this yii\web\View */
/* @var $projectName */
/* @var $area */
/* @var $areaSelection */
/* @var $progressInfo */


$this->title = 'PDCP|Statistics';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>
    <div class="topic-cover bg-gradient" style="padding:10px;">
        <h3 style="text-align: center; color:#fff;">ضرایب پیشرفت پروژه</h3>
        <i class="fa fa-percent" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>

        <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
            <?php
            $form = ActiveForm::begin([
                'id'=>"projectsForm",
                'method' => 'GET',
                'action' => Yii::$app->request->baseUrl."/stat/progress",
                'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:auto;']]); ?>
            <label for="prj-form" style="display: block;color:white;text-align:center;">انتخاب پروژه</label>
            <select name="id" onchange="this.form.submit()" style="width: 100%;" class="form-control">
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
        <?php if($pId > -1) { ?>
            <div style="width:95%; margin:auto; background-color: rgba(100,100,100,0.5); border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">

                <!-- search-->
                <?php $form = ActiveForm::begin(['method'=>"post",'action'=>Yii::$app->request->baseUrl.'/stat/progress?id='.$pId, 'layout'=>'horizontal', 'options' => ['style' => "direction:rtl;"]]); ?>

                <label for="area-input" style="color:white;" >منطقه</label>
                <?= Html::dropDownList('search[area]',$area, $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px;"]); ?>

                <label for="phase-input"  style="color:white;">فاز</label>
                <?= Html::dropDownList('search[phase]',$phase, [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100px;']); ?>
                   

                <button type="submit" class="btn btn-success" style="height:38px;"><i class="fa fa-search text-white" ></i> جستجو </button>
                <?php ActiveForm::end(); ?>
                <!--search-->
                <br />
                <?php if(sizeof($progressInfo) > 1){ ?>

                    <table class="table" style="width: 80%; max-width: 500px;margin:auto;">
                        <?php foreach ($progressInfo as $rec){ ?>
                            <tr style="direction: rtl;">
                                <td>
                                    <div class="progress" style="height: 30px;  width: 300px; border:1px solid green; border-radius:0px; margin:0 5px;">
                                        <?php if($rec['percentage'] < 60) echo $rec['percentage']. "%"; ?>
                                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
                                             aria-valuenow="<?= $rec['percentage']; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $rec['percentage']; ?>%">
                                            <?php if($rec['percentage'] >= 60) echo $rec['percentage']. "%"; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:white;"><?= $rec['name']; ?></td>
                            </tr>
                        <?php } ?>
                    </table>

                <?php } else { ?>
                    <h4 style="color:white; text-align: center; direction: rtl;">متاسفانه اطلاعات موجود نیست.</h4>
                    <h5 style="color:white; text-align: center; direction: rtl;">لطفا با ادمین خود در خصوص وزن دهی به پارامترهای پروژه مذاکره نمایید.</h5>
                <?php } ?>
            </div>
        <?php } else { ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>

        </div>
    <br />
