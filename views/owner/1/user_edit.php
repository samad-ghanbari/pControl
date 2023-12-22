<?php

/* @var $this yii\web\View */
/* @var $model */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use faravaghi\jalaliDatePicker\jalaliDatePicker;

$this->title = 'pControl|Edit User';
?>
<p class="backicon">
    <a href="users"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">
<div class="row" style="width:100%">
    <div class="col-md-8">
        <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
            <h4 class="text-center" style="direction: rtl;color:white;">ویرایش کاربر</h4>
            <?php
            $form = ActiveForm::begin([
                'id'=>"recForm",
                'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']
            ]); ?>
            <div style="width:100%; clear:both;">
            <?= $form->field($model, 'name', ['options'=>['style'=>'float:right; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['maxlength' => true, 'style'=>"direction:rtl", 'placeholder' => "نام"]) ?>
            <?= $form->field($model, 'lastname', ['options'=>['style'=>'float:left; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['maxlength' => true, 'style'=>"direction:rtl", 'placeholder' => "نام خانوادگی"]) ?>
            </div>

            <div style="width:100%; clear:both;">
            <?= $form->field($model, 'nid', ['options'=>['style'=>'float:right; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['placeholder' => "کد ملی"]); ?>
            <?= $form->field($model, 'employee_code', ['options'=>['style'=>'float:left; width:50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['placeholder' => "کد مستخدمی"]); ?>
            </div>

            <div style="width:100%; clear:both;">
            <?= $form->field($model, 'office', ['options'=>['style'=>'float:right; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['placeholder' => "اداره کل"]); ?>
            <?= $form->field($model, 'post', ['options'=>['style'=>'float:left; width:50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['placeholder' => "سمت"]); ?>
            </div>

            <div style="width:100%; clear:both;">
            <?= $form->field($model, 'action_role', ['options'=>['style'=>'float:right; width: 100%;'],'labelOptions'=>['style'=>'color:white;']])
            ->dropDownList(["design"=>'طراحی', "install"=>'نصب و راه‌اندازی', "operation"=>"عملیات و مدیریت شبکه", "test"=>"نظارت، آزمایش و تحویل","district"=>"منطقه", "it"=>"فناوری اطلاعات", "planning"=>"برنامه‌ریزی", "no-action"=>"بدون فعالیت"] ); ?>
            </div>

            <div style="width:100%; clear:both;">
            <?= $form->field($model, 'tel', ['options'=>['style'=>'float:right; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->textInput(['placeholder' => "شماره تماس"]); ?>
            <?= $form->field($model, 'enabled', ['options'=>['style'=>'float:left; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->dropDownList([1=>'فعال', 0=>' غیرفعال'] ); ?>
            </div>

            <div style="width:100%; clear:both;">
            <?= $form->field($model, 'password', ['options'=>['style'=>'float:right; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->passwordInput(['placeholder' => "رمز عبور"]); ?>
            <?= $form->field($model, 'passwordConfirm', ['options'=>['style'=>'float:left; width: 50%;'],'labelOptions'=>['style'=>'color:white;']])->passwordInput(['placeholder' => "تایید رمز عبور"]); ?>
            </div>

            <div class="form-group">
                <br/>
                <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>

            </div>
            <br/>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
    <div class="col-md-4">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/editUser.png'; ?>" style="display: block;width:90%; max-width:500px;height:auto; margin:auto;">
    </div>
</div>
<br/>
</div>