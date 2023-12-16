<?php

/* @var $this yii\web\View */
/* @var  $model */
/* @var  $url */

$this->title = 'PDCP|projects';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";
?>

<i class="fa fa-project-diagram fa-2x text-primary" style="display: block; text-align:center; margin:auto;" ></i>
<h3 style="text-align: center;">مدیریت پروژه ها </h3>
<hr style="border:1px solid lightgray;" />

<p>
    <?= Html::a('افزودن پروژه جدید', ['new_project'], ['class' => 'btn btn-success', 'title'=>"افزودن پروژه جدید"]) ?>
</p>

<div style="display: flex; align-items:center; flex-wrap:wrap; justify-content: center;">
    <?php
    foreach ($model as $rec)
    {
        $rec['project_id'] = $rec['id'];
        echo \app\components\ProjectAdminWidget::widget(['model'=>$rec, 'url'=>$url, 'mng'=>1]);
    }

    ?>
</div>
<br />
