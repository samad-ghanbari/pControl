<?php

/* @var $this yii\web\View */
/* @var $bulletin \app\models\PcBulletin */
/* @var $admin */



$this->title = 'PDCP|Bulletin';
use yii\helpers\Html;
?>
<div class="topic-cover bg-gradient" style="width:95%; margin:10px auto;padding:10px;">
    <br />
    <!--  bulletin  -->
    <div class="box-shadow-dark" style="width: 200px; margin:10px auto; background-color: <?= $bulletin->panel_color; ?>; color:#fff; height: 50px; text-align: center; font-weight: bold; line-height: 50px; ">
        <?= \app\components\Jdf::jdate("Y/m/d", $bulletin->ts); ?>
    </div>
    <br />
    <h4 style="color: <?= $bulletin->panel_color; ?>; text-align: center; direction: rtl; margin: 10px auto;"><?= $bulletin->title; ?></h4>
    <hr style="border-top:2px solid <?= $bulletin->panel_color; ?>">
    <div style="direction: rtl; text-align: justify; padding: 10px; color:white; margin:10px auto; width: 100%; ">
        <?= $bulletin->description; ?>
    </div>
</div>
