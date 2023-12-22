<?php
/* @var $this yii\web\View */
/* @var $this yii\web\View */
/* @var  $model */
/* @var  $project */
/* @var  $projects */


$this->title = 'PDCP|Project Remove';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;

Yii::$app->formatter->nullDisplay = "";
$this->registerCssFile(Yii::$app->request->baseUrl.'/web/css/sidebar.css');
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>

<div class="layout-wrapper  bg-gradient">
    <div class="layout-narrow-panel setting-sidebar">
        <?= \app\components\SidebarWidget::widget(['index'=>5, "projectId"=>$pId]); ?>
    </div>

    <div class="layout-wide-panel">

        <div style="width:95%; margin:auto;">
            <?php
            $form = ActiveForm::begin([
                'id'=>"projectsForm",
                'method' => 'GET',
                'action' => Yii::$app->request->baseUrl."/projects/remove_project",
                'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:10px auto;']]); ?>
            <label for="prj-form" style="display: block;color:white;">انتخاب پروژه</label>
            <select name="id" onchange="this.form.submit()" style="width: 300px;" class="form-control">
                <option value="-1" disabled <?php if($pId==-1) echo "selected"; ?> ></option>
                <?php
                foreach ($projects as $prj)
                {
                    $sel = "";
                    if($pId==$prj['id']) $sel="selected";
                    echo "<option value='".$prj['id']."' $sel >".$prj['project']."</option>";
                }
                ?>
            </select>

            <?php ActiveForm::end(); ?>
        </div>

        <hr />

        <?php
        if($pId > -1)
        {
            ?>
            <div style="width:95%; margin:auto;">
                <i class="fa fa-times fa-2x " style="color:red;display: block; text-align:center; margin:auto;" ></i>
                <h3 style="text-align: center;color:white;"><?= ' پروژه '.$project['project']; ?></h3>
                <h4 style="text-align: center;color:white;"><?= $project['office']; ?></h4>
                <div class="record-remove" style="direction:rtl;">
                    <h3 class="text-center" style="color:white;">حذف پروژه</h3>
                    <br />
                    <?php
                    $form = ActiveForm::begin([
                        'id'=>"recForm",
                        'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto','confirm' => 'آیا از حذف کاربر اطمینان دارید؟']
                    ]); ?>

                    <div class="form-group">
                        <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
                        <?= Html::submitButton('<i style="color:red;" class="fa fa-trash fa-3x" title="حذف پروژه"></i>', ['class'=>'hvr-bounce-in', 'style' =>'display:block; margin:auto;border:none; background:transparent;']) ?>
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
                                    'label' => 'نام پروژه',
                                    'value' => $model->project,
                                    'captionOptions'=>['class'=>'text-right text-primary'],
                                ],
                                [
                                    'label' => 'پروژه اداره کل',
                                    'value' => $model->office,
                                    'captionOptions'=>['class'=>'text-right text-primary'],
                                ],
                                [
                                    'label' => 'زمان ثبت پروژه',
                                    'value' => function($data){return \app\components\Jdf::jdate('Y/m/d', $data->ts);},
                                    'captionOptions'=>['class'=>'text-right text-primary'],
                                ],
                            ],
                    ]) ?>
                    </div>

                </div>
            </div>
            <?php
        }
        else
        {
            // no selected
            ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
            <?php
        }
        ?>
    </div>


</div>
