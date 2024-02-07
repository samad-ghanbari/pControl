<?php

/* @var $this yii\web\View */
/* @var  $tableInfo */
/* @var $projectName */
/* @var $phaseNo */
/* @var $searchParams */
/* @var $exchanges */
/* @var $areaSelection */

$this->title = 'PDCP|Statistics';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>
    <div class="topic-cover bg-gradient" style="padding:10px;" >
            <h3 style="text-align: center; color:#fff;">جدول جامع آماری</h3>
            <i class="fa fa-table" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>

        <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
            <?php
            $form = ActiveForm::begin([
                'id'=>"projectsForm",
                'method' => 'GET',
                'action' => Yii::$app->request->baseUrl."/stat/totaltablestat",
                'options'=>['style'=>'direction:rtl; min-width:100px; max-width:400px; margin:auto;']]); ?>
            <label for="prj-form" style="display: block;text-align:center; color:white;">انتخاب پروژه</label>
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

        <?php if($pId > -1)
            { ?>
                <div style="width:95%; margin:auto; background-color: rgba(100,100,100,0.5); border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">

                    <!-- search-->
                    <?php $form = ActiveForm::begin(['method'=>"post",'action'=>Yii::$app->request->baseUrl.'/stat/totaltablestat?id='.$pId, 'layout'=>'horizontal', 'options' => ['style' => "direction:rtl;"]]); ?>

                    <label for="area-input" style="color:white;" >منطقه</label>
                    <?= Html::dropDownList('search[area]',$searchParams['area'], $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px;"]); ?>

                    <label for="center-id"  style="color:white;">مرکز اصلی</label>
                    <?= Html::dropDownList('search[exchange_id]', $searchParams['exchange_id'],$exchanges[$searchParams['area']] ,['id'=>'eselect', 'style'=>"height:40px;"]); ?>


                    <label for="phase-input"  style="color:white;">فاز</label>
                    <?= Html::dropDownList('search[phase]',$searchParams['phase'], [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100px;']); ?>
            

                    <button type="submit" class="btn btn-success" style="height:38px;"><i class="fa fa-search text-white" ></i> جستجو </button>
                    <?php ActiveForm::end(); ?>
                    <!--search-->
                    <hr style="border-top: 1px dotted mediumvioletred;">
                    <br />

                    <div style="width: 100%; min-height: 80vh; overflow: auto;">

                        <table class="table table-striped table-hover table-bordered" style="background-color:#eee;width:90%; margin:auto; color:#eee;direction:rtl;">
                        <!-- table header -->
                        <tr style="background-color: #1b6d85; color:white; font-weight: bold;">
                                <td style="text-align:center;">عنوان</td>
                                
                                
                                <?php
                                if($searchParams['area'] == -1)
                                {
                                    echo "<td style='text-align:center;'>تخصیص</td>";
                                    echo "<td style='text-align:center;'>اقدام‌شده</td>";
                                }
                                else 
                                    echo "<td style='text-align:center;'>تعداد سایت</td>";
                                
                                foreach($opMap as $col=>$array)
                                    {
                                        echo "<td style='text-align:center;'>".$array['title']."</td>";
                                    }
                                ?>
                                <td style="text-align:center;">درصد پیشرفت</td>
                        </tr>

                        <?php 
                            $rowId = 1;
                            $info = $tableInfo['info'];
                            $details = $tableInfo['details'];
                        ?>

                        <tr class="table-row enFont" style="background-color:#93C763; color:#000; font-weight:bold;direction:ltr;" id="<?=$rowId?>" onclick="activateRow(this);">
                            <td style='text-align:center;'><?= $info['title']; ?></td>
                            
                            <?php
                                if($searchParams['area'] == -1)
                                {
                                    echo "<td style='text-align:center;'>".$info['dedicated']."</td>";
                                    echo "<td style='text-align:center;'>".$info['onAction']."</td>";
                                }
                                else{
                                    echo "<td style='text-align:center;'>".$info['count']."</td>";
                                }
                                foreach($opMap as $col=>$array)
                                {
                                    $val = 0;
                                    if(isset($info['attributes'][$array['id']]))
                                    {
                                        $temp = $info['attributes'][$array['id']];
                                        $opId = $array['id'];
                                        $count = $temp[1];
                                        $perc = $temp[2];
                                        // $val = $count." [".$perc." ]";
                                        $val = $count;
                                    }
                                    echo "<td style='text-align:center;'>".$val."</td>";
                                }
                            ?>
                            <td style='text-align:center;'><?= $info['progress']; ?></td>
                        </tr>

                            <!--  details  -->
                            <?php
                            foreach($details as $detail)
                            {
                                $rowId++;
                                echo "<tr class='table-row enFont' style='color:#000; font-weight:bold;direction:ltr;' id='".$rowId."' onclick='activateRow(this);'>";
                                echo "<td style='text-align:center;'>".$detail['title']."</td>";
                                
                                if($searchParams['area'] == -1)
                                {
                                    echo "<td style='text-align:center;'>".$detail['dedicated']."</td>";
                                    echo "<td style='text-align:center;'>".$detail['onAction']."</td>";
                                }
                                else
                                {
                                    echo "<td style='text-align:center;'>".$detail['count']."</td>";
                                }

                                    foreach($opMap as $col=>$array)
                                    {
                                        $val = 0;
                                        if(isset($detail['attributes'][$array['id']]))
                                        {
                                            $temp = $detail['attributes'][$array['id']];
                                            $opId = $array['id'];
                                            $count = $temp[1];
                                            $perc = $temp[2];
                                            //  $val = $count." [".$perc." ]";
                                            $val = $count;
                                        }
                                        echo "<td style='text-align:center;'>".$val."</td>";
                                    }

                                echo "<td style='text-align:center;'>".$detail['progress']."</td>";

                                echo "</tr>";
                            }
                            ?>

                        </table>
                        <br />
                        <br />
                        <a href="<?= Yii::$app->request->baseUrl.'/stat/report_tablestat?id='.$pId.'&AREA='.$searchParams['area'].'&EXCHANGE_ID='.$searchParams['exchange_id'].'&PHASE='.$searchParams['phase']; ?>" class="btn btn-success" style="height:38px; float: left; width:150px;"><i class="fa fa-file-excel text-white" ></i> خروجی اکسل </a>

                        <br style="clear:both;" />
                    </div>
                </div>
            <?php }
            else { ?>
                <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>
    <br />
    </div>
<?php
$exchs = json_encode($exchanges);
$script =<<< JS
var exchs = $exchs;
function areaChanged(obj)
{
    var area  = $(obj).val();
    var eselect = $("#eselect");
    $(eselect).empty();
    var exchanges = exchs[area];

    for(var id in exchanges)
        {
            var o = new Option(exchanges[id], id);
            $(eselect).append(o);
        }
    
    $(eselect).val("-1");
}

function activateRow(obj)
{
    $(".selectedRow").removeClass("selectedRow");
    $(obj).addClass("selectedRow");
}

function checkBoxClicked(obj)
{
    var ok = $(obj).prop('checked');
    var id = $(obj).attr("row-id");
    if(id == -1)
        {
            if(ok == true)
                { 
                    //select all
                    $(".row-chb").prop('checked', true);
                    $(".table-row").show();
                    return ;
                }
            else 
                {
                    //deselect all   
                    $('.row-chb').prop('checked', false);
                    $(".table-row").hide();
                    return;
                }
        }
    
    if(ok == true)
        $("#"+id).show();
    else 
        $("#"+id).hide();
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>
