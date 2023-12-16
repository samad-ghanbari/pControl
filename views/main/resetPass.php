<?php

/* @var $id */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = 'pControl|Reset Password';
?>
<div class="topic-cover bg-gradient">
<div class="row" style="width:100%; margin:0px auto;">
  <div class="col-md-4 col-md-push-7 padding-5">
    <h4 class=" text-right pr5" style="direction: rtl;color:whiye;">پسورد جدید خود را وارد نمایید</h4>
    <div class="box-shadow-dark" style="border-radius:10px; padding:50px 10px 10px; width:95%; max-width:450px;margin: auto;">
        <?php
        $form = ActiveForm::begin([
            'options'=>['style'=>'direction:rtl;']
        ]);
        ?>
        <label class='control-label' style="color:white;" for='pId'>رمز جاری</label>
        <?= Html::input('cp','cp','', $options=['id'=>"pId", 'type'=>"password", 'class'=>'form-control', 'placeholder'=>'رمز جاری', 'style'=>"dispaly: block; width:300px;margin:auto;"]); ?>
        <hr style="border-top:1px dotted gray; width:300px;">
        <?php
        echo "<input name='id' type='hidden' value='".$id."'>";
        echo "<label class='control-label' style='color:white;' for='p1Id'>رمز عبور جدید</label>";
        echo Html::input('password','password','', ['required'=>true,'id'=>"p1Id", 'type'=>"password", 'class'=>'form-control', 'style'=>'width: 300px;margin:auto;']);
        echo "<label class='control-label' style='color:white;' for='p2Id'>تایید رمز عبور</label>";
        echo Html::input('passwordConfirm','passwordConfirm', '', ['required'=>true, 'id'=>"p2Id", 'type'=>"password", 'class'=>'form-control', 'style'=>'width: 300px;margin:auto;']);
        ?>
        <br />
        <div class="form-group">
            <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left padding-10', "id"=>"resetPassBtn"]) ?>
        </div>
        <br style="clear:both;" />
        <?php ActiveForm::end(); ?>
    </div>
  </div>
  <div class="col-md-6 col-md-pull-4 padding-5">
    <img style="padding-top:5%; width=90%; max-width:300px; display:block;margin:auto; margin-top:20px;" src="<?= Yii::$app->request->baseUrl."/web/images/newpass.png"; ?>">
  </div>
</div>
</div>