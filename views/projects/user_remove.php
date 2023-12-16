<?php

/* @var $this yii\web\View */
/* @var $model $model */
$this->title = 'pControl|Remove';
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;


Yii::$app->formatter->nullDisplay = "";
?>
<div class="topic-cover bg-gradient">
<div class="record-remove" style="direction:rtl;">
    <h3 class="text-center" style="color:white;">حذف کاربر</h3>
    <br />
<?php
    $form = ActiveForm::begin([
    'id'=>"recForm",
    'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto','onsubmit' => "return confirm('آیا از حذف کاربر اطمینان دارید؟');"]
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
        <?= Html::submitButton('<i class="fa fa-trash text-danger fa-3x" title="حذف کاربر"></i>', ['class'=>'hvr-bounce-in', 'style' =>'display:block; margin:auto;border:none; background:transparent;']) ?>
    </div>
    <br/>
    <?php ActiveForm::end(); ?>

    <p class="backicon">
        <a href="users"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
    </p>
    <hr style="border-top:1px dotted white;"/>

    <div style="background-color:#eee;">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' =>
            [
            [
                'label' => 'نام',
                'value' => $model->name,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'نام خانوادگی',
                'value' => $model->lastname,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'کد ملی',
                'value' => $model->nid,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'کد مستخدمی',
                'value' => $model->employee_code,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'اداره کل',
                'value' => $model->office,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'سمت',
                'value' => $model->post,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'شماره تماس',
                'value' => $model->tel,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ]
        ],
    ]) ?>
    </div>

</div>
<br />
</div>