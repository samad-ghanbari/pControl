<?php

/* @var $this yii\web\View */
/* @var  $model */

$this->title = 'pControl|Home';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";
?>

<i class="fas fa-project-diagram fa-2x text-primary" style="display: block; text-align:center; margin:auto;" ></i>
<h3 style="text-align: center;">مدیریت پروژه ها </h3>

<hr style="border:1px solid lightgray;" />
<div style="display: flex; align-items:center; flex-wrap:wrap; justify-content: center;">
    <?php
        foreach ($model as $rec)
        {
//            echo \app\components\ProjectWidget::widget(['model'=>$rec]);
        }

    ?>
</div>
<br />