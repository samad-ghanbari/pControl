<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user */

$session = Yii::$app->session;
$session->open();
$adminFlag = 0;
$ownerFlag = false;
$project_owner=[];
if(isset($session['user'])) 
    $adminFlag = $session['user']['admin'];

if(isset($session['owner'])) 
{
    $project_owner = $session['owner'];
    if(sizeof($project_owner) > 0)
        $ownerFlag = true;
}

if($ownerFlag == true && $adminFlag == 1)
    $ownerFlag = false;

$projectFlag = 0;
$projectName = '';
$officeName = '';
$accessLevel = '';
$accessRw='';
if(isset($session['project'])) {$projectFlag = 1;$projectName = $session['project']['project'];$officeName=$session['project']['office'];}
if(isset($session['accessLevel'])) {$accessLevel = $session['accessLevel']['name']; $accessRw = $session['accessLevel']['rw'];}
$userFlag = false;
$user = ['name'=>'', 'lastname'=>'', 'employee_code'=>''];
if(isset($session['user']))
{
    $user = $session['user'];
    $userFlag = true;
}


use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container">
        <?php
        NavBar::begin([
            'brandLabel' => 'P D C P',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right', 'style'=>"direction:rtl;"],
            'items' => [
//                ['visible'=>$projectFlag, 'label' => "تاریخچه تغییرات <i class='fa fa-history text-primary'></i>", 'encode' => false, 'url' => '/main/log'],

                ['label' => " <i class='fa fa-home text-white'></i>"." صفحه اصلی ", 'encode' => false, 'url' => '/main/home', 'active'=>in_array(\Yii::$app->controller->action->id, ['home'])],
//                ['visible'=>$adminFlag, 'label' => " کاربران <i class='fa fa-users text-primary'></i>", 'encode' => false, 'url' => '/main/users', 'active'=>in_array(\Yii::$app->controller->action->id, ['users'])],
                ['visible'=>$adminFlag, 'label' => "  <i class='fas fa-cogs text-white'></i>"." تنظیمات ", 'encode' => false, 'url' => '/projects/edit_project', 'active'=>in_array(\Yii::$app->controller->id, ['projects'])],
                ['visible'=>$ownerFlag, 'label' => "  <i class='fas fa-cogs text-white'></i>"." تنظیمات پروژه ", 'encode' => false, 'url' => '/owner/edit_project', 'active'=>in_array(\Yii::$app->controller->id, ['owner'])],
//                ['visible'=>$projectFlag, 'label' => " جزییات پروژه <i class='fas fa-table text-primary'></i>", 'encode' => false, 'url' => '/project/index', 'active'=>in_array(\Yii::$app->controller->id, ['project'])],
                ['visible'=>true, 'encode'=>false,'label' =>"  <i class='fa fa-chart-pie text-white'></i>"." آمار پروژه " , 'active'=>in_array(\Yii::$app->controller->id, ['stat']),
                    'items'=>[
                        ['encode'=>false, 'label'=>"<img style='width:32px;height:auto; display:block; margin:auto;' src='".Yii::$app->request->baseUrl.'/web/images/pie.png'."'>"],
                        '<li class="divider"></li>',
                        ['label'=>"نمودارهای آماری" , 'encode'=>false, 'url' => ['/stat/piechart']],
                        '<li class="divider"></li>',
//                        ['label'=>'جدول آماری', 'encode'=>false, 'url' => ['/stat/tablestat']],
                        ['label'=>'جدول آماری', 'encode'=>false, 'url' => ['/stat/totaltablestat']],
                        '<li class="divider"></li>',
                        ['label'=>'آمار جزییات', 'encode'=>false, 'url' => ['/stat/cond']],
                        '<li class="divider"></li>',
                        ['label'=>'درصد پیشرفت', 'encode'=>false, 'url' => ['/stat/progress']],
                        '<li class="divider"></li>',
                        ['label'=>'چارت تخصیص تجهیزات', 'encode'=>false, 'url' => ['/stat/dedication']],
                        '<li class="divider"></li>',
                        ['label'=>'جدول تخصیص تجهیزات', 'encode'=>false, 'url' => ['/stat/dedicate']]

                    ]
                ],

                ['label' => " <i class='fa fa-bell text-white'></i>"." اعلانات ", 'encode' => false, 'url' => '/main/notifications',  'active'=>in_array(\Yii::$app->controller->action->id, ['notifications'])],

//                ['visible'=>true, 'encode'=>false,'label' =>"  <i class='fa fa-file-excel text-white'></i>"." گزارشات " , 'active'=>in_array(\Yii::$app->controller->id, ['report']),
//                    'items'=>[
//                        ['encode'=>false, 'label'=>"<img style='width:32px;height:auto; display:block; margin:auto;' src='".Yii::$app->request->baseUrl.'/web/images/excel.png'."'>"],
////                        '<li class="divider"></li>',
////                        ['label'=>"گزارش کلی" , 'encode'=>false, 'url' => ['/report/total']],
//                        '<li class="divider"></li>',
////                        '<li class="divider"></li>',
////                        ['label'=>'گزارش آماری', 'encode'=>false, 'url' => ['/report/stat']],
//                    ]
//                ],
                ['label' => "<i class='fas fa-download text-white'></i>"." ورود اطلاعات ", 'encode' => false, 'url' => '/import/index', 'active'=>in_array(\Yii::$app->controller->id, ['import'])],


            ],


        ]);

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' =>
                [
                    ['visible'=>$userFlag, 'encode'=>false, 'label'=>"<i  class='fa fa-user text-white'  style='font-size: 18px;width:50px;'></i>", 'active'=>in_array(\Yii::$app->controller->action->id, ['reset_password']),
                            'items'=>[
                                        ['encode'=>false,'label'=>"<img src='".Yii::$app->request->baseUrl."/web/images/tci.png' style='margin-top:5px; width:32px; display: block; margin:auto;'>",'options'=>['style'=>"width:200px;"],],
                                        ['encode'=>false,'label'=>"<h5 class='text-center'>مخابرات منطقه تهران </h5>"],
                                        '<li class="divider"></li>',
                                        ['encode'=>false,'label'=>"<h5 class='text-center'>".$user['name'].' '.$user['lastname']."</h5>"],
                                        //['encode'=>false,'label'=>"<h5 class='text-center'>".$user['employee_code']."</h5>"],
                                        ['encode'=>false,'label'=>"<h5 class='text-center'>".$user['office']."</h5>"],
                                        '<li class="divider"></li>',
                                        ['label'=>" تغییر رمز عبور ", 'url'=>"/main/reset_password"],
                                        '<li class="divider"></li>',
                                        ['encode'=>false,'label'=>"<i class='fa fa-sign-out-alt'></i>  خروج ", 'url'=>"/main/logout", 'options'=>['style'=>"width:100%; background-color:#9e0061;text-align:center;", 'class'=>"hvr-dim"]]
//                                        ['encode'=>false, 'label'=>"<a href='".Yii::$app->request->baseUrl."/main/logout' class='hvr-bounce-out' style='width:100%;color:#fff; background-color: mediumvioletred;text-align:center;  display: block;margin:auto;'><i class='fa fa-sign-out-alt'></i>  خروج  </a>"]
                                    ]
                    ],

//                    ['visible'=>$projectFlag, 'encode'=>false, 'label'=>"<i  class='fa fa-cloud text-primary'  style='font-size: 18px;width:50px;'></i>", 'active'=>in_array(\Yii::$app->controller->action->id, ['reset_password']),
//                        'items'=>[
//                            ['encode'=>false,'label'=>"<img src='".Yii::$app->request->baseUrl."/web/images/certificate.png' style='margin-top:5px; width:32px; display: block; margin:auto;'>",'options'=>['style'=>"width:200px;"],],
//                            ['encode'=>false,'label'=>"<h5 class='text-center'>$accessLevel</h5>"],
//                            ['encode'=>false,'label'=>"<h6 class='text-center'>$accessRw</h6>"],
//                            '<li class="divider"></li>',
//                            ['encode'=>false,'label'=>"<h5 class='text-center'>پروژه</h5>"],
//                            ['encode'=>false,'label'=>"<h5 class='text-center' style='direction:rtl;'>".$projectName."</h5>"],
//                            '<li class="divider"></li>',
//                            ['encode'=>false,'label'=>"<h5 class='text-center'>$officeName</h5>"],
//                        ]
//                    ],
                ],
        ]);

        NavBar::end();
        ?>
        <!-- display success message -->
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade in " style="max-width: 80%; margin: auto; direction:rtl;">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?= Yii::$app->session->getFlash('success') ?>
            </div>

        <?php endif; ?>
        <!-- display error message -->
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div  class="alert alert-danger alert-dismissible fade in" style="max-width: 80%; margin: auto;direction:rtl;">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
      
        <?php endif; ?>
        <!--   ticket     -->
        <div class="ticket-div">
            <a href="<?= Yii::$app->request->baseUrl.'/main/ticket_inbox'; ?>" style="display: inline-block; text-align: center; line-height: 40px;color:#fff; font-weight: bold; width: 100%; height:100%;">پشتیبانی</a>
        </div>

        <?= $content ?>
    </div>
</div>
<footer class="footer" title="Developer Samad Ghanbari <s.ghanbari@tci.ir>">
    <div class="container">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/tci.png'; ?>" style="float:right;margin-top:10px; width:40px;height:40px;">
        <p style="float:left; margin-top:20px;"> Developed By Planning Office &copy TCT</p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php
$script =<<< JS
function toggleUser(obj)
{
    var toggle = $(obj).attr('data-toggle');
    if(toggle == 0)
        {
            $(obj).attr('data-toggle', 1);
            $("#user-box").css("display", "block");
            $(obj).removeClass("fa-user");
            $(obj).addClass("fa-times");
        }
    else 
        {
            $(obj).attr('data-toggle', 0);
            $("#user-box").css("display", "none");
            $(obj).removeClass("fa-times");
            $(obj).addClass("fa-user");
        }
}


JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>

<?php $this->endPage() ?>
