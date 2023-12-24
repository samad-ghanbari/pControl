<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcUserProjects */
/* @var $project */
/* @var $users */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'PDCP| Project Owner';
?>

<p class="backicon">
    <a href="<?= 'project_owner?id='.$project['id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">

<h4 style="text-align: center; color:white; direction:rtl;"><?= ' افزودن مسئول جدید به پروژه '.$project['project']; ?></h4>
<br >
<div class="row" style="width:100%;">
    <div class="col-md-8">
        <div style="width: 90%; max-width:700px; margin:auto; padding: 20px;" >
            <?php $form = ActiveForm::begin(['options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']]); ?>

            <?= $form->field($model, 'user_id', ['labelOptions'=>['style'=>'color:white;']])->dropDownList($users,['prompt'=>"انتخاب کاربر"]); ?>

            <div class="form-group">
                <br/><br/><?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
            </div>
            <br/><br/>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
    <div class="col-md-4">
        <img src="<?= Yii::$app->request->baseUrl.'/web/images/user.png'; ?>" style="display: block;width:90%; max-width:300px;height:auto; margin:auto;">
    </div>
</div>
<br/>
</div>


