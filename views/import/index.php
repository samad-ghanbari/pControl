<?php

/* @var $this yii\web\View */
/* @var $projects */

use yii\bootstrap\ActiveForm;

$this->title = 'PDCP|Import';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";
?>
    <div class="topic-cover bg-gradient" >
        <div style="width:90%;max-width: 400px; margin:auto; height:100px; padding: 20px; border-radius:10px;">
            <h4 style="text-align: center; color:#fff;">ورود اطلاعات پایه پروژه</h4>
            <i class="fa fa-download fa-2x" style="display: block; text-align:center; color:#fff;width:100%;"></i>
        </div>
        <div style="width: 90%; margin:20px auto; display: flex; justify-content: center;">
            <a style="border-radius:8px 0 0 8px;" href="<?= Yii::$app->request->baseUrl.'/views/import/PDCP_IMPORT_Template.xls'; ?>" class="btn btn-primary" >فایل نمونه ورود اطلاعات</a>
            <a style="border-radius:0;" href="<?= Yii::$app->request->baseUrl.'/import/export_centers' ?>" class="btn btn-primary" >لیست مراکز اصلی</a>
            <a style="border-radius:0 8px 8px 0;" onclick="exportLom()" class="btn btn-primary" >لیست تجهیزات</a>
        </div>

        <div style="width:95%;  max-width: 500px; margin:20px auto; background-color: rgba(100,100,100,0.5); border-radius: 10px;padding:10px;" class="box-shadow-dark">
            <h4 style="text-align:center; color:white;">جهت ورود اطلاعات لطفا فرم زیر را تکمیل نمایید</h4>

            <div style="width:90%; max-width: 400px; margin: auto; direction: rtl;">
                <form method="post" enctype="multipart/form-data" action="<?= Yii::$app->request->baseUrl.'/import/parser'; ?>">
                    <input type="hidden" name="<?= yii::$app->request->csrfParam; ?>" value="<?= yii::$app->request->csrfToken; ?>">

                    <label for="projectCB" style="text-align: right;color:white;" >انتخاب پروژه</label>
                    <select id="projectCB" name="projectCB" class="form-control" style="direction: rtl;" required>
                        <option disabled selected></option>
                        <?php
                        foreach ($projects as $project)
                        {
                            $prj = $project['project'];
                            $prjId = $project['project_id'];
                            echo "<option value=$prjId>$prj</option>";
                        }
                        ?>

                    </select>
                    <br />
                    <label for="fileIn" style="text-align: right;color:white;">انتخاب فایل اکسل ورودی</label>
                    <input id="fileIn" name="fileUpload" type="file" accept="application/vnd.ms-excel"  class="form-control" style="direction: rtl;"  required/>
                    <br />
                    <button class="btn btn-success " style="float: left; width:100px;" type="submit" >
                        ادامه
                        <i class=" fa fa-arrow-left"></i>
                    </button>
                </form>
                <br style="clear:both;" />
                <br />
            </div>

        </div>


        <img src="<?=Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="width: 128px; display: block; margin:auto;" >
    </div>


<?php
require_once("getLomModal.php");

$script =<<< JS
function exportLom()
{
    $("#getLomModal").modal("show");
}
JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>