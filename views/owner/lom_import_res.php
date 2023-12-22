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
            <a href="<?= Yii::$app->request->baseUrl.'/owner/edit_project?id='.$project_id; ?>"  style="display: block; margin:auto; text-align: center;color:#fff;"><?=$project; ?></a>
        </div>
    </div>

    <div style="width:95%; margin:auto; margin-top:-90px;z-index: 2; background-color: whitesmoke; border-radius: 10px;padding:10px; min-height:50vh;" class="box-shadow-dark">

            <table class="table table-striped table-bordered table-hover" style="direction: rtl; width:95%; margin:auto;">
                <thead>
                <tr>
                    <th>تجهیز</th>
                    <th>توضیحات</th>
                    <th>تعداد کل</th>
                    <th>منطقه ۲</th>
                    <th>منطقه ۳</th>
                    <th>منطقه ۴</th>
                    <th>منطقه ۵</th>
                    <th>منطقه ۶</th>
                    <th>منطقه ۷</th>
                    <th>منطقه ۸</th>
                    <th>عملیات ورود</th>
                </tr>
                </thead>

                <tbody>
                <?php
                foreach ($models as $model)
                {
                    /* @var $model \app\models\XlsLomModel */
                    $temp = "<tr>";
                    if(!$model->done)
                        $temp = "<tr style='background-color: lightpink;'>";
                    echo $temp;

                    echo "<td>$model->equipment</td>";
                    echo "<td>$model->description</td>";
                    echo "<td>$model->quantity</td>";
                    echo "<td>$model->area2</td>";
                    echo "<td>$model->area3</td>";
                    echo "<td>$model->area4</td>";
                    echo "<td>$model->area5</td>";
                    echo "<td>$model->area6</td>";
                    echo "<td>$model->area7</td>";
                    echo "<td>$model->area8</td>";

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