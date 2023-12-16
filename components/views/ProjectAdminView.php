<?php
use yii\helpers\Url;

/* @var $model */
/* @var $url */
/* @var $mng */
?>

<div style="
direction:rtl; width:300px;height:auto;
position: relative;
float:left; margin: 10px;">

    <div class="hvr-grow box-shadow-dodgerblue" style="color:white;direction:rtl; width:300px;height:auto;border-radius: 10px;position: relative;">
        <a href="<?= $url.$model['project_id']; ?>" style="width:100%;height: 150px;border-radius: 10px; display: inline-block; padding:10px; background-color: #1b6d85;">
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/minidart.png'; ?>"  style=" width:50px; height:50px; margin:auto; position: absolute; top:-20px; right:-20px;">
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= ' پروژه '.$model['project']; ?></p>
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= $model['office']; ?></p>
            <p align='center' dir="rtl" style="color:white; height:30px; line-height: 30px; text-align: center; font-weight: bold;"><?= \app\components\Jdf::jdate('Y/m', $model['ts']); ?></p>
        </a>
    </div>

</div>
