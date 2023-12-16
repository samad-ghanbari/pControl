<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user */

$session = Yii::$app->session;
$session->open();
$adminFlag = 0;
if(isset($session['user'])) $adminFlag = $session['user']['admin'];
$projectFlag = 0;
$projectName = '';
$officeName = '';
$accessLevel = '';
if(isset($session['project'])) {$projectFlag = 1;$projectName = $session['project']['project'];$officeName=$session['project']['office'];}
if(isset($session['accessLevel'])) {$accessLevel = $session['accessLevel']['name'];}
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
    <div class="container" style="width:100%;" >
        <?php
        NavBar::begin([
            'brandLabel' => 'P C o n t r o l',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-default navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
//                ['visible'=>$projectFlag, 'label' => "تاریخچه تغییرات <i class='fa fa-history text-primary'></i>", 'encode' => false, 'url' => '/main/log'],

                ['visible'=>$projectFlag, 'encode'=>false,'label' =>"</a><div id='user-box-div' style='position:relative;display: inline-block;top:-15px;'>
                             آمار پروژه <i  class='fa fa-chart-pie text-primary' data-toggle='0'   style='cursor:pointer;display:inline-block;width:50px;'></i>
                            <div id='user-box' class='box-shadow-dark'>
                                <img src='".Yii::$app->request->baseUrl."/web/images/pie.png' style='margin-top:5px; width:32px; display: block; margin:auto;'>
                                <h5 class='text-center'>آمار پروژه</h5>
                                <hr style='border:1px dotted #28a4c9; margin:2px;' />
                                <a href='".Yii::$app->request->baseUrl."/stat/province' class='btn' style='width:80%;  display: block;margin:auto;'> آمار در سطح استان </a>
                                <hr style='border:1px dotted #28a4c9; margin:2px;' />
                                <a href='".Yii::$app->request->baseUrl."/stat/area' class='btn' style='width:80%;  display: block;margin:auto;'> آمار در سطح منطقه </a>
                                <hr style='border:1px dotted #28a4c9; margin:2px;' />
                                <a href='".Yii::$app->request->baseUrl."/stat/exchange' class='btn' style='width:80%;  display: block;margin:auto;'> آمار در سطح مرکز </a>
                            </div>
                        </div>
                            " ,
//                    'options'=>['style'=>'margin:0; padding:0;'],
                ],
                ['visible'=>$projectFlag, 'label' => " جزییات پروژه <i class='fas fa-table text-primary'></i>", 'encode' => false, 'url' => '/project/index'],

                ['visible'=>$adminFlag, 'label' => " مدیریت پروژه ها <i class='fas fa-project-diagram text-primary'></i>", 'encode' => false, 'url' => '/projects/index'],
                ['visible'=>$adminFlag, 'label' => " مدیریت کاربران <i class='fa fa-users text-primary'></i>", 'encode' => false, 'url' => '/main/users'],
                ['label' => " پروژه های من <i class='fa fa-tasks text-primary'></i>", 'encode' => false, 'url' => '/main/home'],
            ],


        ]);

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' =>
                [
                    ['visible'=>$userFlag, 'encode'=>false,'label' =>"</a><div id='user-box-div' style='position:relative;display: inline-block;top:-15px;'>
                            <i  class='fa fa-user text-primary' data-toggle='0'   style='cursor:pointer;display:inline-block;font-size: 18px;;width:50px;'></i>
                            <div id='user-box' class='box-shadow-dark'>
                                <img src='".Yii::$app->request->baseUrl."/web/images/tci.png' style='margin-top:5px; width:32px; display: block; margin:auto;'>
                                <h5 class='text-center'>مخابرات منطقه تهران </h5>
                                <hr style='border:1px dotted #1b6d85; margin:2px;' />
                                <h5 class='text-center'>".$user['name'].' '.$user['lastname']."</h5>
                                <h5 class='text-center'>".$user['employee_code']."</h5>
                                <hr style='border:1px dotted #1b6d85; margin:2px;' />
                                <a href='".Yii::$app->request->baseUrl."/main/reset_password' class='btn' style='width:80%;  display: block;margin:auto;'> تغییر رمز عبور </a>
                                <a href='".Yii::$app->request->baseUrl."/main/logout' class='btn btn-danger' style='width:80%;  display: block;margin:auto;'><i class='fa fa-sign-out-alt'></i>  خروج  </a>
                            </div>
                        </div>
                            " ,
//                    'options'=>['style'=>'margin:0; padding:0;'],
                    ],

                    ['visible'=>$projectFlag, 'encode'=>false,'label' =>"</a><div id='user-box-div' style='position:relative;display: inline-block;top:-15px;'>
                            <i  class='fa fa-cloud text-primary' data-toggle='0'   style='cursor:pointer;display:inline-block;font-size: 18px;;width:50px;'></i>
                            <div id='user-box' class='box-shadow-dark'>
                                <img src='".Yii::$app->request->baseUrl."/web/images/certificate.png' style='margin-top:5px; width:32px; display: block; margin:auto;'>
                                <h5 class='text-center'>$accessLevel</h5>
                                <hr style='border:1px dotted #1b6d85; margin:2px;' />
                                <h5 class='text-center'>پروژه</h5>
                                <h5 class='text-center'>".$projectName."</h5>
                                <hr style='border:1px dotted #1b6d85; margin:2px;' />
                                <h5 class='text-center'>$officeName</h5>
                            </div>
                        </div>
                            " ,
//                    'options'=>['style'=>'margin:0; padding:0;'],
                    ],

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
            <br/>
        <?php endif; ?>
        <!-- display error message -->
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div  class="alert alert-danger alert-dismissible fade in" style="max-width: 80%; margin: auto;direction:rtl;">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
            <br/>
        <?php endif; ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer" title="Developer Samad Ghanbari">
    <div class="container">
        <ul style="float:right;  list-style-type: none;">
            <li>
                <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:auto; width:80px;height:80px;">
            </li>
        </ul>

        <ul style="float:left; list-style-type: none;">
            <li>
                <p>&copy; TCT <?= date('Y') ?></p>
            </li>
            <li>
                <p> Designed By Planning Office</p>
            </li>
            <li>
                <h6> Developer Contact: S.Ghanbari@Tci.ir</h6>
            </li>
        </ul>
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
