<?php

/* @var $this yii\web\View */
/* @var $model app\models\PcTicket */
/* @var $projects */


$this->title = 'PDCP|New Ticket';
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use dosamigos\ckeditor\CKEditor;


Yii::$app->formatter->nullDisplay = "";

?>
<div class="backicon">
    <a href="<?= Yii::$app->request->referrer; ?>" ><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</div>
<div class="topic-cover bg-gradient">
        <h2 style="text-align: center; color:#fff;"> P D C P </h2>
        <h4 style="text-align: center; color:#fff;">درخواست پشتیبانی جدید </h4>
    <img src="<?= Yii::$app->request->baseUrl.'/web/images/support.png'?>" style="display: block; width: 150px; height: auto; margin:auto;">
    <br />

        <div style="width:80%; margin:auto; direction:rtl;padding:20px;border-radius:10px;" class="box-shadow-dark">
            <?php $form = ActiveForm::begin(['action' =>Yii::$app->request->baseUrl.'/main/ticket_insert', 'method' => "POST" ,'options'=>['style'=>'direction:rtl;']]); ?>

            <?= $form->field($model, 'user_id')->hiddenInput()->label(false); ?>
            <?= $form->field($model, 'project_id',['labelOptions'=>['style'=>"color:white;"]])->dropDownList($projects); ?>

            <?= $form->field($model, 'title',['labelOptions'=>['style'=>"color:white;"]])->textInput(["style"=>"direction:rtl;", 'required'=>true]); ?>
            <?= $form->field($model, 'ticket',['labelOptions'=>['style'=>"color:white;"]])->widget(CKEditor::className(), ['options' => ['id'=>'ticket' ,'rows' => 10, 'required'=>true], 'preset' => 'full',
                'clientOptions' => ['language' => 'fa',
                    'allowedContent' => true,
                    'filebrowserUploadUrl' => yii\helpers\Url::to(['/main/img_upload']),
                    'filebrowserBrowseUrl' => Yii::$app->request->baseUrl.'/main/img_browse',
                ] ]) ?>

            <div class="form-group">
                <?= Html::submitButton('ارسال درخواست', ['class' => 'btn btn-success pull-left']) ?>
            </div>
            <br style="clear:both;" />
            <?php ActiveForm::end(); ?>

        </div>

        <br style="clear: both;" />
</div>




