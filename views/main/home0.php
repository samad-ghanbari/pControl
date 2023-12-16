<?php

/* @var $this yii\web\View */
/* @var  $userProjects */
/* @var  $notiPages */
/* @var  $bulletin */
/* @var  $admin */

$this->title = 'PDCP|Home';
use yii\helpers\Html;
use yii\widgets\Pjax;


Yii::$app->formatter->nullDisplay = "";
$user = ['name'=>'', 'lastname'=>''];
$sessoin = Yii::$app->session;
if(isset($sessoin['user']))
    $user= $sessoin['user'];

?>

    <div class="topic-cover bg-gradient" >
        <div class="layout-wrapper">
            <div class="layout-narrow-dim-panel">
                <img style="display: block; margin: auto; width:20%;" src="<?= Yii::$app->request->baseUrl.'/web/images/alarm.png'; ?>">
                <a href="<?= Yii::$app->request->baseUrl.'/main/notifications'; ?>" class="btn btn-primary" style="display: block; margin: auto;">مشاهده همه اعلانات </a>
                <br />
                <h4 style="text-align: center; color: white;">اعلانات اخیر</h4>

                <ul style="list-style-type: none;width: 100%; direction:rtl; padding:0; margin:0;">
                    <?php foreach ($bulletin as $bull){
                        $url = Yii::$app->request->baseUrl.'/main/bulletin_view?id='.$bull['id']; ?>
                        <!--    -->
                        <li  style="width: 100%; color:white; padding-right: 5px;">
                            <a href="javascript:void(0);" onclick="myPopup('<?= $url; ?>', 'اعلان',700, 400)" style="width: 100%;direction: rtl; display: inline-block; ">
                                <i class=" fa fa-check-square" style="color:<?= $bull['panel_color']; ?>"></i>
                                <span style="font-weight: bold; color: white; line-height: 30px; text-align: right; direction: rtl;"><?= $bull['title']; ?></span>
                            </a>
                        </li>
                        <hr style="border-top:1px dotted white;" />
                        <!--    -->
                    <?php }?>
                </ul>

                <br style="clear: both;">
            </div>

            <div class="layout-wide-dim-panel">
                <img style="width: 80px; height:auto; display:block; margin:auto;" src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>">
                <h3 style="text-align: center; color:white;">پروژه های من</h3>
                <hr />
                <br />
                <!-- flex-wrap:wrap;  -->
                <div style="display: flex;flex-direction:column; align-items:center; justify-content: center; overflow: hidden; margin-bottom: 50px;">
                    <?php
                    $url = Yii::$app->request->baseUrl.'/project/index?id=';
                    if(empty($userProjects))
                    {
                        echo "<h3 style='color:#721c24; text-align: center; direction:rtl;'>پروژه ای برای شما تعریف نشده است.</h3>";
                    }
                    else
                    {
                        foreach ($userProjects as $rec)
                        {
                            echo \app\components\ProjectWidget::widget(['model' => $rec, 'url' => $url]);
                        }
                    }
                    ?>

                    <br style="clear:both;" />
                </div>
            </div>
        </div>
    </div>


