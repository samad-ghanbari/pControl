<?php

/* @var $this yii\web\View */
/* @var  $notiPages */
/* @var  $bulletin */
/* @var  $admin */

$this->title = 'PDCP|Notification';
use yii\helpers\Html;
use yii\widgets\Pjax;
Yii::$app->formatter->nullDisplay = "";
?>

<p class="backicon">
    <a href="home"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>

    <div class="topic-cover bg-gradient" style="padding:10px;">
            <h2 style="text-align: center; color:#fff;"> P D C P </h2>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/alarm.png'?>" style="width:48px; height:auto; display: block; margin:auto;">
            <h3 style="text-align: center; color:#fff;"> اعلانات </h3>
        <?php Pjax::begin(['id'=>'pj-noti', 'enablePushState' => false]); ?>

        <?php
        if(isset($user['admin']))
        {
            if($user['admin'] == true)
            {
                echo "<p>";
                echo Html::a('افزودن اعلان جدید', ['new_notification'], ['class' => 'btn btn-success', 'title'=>"افزودن اعلان جدید"]);
                echo "</p>";
            }
        }
        ?>
        <?php foreach ($bulletin as $bull){ $url = Yii::$app->request->baseUrl.'/main/bulletin_view?id='.$bull['id']; ?>
            <!--    -->

            <div class="bulletin-item" style="width: 80%; max-width: 1000px;margin:0px auto; border-right:5px solid <?= $bull['panel_color'];?>; ">
                <a href="javascript:void(0);" onclick="myPopup('<?= $url; ?>', 'اعلان',700, 400)" style="width: 100%;direction: rtl; height: 100%; display: inline-block; ">
                    <p style="float: right; color: white; line-height: 30px; height:100%; padding: 10px; text-align: right; direction: rtl;"><?= $bull['title']; ?></p>
                    <p style="float: left; line-height: 30px;height:100%; color: white; padding: 10px; text-align: left; "><?= \app\components\Jdf::jdate("Y/m/d", $bull['ts']); ?></p>
                </a>
            </div>
            <?php if($admin) {
                echo "<div style='width: 80%; max-width: 1000px;margin:0px auto;'>";
                echo Html::a('ویرایش', ['/main/update_notification?id='.$bull['id']], ['onclick'=>"window.close();", 'class' => 'btn btn-primary', 'style'=>"width:80px;", 'title'=>"ویرایش اعلان"]);
                echo Html::a('حذف', ['/main/delete_notification?id='.$bull['id']], ['class' => 'btn btn-danger','style'=>"width:80px;", 'title'=>"حذف اعلان"]);
                echo "</div>";
            } ?>
                <br style="clear:both; margin:10px;" />
            <!--    -->
        <?php }?>

        <br style="clear: both;">
        <?= yii\widgets\LinkPager::widget([
            'pagination' => $notiPages,
            'options'=>['style'=>'float:left;', 'class'=>'pagination']
        ]);

        ?>
        <br style="clear: both;" />


        <?php Pjax::end(); ?>
    </div>


