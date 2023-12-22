<?php

/* @var $this yii\web\View */
/* @var $model \app\models\PcViewUserProjects */
$this->title = 'pControl|Remove user project';
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;


Yii::$app->formatter->nullDisplay = "";
?>

<p class="backicon">
    <a href="<?= 'project_users?id='.$model['project_id']; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
</p>
<div class="topic-cover bg-gradient">

<div class="record-remove" style="direction:rtl;">
    <h3 class="text-center" style="color:white;">حذف کاربر از پروژه</h3>
    <br />
<?php
    $form = ActiveForm::begin(['options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto','confirm' => 'آیا از حذف کاربر از پروژه اطمینان دارید؟']
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
        <?= Html::submitButton('<i class="fa fa-trash text-danger fa-3x" title=" حذف کاربر از پرٰوژه "></i>', ['class'=>'hvr-bounce-in', 'style' =>'display:block; margin:auto;color:white;border:none; background:transparent;']) ?>
    </div>
    <br/>
    <?php ActiveForm::end(); ?>

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
                'label' => ' اداره کل',
                'value' => $model->office,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'پست',
                'value' => $model->post,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'پروژه',
                'value' => $model->project,
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'منطقه',
                'format'=>'html',
                'value' => function($data){if(empty($data['area'])) return "<i class='fa fa-certificate' style='color: goldenrod;'></i>"; else return $data['area']; },
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
            [
                'label' => 'مرکز',
                'format'=>'html',
                'value' => function($data){if(empty($data['exchange_id'])) return "<i class='fa fa-certificate' style='color: goldenrod;'></i>"; else return $data['exchange']; },
                'captionOptions'=>['class'=>'text-right text-primary'],
            ],
        ],
    ]) ?>
    </div>

</div>
<br />
</div>