<?php
/* @var $this yii\web\View */
/* @var $model */
$this->title = 'PDCP|Login';

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

?>
<div class="bg-cover login-cover">

    <div class="row" style="width:100vw; height:100vh; box-sizing: border-box;">
        <div class="col-md-4" style="height:100vh">

                    <!-- display error message -->
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div  class="alert alert-danger alert-dismissible fade in" align="center" style="text-align: right; direction:rtl; margin: auto;">
                     <!--            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>-->
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
                <br/>
            <?php endif; ?>


            <div class="box-shadow-dodgerblue" style="position: absolute;min-width:350px; top: 50%;left: 50%;transform: translate(-50%, -50%); background-color: rgba(50,50,50,0.7); padding: 20px; border-radius:10px;">
                <h4 style="color:white; text-align: center; padding:15px;">ورود به سامانه</h4>
                <hr style="margin:2px; padding:0px;" />
                <?php $form = ActiveForm::begin(['options' => ['style' => "width:90%; max-width:300px; margin:auto; display:block; direction:rtl;"]]); ?>
                    
                <?= $form->field($model, 'nid', ['labelOptions' => ['style' => 'color:white;'], 'errorOptions' => ['class' => 'text-danger']])->textInput(['placeholder' => "کد ملی خود را وارد نمایید"]); ?>
                <?= $form->field($model, 'password', ['labelOptions' => ['style' => 'color:white;'], 'errorOptions' => ['class' => 'text-danger']])->passwordInput(['placeholder' => "رمز عبور خود را وارد نمایید"]); ?>
                
                <?= $form->field($model, 'verifyCode', ['options'=>['style'=>'margin:0px;direction:rtl;'], 'labelOptions'=>['style'=>"padding:0; margin:0;width:100%;text-align:right; color:white; height:40px; line-height:40px;"]])
                        ->widget(Captcha::className(), 
                        [
                        'options'=>['class'=>"form-control enFont text-right", 'style'=>'height:100%; width:100%;'],
                        'template' => '  <div style="height:100%; width:100%; padding:0;">
                                                <div class="row" style="width:100%; padding:0; margin:0;">
                                                <div class="col-md-5" style="padding:0; margin:0;height:40px;">{image}</div>
                                                <div class="col-md-7" style="padding:0; margin:0;height:40px;">{input}</div>
                                                </div>
                                            </div>
                                    ',
                    ]); ?>

                <div class="form-group" style="width:100px;margin:auto;">
                    <br />
                    <button type="submit" style="min-width:120px;" class="btn btn-success"><i class="fa fa-sign-in-alt text-white"></i> ورود</button>
                </div>
                <?php ActiveForm::end(); ?>
             </div>
        </div>

        <div class="col-md-8" style="height:100vh; background-color: rgba(40,40,40,0.5);">
            <div style="width: 100%; height:100%; display:flex; justify-content: center; align-items: center;">
                    <div class="column">
                        <img src="<?= Yii::$app->request->baseUrl.'/web/images/tci.png'; ?>" style="display: block; margin:auto; width:100px;height:100px;">
                        <h1 style="color:white; text-align: center; padding:5px;">مخابرات منطقه تهران</h1>
                        <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:auto; width:120px;height:120px;">
                        <h2 style="color:white; text-align: center; padding:15px;">سامانه جامع کنترل پروژه</h2>
                    </div>
            </div>
        </div>
    </div>

</div>