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
            <h3 style="text-align: center; color:#fff;">جدول آماری</h3>
            <i class="fa fa-table" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>

        <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
            <?php
            $form = ActiveForm::begin([
                'id'=>"projectsForm",
                'method' => 'GET',
                'action' => Yii::$app->request->baseUrl."/stat/tablestat",
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
                    <?php $form = ActiveForm::begin(['method'=>"post",'action'=>Yii::$app->request->baseUrl.'/stat/tablestat?id='.$pId, 'layout'=>'horizontal', 'options' => ['style' => "direction:rtl;"]]); ?>

                    <label for="area-input" style="color:white;" >منطقه</label>
                    <?= Html::dropDownList('search[area]',$searchParams['area'], $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px;"]); ?>

                    <label for="center-id"  style="color:white;">مرکز اصلی</label>
                    <?= Html::dropDownList('search[exchange_id]', $searchParams['exchange_id'],$exchanges[$searchParams['area']] ,['id'=>'eselect', 'style'=>"height:40px;"]); ?>

                    <label for="phase-input"  style="color:white;">فاز</label>
                    <?= Html::dropDownList('search[phaseNo]',$searchParams['phaseNo'], [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100px;']); ?>
                   
                    <button type="submit" class="btn btn-success" style="height:38px;"><i class="fa fa-search text-white" ></i> جستجو </button>
                    <?php ActiveForm::end(); ?>
                    <!--search-->
                    <hr style="border-top: 1px dotted mediumvioletred;">
                    <br />


                    <?php if(empty($tableInfo)){ ?>
                        <div style="width: 100%; max-width: 700px; margin: auto;">
                            <h4 class="text-center text-danger" style="direction: rtl;">رکوردی یافت نشد.</h4>
                        </div>
                    <?php } ?>


                    <div style="width: 100%; min-height: 200vh; position: relative; padding-right: 310px;">

                        <div style="width: 90%; max-width: 700px; margin:auto;">
                            <table class="table table-hover table-striped" style="direction: rtl; background-color: whitesmoke;  font-size: 18px;">
                                <tr style="background-color: #1b6d85; color:white; font-weight: bold;">
                                    <td>عنوان</td>
                                    <td>تعداد</td>
                                    <td>درصد</td>
                                </tr>
                                <?php
                                foreach ($tableInfo as $info)
                                {
                                    $style='';
                                    $info = ($info == null)? "" : $info;
                                    if(str_contains($info['0'], 'نشده'))
                                        $style='style="background-color:pink;"';
                                    ?>
                                    <tr <?= $style; ?> id="<?= $info[3]; ?>" class="table-row" onclick="activateRow(this);">
                                        <td><?= $info['0']; ?></td>

                                        <td><?= $info['1']; ?></td>

                                        <td><?= $info['2']; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>

                        <div style="width: 300px; background-color: #ddd;padding:5px;position:absolute; border:1px solid gray; direction:rtl; top:0; right:0;">
                            <h4 style="text-align: center; color:#0c5460;">فیلتر گزینه ها</h4>
                            <hr />

                            <?php
                            echo "<div>";
                            echo Html::checkbox('ckb', false,['label'=>'انتخاب همه موارد','row-id'=>-1,  'style'=>'direction:rtl;', 'id'=>'select-all', 'onclick'=>'checkBoxClicked(this)']);
                            echo "</div><hr />";


                            foreach ($tableInfo as $row)
                            {
                                echo "<div>";
                                echo Html::checkbox('ckb', true,['label'=>$row[0],'row-id'=>$row[3],  'style'=>'direction:rtl;', 'class'=>"row-chb", 'onclick'=>'checkBoxClicked(this)']);
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>

                </div>
            <?php }
            else { ?>
                <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>







    </div>
    <br />



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