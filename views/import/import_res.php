<?php

/* @var $this yii\web\View */
/* @var $project */
/* @var $project_id */
/* @var $models */

use yii\bootstrap\ActiveForm;

$this->title = 'PDCP|Import Result';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";
?>
    <div class="topic-cover bg-gradient" style="height:300px; z-index:1;min-height: 100px;">
        <div class="box-shadow-dodgerblue" style="width:90%;max-width: 400px; margin:auto; height:150px;background-color: rgba(100,100,100,0.8); padding: 20px; border-radius:10px;">
            <h4 style="text-align: center; color:#fff;direction:rtl;">ورود اطلاعات مطابق گزارش زیر به اتمام رسید.</h4>
            <i class="fa fa-download" style="display: block; text-align:center; color:#fff;width:100%;"></i>
            <br />
            <a href="<?= Yii::$app->request->baseUrl.'/project/index?id='.$project_id; ?>"  style="display: block; margin:auto; text-align: center;color:#fff;"><?=$project; ?></a>
        </div>
    </div>

    <div style="width:95%; margin:auto; margin-top:-90px;z-index: 2; background-color: whitesmoke; border-radius: 10px;padding:10px; min-height:50vh;" class="box-shadow-dark">

            <table class="table table-striped table-bordered table-hover" style="direction: rtl; width:95%; margin:auto;">
                <thead>
                <tr>
                    <th>منطقه</th>
                    <th>نام سایت/مرکز</th>
                    <th>نام اختصار</th>
                    <th>نوع سایت/مرکز</th>
                    <th>نام مرکز اصلی</th>
                    <th>شناسه سایت</th>
                    <th>کد کافو</th>
                    <th>آدرس</th>
                    <th>موقعیت</th>
                    <th>فاز</th>
                    <th>عملیات ورود</th>
                </tr>
                </thead>

                <tbody>
                <?php
                foreach ($models as $model)
                {
                    /* @var $model \app\models\XlsModel */
                    $temp = "<tr>";
                    if(!$model->done)
                        $temp = "<tr style='background-color: lightpink;'>";
                    echo $temp;

                    echo "<td>$model->area</td>";
                    echo "<td>$model->name</td>";
                    echo "<td>$model->abbr</td>";
                    $temp =  "Site";
                    if($model->type == 2)
                        $temp = "center";
                    echo "<td>$temp</td>";

                    echo "<td>$model->center</td>";
                    echo "<td>$model->site_id</td>";
                    echo "<td>$model->kv_code</td>";
                    echo "<td>$model->address</td>";
                    echo "<td>$model->position</td>";
                    echo "<td>$model->phase</td>";

                    $temp ="<i class='fa fa-check' style='color:limegreen;'></i>";
                    if(!$model->done)
                        $temp ="<i class='fa fa-times' style='color:red;'></i>";
                    echo "<td>$temp</td>";

                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
    </div>