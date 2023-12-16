<?php
use yii\helpers\Url;
/* @var $model */
$url = Yii::$app->request->baseUrl."/projects/edit_lom?id=";
?>

<div class="box-shadow-dark" title="<?= $model->description; ?>"  style="border-radius:2px;direction:rtl; width:250px;height:auto;background-color:whitesmoke;position: relative;float:right; margin: 10px;">

    <div style="position: relative; top:0; border-radius:2px; color:white; background-color: #1b6d85; height:40px;font-size: 18px; font-weight: bold; line-height:40px;" align="center">
        <?= $model['equipment']; ?>
    </div>
    <div style="position: relative;border-radius:2px; color:white; background-color: #0d3349; height:40px;font-size: 18px; font-weight: bold; line-height:40px;" align="center">
        <?= " تعداد کل : ".$model['quantity']; ?>
    </div>
    <div style="position: relative;border-radius:2px; color:#0d3349; background-color: whitesmoke; height:40px;font-size: 18px; font-weight: bold; line-height:40px;" align="center">
        تخصیص مناطق
    </div>


    <div style="color:darkslateblue;padding:10px; height:350px; overflow: auto;">
        <table class="table table-hover">
            <tr>
                <td style="color:darkslategray">
                    منطقه ۲
                </td>
                <td>
                    <?= $model['area2']; ?>
                </td>
            </tr>
            <tr>
                <td style="color:darkslategray">
                    منطقه ۳
                </td>
                <td>
                    <?= $model['area3']; ?>
                </td>
            </tr>
            <tr>
                <td style="color:darkslategray">
                    منطقه ۴
                </td>
                <td>
                    <?= $model['area4']; ?>
                </td>
            </tr>
            <tr>
                <td style="color:darkslategray">
                    منطقه ۵
                </td>
                <td>
                    <?= $model['area5']; ?>
                </td>
            </tr>
            <tr>
                <td style="color:darkslategray">
                    منطقه ۶
                </td>
                <td>
                    <?= $model['area6']; ?>
                </td>
            </tr>
            <tr>
                <td style="color:darkslategray">
                    منطقه ۷
                </td>
                <td>
                    <?= $model['area7']; ?>
                </td>
            </tr>
            <tr>
                <td style="color:darkslategray">
                    منطقه ۸
                </td>
                <td>
                    <?= $model['area8']; ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="position: relative; bottom: 0px;width:100%; height:30px;">
            <a href="<?= $url.$model['id']; ?>" title="ویرایش" class="btn hvr-bounce-in" style="border-radius:50%; width:100%;"><i class="fa fa-edit fa-lg text-success"></i></a>
    </div>

</div>