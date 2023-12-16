<?php
use yii\helpers\Url;

/* @var $model */
/* @var $url */
/* @var $mng */

$attrUrl = Yii::$app->request->baseUrl.'/project/attributes?pid=';
?>

<div style="
direction:rtl; width:300px;height:auto;
position: relative;
float:left; margin: 10px;">

    <div class="box-shadow-dodgerblue" style="color:white;direction:rtl; width:300px;height:auto;border-radius: 10px;position: relative;">
        <a href="<?= $url.$model['project_id']; ?>" class="projectWidgetDiv1">
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/minidart.png'; ?>"  style=" width:50px; height:50px; margin:auto; position: absolute; top:-20px; right:-20px;">
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= ' پروژه '.$model['project']; ?></p>
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= $model['office']; ?></p>
            <hr style="margin:1px; border-top:1px dotted white;" />
            <p align='center' dir="rtl" style="color:white; height:20px; line-height: 20px; text-align: right;">عنوان قرارداد/پروژه</p>
            <p align='center' dir="rtl" style="color:white; height:60px; line-height: 20px; text-align: center; font-weight: bold;"><?= $model['contract_subject']; ?></p>
            <hr style="margin:1px; border-top:1px dotted white;" />
            <p align='center' dir="rtl" style="color:white; height:20px; line-height: 20px; text-align: right;">شرکت طرف قرارداد</p>
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= $model['contract_company']; ?></p>
            <hr style="margin:1px; border-top:1px dotted white;" />
            <p align='center' dir="rtl" style="color:white; height:20px; line-height: 20px; text-align: right;">تاریخ قرارداد</p>
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= $model['contract_date']; ?></p>
            <hr style="margin:1px; border-top:1px dotted white;" />
            <p align='center' dir="rtl" style="color:white; height:20px; line-height: 20px; text-align: right;">مدت زمان اجرای قرارداد</p>
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= $model['contract_duration']; ?></p>
        </a>
        <hr style="color:white; margin:2px;" />
        <div class="projectWidgetDiv2">
            <a href="<?= $attrUrl.$model['project_id']; ?>" style="display: inline-block;width:100%;">
                <p align='center' dir="rtl" style="color:white; height:50px; line-height: 50px; text-align: center; font-weight: bold;"><?= ' وزن کل پروژه '.$model['project_weight']; ?></p>
                <br />
            </a>
        </div>
    </div>

</div>
