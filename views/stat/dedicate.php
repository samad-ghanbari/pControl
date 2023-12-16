<?php

/* @var $this yii\web\View */
/* @var $project */
/* @var $projects */
/* @var $area */
/* @var $models */


$this->title = 'PDCP|Report';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>

    <div class="topic-cover bg-gradient" >
        <div style="width:100% ; padding: 20px; color:white;">
            <h3 style="text-align: center; color:#fff;">جدول تخصیص تجهیزات</h3>
            <i class="fa fa-table" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>
            <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/stat/dedicate",
                    'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:auto;']]); ?>
                <label for="prj-form" style="display: block;text-align:center;">انتخاب پروژه</label>
                <select name="id" onchange="this.form.submit()" style="width: 100%;" class="form-control">
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
        </div>

        <?php if($pId > -1)
        { ?>
            <div style="width:95%; margin:auto; background-color: rgba(100,100,100,0.5); border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">
                <br />
                <div style="width: 98%;max-width: 1500px;  margin:auto;">

                    <?php if(sizeof($models) > 0) {
                        if($area > 1){ ?>
                    <table class="table table-hover table-striped enFont" style="direction: rtl; background-color: whitesmoke;width:100%;  font-size: 18px;">
                        <tr style="background-color: #1b6d85; color:white; font-weight: bold;">
                            <td>منطقه</td>
                            <td>تجهیز</td>
                            <td>توضیحات</td>
                            <td>تخصیص یافته</td>
                            <td>سایت ثبت شده</td>
                        </tr>
                        <?php $ded = 0; $used =0;
                        $AREA = 'area' . $area;
                        foreach ( $models as $id=>$model)
                        {
                            $ded = $ded + $model[$AREA][0];
                            $used = $used + $model[$AREA][1];
                            ?>
                            <tr id="<?= 'row'.$id; ?>" class="table-row" style="font-size:14px;" onclick="activateRow(this);">
                                <td><?= $area; ?></td>
                                <td><?= $model['equipment']; ?></td>

                                <td><?= $model['description']; ?></td>


                                <td><?= $model[$AREA][0]; ?></td>
                                <td><?= $model[$AREA][1]; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr style='background-color:darkgoldenrod; font-weight:bold; color:#fff;' class="table-row">
                            <td colspan="3" style="text-align:center;" >مجموع</td>
                            <td><?= $ded; ?></td>
                            <td><?= $used; ?></td>
                        </tr>
                    </table>
            <?php } else { ?>

<!--  legend -->
                            <table class="table table-striped" style="width: 200px; background-color: #fff; direction: rtl;">
                                <tr>
                                    <td>سایت‌های ثبت شده</td>
                                    <td style="background-color: #000;width:20px;"></td>
                                </tr>

                                <tr>
                                    <td>تخصیص داده شده</td>
                                    <td style="background-color: #00f; width:20px;"></td>
                                </tr>
                            </table>
                            <br />

                    <table class="table table-hover table-striped enFont" style="direction: rtl; background-color: whitesmoke; width:100%;  font-size: 18px;">
                            <tr style="background-color: #1b6d85; color:white; font-weight: bold;">
                                <td>تجهیز</td>
                                <td>توضیحات</td>
                                <td>تعداد کل خرید</td>
                                <td>تعداد باقیمانده</td>
                                <td>منطقه ۲</td>
                                <td>منطقه ۳</td>
                                <td>منطقه ۴</td>
                                <td>منطقه ۵</td>
                                <td>منطقه ۶</td>
                                <td>منطقه ۷</td>
                                <td>منطقه ۸</td>
                            </tr>
                            <?php $ded = 0; $used =0;
                            $ded2 =0; $used2=0;
                            $ded3 =0; $used3=0;
                            $ded4 =0; $used4=0;
                            $ded5 =0; $used5=0;
                            $ded6 =0; $used6=0;
                            $ded7 =0; $used7=0;
                            $ded8 =0; $used8=0;
                            foreach ( $models as $id=>$model)
                            {
                                $ded = $ded + $model['total'];
                                $used = $used +$model['left'];

                                $ded2 = $ded2 + $model['area2'][0]; $used2 = $used2 + $model['area2'][1];
                                $ded3 = $ded3 + $model['area3'][0]; $used3 = $used3 + $model['area3'][1];
                                $ded4 = $ded4 + $model['area4'][0]; $used4 = $used4 + $model['area4'][1];
                                $ded5 = $ded5 + $model['area5'][0]; $used5 = $used5 + $model['area5'][1];
                                $ded6 = $ded6 + $model['area6'][0]; $used6 = $used6 + $model['area6'][1];
                                $ded7 = $ded7 + $model['area7'][0]; $used7 = $used7 + $model['area7'][1];
                                $ded8 = $ded8 + $model['area8'][0]; $used8 = $used8 + $model['area8'][1];
                                ?>
                                <tr id="<?= 'row'.$id; ?>" class="table-row" style="font-size:14px;text-align:center; direction:ltr;" onclick="activateRow(this);">
                                    <td><?= $model['equipment']; ?></td>
                                    <td><?= $model['description']; ?></td>
                                    <td><?= $model['total']; ?></td>
                                    <td><?= $model['left']; ?></td>

                                    <td><?= "<span style='color:#000;'>".$model['area2'][1].'</span> / <span style="color:#00f;">'.$model['area2'][0]."</span>"; ?></td>
                                    <td><?= "<span style='color:#000;'>".$model['area3'][1].'</span> / <span style="color:#00f;">'.$model['area3'][0]."</span>"; ?></td>
                                    <td><?= "<span style='color:#000;'>".$model['area4'][1].'</span> / <span style="color:#00f;">'.$model['area4'][0]."</span>"; ?></td>
                                    <td><?= "<span style='color:#000;'>".$model['area5'][1].'</span> / <span style="color:#00f;">'.$model['area5'][0]."</span>"; ?></td>
                                    <td><?= "<span style='color:#000;'>".$model['area6'][1].'</span> / <span style="color:#00f;">'.$model['area6'][0]."</span>"; ?></td>
                                    <td><?= "<span style='color:#000;'>".$model['area7'][1].'</span> / <span style="color:#00f;">'.$model['area7'][0]."</span>"; ?></td>
                                    <td><?= "<span style='color:#000;'>".$model['area8'][1].'</span> / <span style="color:#00f;">'.$model['area8'][0]."</span>"; ?></td>

                                </tr>
                                <?php
                            }
                            ?>
                            <tr style='background-color:darkgoldenrod;direction:ltr;text-align:center; font-weight:bold; color:#fff;' class="table-row">
                                <td colspan="2" style="text-align:center;" >مجموع</td>
                                <td><?= $ded; ?></td>
                                <td><?= $used; ?></td>
                                <td><?= "<span style='color:#000;'>".$used2.'</span> / <span style="color:#00f;">'.$ded2."</span>"; ?></td>
                                <td><?= "<span style='color:#000;'>".$used3.'</span> / <span style="color:#00f;">'.$ded3."</span>"; ?></td>
                                <td><?= "<span style='color:#000;'>".$used4.'</span> / <span style="color:#00f;">'.$ded4."</span>"; ?></td>
                                <td><?= "<span style='color:#000;'>".$used5.'</span> / <span style="color:#00f;">'.$ded5."</span>"; ?></td>
                                <td><?= "<span style='color:#000;'>".$used6.'</span> / <span style="color:#00f;">'.$ded6."</span>"; ?></td>
                                <td><?= "<span style='color:#000;'>".$used7.'</span> / <span style="color:#00f;">'.$ded7."</span>"; ?></td>
                                <td><?= "<span style='color:#000;'>".$used8.'</span> / <span style="color:#00f;">'.$ded8."</span>"; ?></td>

                            </tr>
                        </table>

            <?php } ?>
                    <br style="clear: both;" />
                    <br />
                    <a href="<?= Yii::$app->request->baseUrl.'/stat/export_dedicate?id='.$pId; ?>" class="btn btn-success" style="height:38px; float: left; width:150px;"><i class="fa fa-file-excel text-white" ></i> خروجی اکسل </a>
                <?php } else { ?>
                <h4 style="color:#fff; text-align:center; font-weight:bold; direction:rtl;">اطلاعات تخصیص تجهیز برای این پروژه در دسترس نیست.</h4>
                <?php } ?>
                <br style="clear: both;" />
                </div>
                <br />
            </div>


            <?php
        }
        else { ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>

    </div>

<?php
$script =<<< JS
function activateRow(obj)
{
    $(".selectedRow").removeClass("selectedRow");
    $(obj).addClass("selectedRow");
}
JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>