<?php

/* @var $this yii\web\View */
/* @var $admin */
/* @var $ticket \app\models\PcViewTickets */
/* @var $reply \app\models\PcTicketReplies */
/* @var $replies \app\models\PcViewTicketReplies */


$this->title = 'PDCP|Ticket';
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use dosamigos\ckeditor\CKEditor;

?>
<div class="backicon">
    <a href="<?= Yii::$app->request->referrer; ?>" ><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</div>

<div class="topic-cover bg-gradient">
        <h2 style="text-align: center; color:#fff;"> P D C P </h2>
        <h4 style="text-align: center; color:#fff;"> مشاهده درخواست </h4>
    <div style="width:100%;padding:10px 10px 100px 10px; min-height:30vh;" >
        <br />
        <!--  ticket  -->
        <div class="box-shadow-dark" style="width: 100%;background-color:rgba(100,100,100,0.2);border-radius:10px; ">
            <div style="width:80%; float: right; border-radius:10px; background-color:rgba(150,255,150, 0.3);border:2px solid #0F9E5E">
                <div style="float: left; width:70%; padding: 10px;">
                    <div style="width: 100%;direction: rtl; background-color: #aaa; color:#000; text-align: center; padding: 10px;border-radius:10px 10px 0 0;">
                        <?= $ticket->title; ?>
                    </div>
                    <div style="width: 100%; line-height: 30px; background-color: #ddd; color:#335;border-radius:0 0 10px 10px; padding: 10px; direction: rtl; text-align: justify;">
                        <?= $ticket->ticket; ?>
                    </div>
                    <br style="clear: both;" />
                </div>
                <div style="float: right; width:30%; padding: 10px;">
                    <div style="background-color: #2b542c;border-radius:10px 10px 0 0 ;color:#fff; padding: 10px;direction: rtl; text-align: center;">
                        <?= $ticket->name." ".$ticket->lastname; ?>
                        <br />
                        <?= $ticket->office; ?>
                    </div>
                    <div style="margin:0;padding:10px; direction: rtl; background-color:#aaa; color:#333; text-align: center;">
                        پروژه
                        <br />
                        <?= $ticket->project; ?>
                    </div>
                    <div style="margin:0; padding:10px;direction: rtl; background-color: #aaa; color:#333; text-align: center; border-bottom:1px solid #000; border-radius:0 0 10px 10px;">
                        زمان ثبت
                        <br />
                        <?= \app\components\Jdf::jdate("Y/m/d H:i", $ticket->ts); ?>
                    </div>
                    <br style="clear: both" />
                </div>
            </div>
            <br style="clear: both;" />
            <br />
        <br  />

        <!--  replies  -->
        <?php
        $dirLeft = true;
        foreach ($replies as $rep) {
                $float = ($dirLeft)? 'left' :'right';
                $infoDir = $float;
                $repDir = ($infoDir == 'left')? 'right' : 'left';
                ?>
            <div style="width: 80%;float:<?= $float; ?>">
                <div style="width:100%; background-color:#ddd;border-radius:10px; ">
                    <div style="float: <?= $repDir; ?>; width:70%; padding: 10px;">
                        <div style="width: 100%; line-height: 30px; background-color: #ddd;padding: 10px; direction: rtl; text-align: justify; border-radius:10px;">
                            <?= $rep['reply']; ?>
                        </div>
                        <br style="clear: both;" />
                    </div>
                    <div style="float: <?= $infoDir; ?>; width:30%; padding: 10px;">
                        <div style="background-color: #444;border-radius:10px; color:#fff; padding: 10px;direction: rtl; text-align: center;">
                            <?= $rep['name']." ".$rep['lastname']; ?>
                            <br />
                            <?= $rep['office']; ?>
                        </div>
                        <div style="margin: 0; padding:10px;direction: rtl; color:#555; text-align: center;">
                            زمان ثبت
                            <br />
                            <?= \app\components\Jdf::jdate("Y/m/d H:i", $rep['ts']); ?>
                        </div>
                        <br style="clear: both" />
                    </div>
                    <br style="clear: both;" />
                </div>
            </div>
            <br style="clear: both;" />
            <br  />
        <?php
        $dirLeft = ($dirLeft)? false : true;
        } ?>
        </div>

        <!--    reply -->
        <br />
        <?php if($admin) { ?>
            <div style="width:80%; margin:auto; direction:rtl;padding:20px;border-radius:10px;" class="box-shadow-dark">
                <?php $form = ActiveForm::begin(['action' =>Yii::$app->request->baseUrl.'/main/ticket_reply', 'method' => "POST" ,'options'=>['style'=>'direction:rtl;']]); ?>

                <?= $form->field($reply, 'ticket_id')->hiddenInput()->label(false); ?>

                <?= $form->field($reply, 'reply')->widget(CKEditor::className(), ['options' => ['id'=>'reply' ,'rows' => 5, 'required'=>true], 'preset' => 'full',
                    'clientOptions' => ['language' => 'fa',
                        'allowedContent' => true,
                        'filebrowserUploadUrl' => yii\helpers\Url::to(['/main/img_upload']),
                        'filebrowserBrowseUrl' => Yii::$app->request->baseUrl.'/main/img_browse',
                    ] ]) ?>

                <div class="form-group">
                    <?= Html::submitButton('ارسال پاسخ', ['class' => 'btn btn-success pull-left']) ?>
                </div>
                <br style="clear:both;" />
                <?php ActiveForm::end(); ?>

            </div>

        <?php } ?>
    </div>
    <br />
</div>


