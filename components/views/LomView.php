<?php
use yii\helpers\Url;
/* @var $model */
/* @var $area */
/* @var $edit */

if($admin)
    $url = Yii::$app->request->baseUrl."/projects/edit_lom?id=";
else
    $url = Yii::$app->request->baseUrl."/owner/edit_lom?id=";

$left = $model['quantity'] - ($model['area2']+$model['area3']+$model['area4']+$model['area5']+$model['area6']+$model['area7']+$model['area8']);

?>

<div class="box-shadow-dark" title="<?= $model->description; ?>"  style="border-radius:2px;direction:rtl; width:350px;height:auto;background-color:whitesmoke;position: relative;float:left; margin: 10px;">

    <div class="enFont" style="position: relative; top:0; border-radius:2px; color:white; background-color: #1b6d85; height:40px;font-size: 14px;direction:ltr; font-weight: bold; line-height:40px;" align="center">
        <?= $model['equipment']; ?>
    </div>

    <div style="color:darkslateblue;padding:10px; height:auto; overflow: auto;">
        <table class="table table-hover table-striped">
            <?php if($area == -1){ ?>

                <tr>
                    <td colspan="2" style="background-color: lightgrey; color:#000;text-align:center;">
                        <?= " تعداد کل : ".$model['quantity']; ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="background-color: gray; color:white;text-align:center;font-weight:bold;">
                        <?= " مانده : ".$left; ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="background-color: #fff; color:#000;text-align:center;">
                        تخصیص مناطق
                    </td>
                </tr>

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
            <?php } else if($area > 1) { ?>
            <tr>
                <td style="color:darkslategray">
                    <?= " منطقه ".$area; ?>
                </td>
                <td>
                    <?= $model['area'.$area]; ?>
                </td>
            </tr>
            <?php } ?>

        </table>
        <br style="clear:both; "/>
    </div>

<?php if($edit == true){ ?>
    <div style="position: relative; bottom: 0px;width:100%; height:30px;">
            <a href="<?= $url.$model['id']; ?>" title="ویرایش" class="btn hvr-bounce-in" style="border-radius:50%; width:100%;"><i class="fa fa-edit fa-lg text-success"></i></a>
    </div>
    <?php } ?>
</div>