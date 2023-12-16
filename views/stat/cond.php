<?php

/* @var $this yii\web\View */
/* @var  $operations */
/* @var   $choices */
/* @var  $areas */
/* @var $project */
/* @var $phaseNo */
/* @var $search */
/* @var $exchanges */
/* @var $areaSelection */


$this->title = 'PDCP|Report';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>

    <div class="topic-cover bg-gradient" >
        <div style="width:100% ; padding: 20px; color:white;">
            <h3 style="text-align: center; color:#fff;">آمار جزییات</h3>
            <i class="fa fa-table" style="font-size:48px; color:white; text-align:center;display:block; margin:auto;"></i>

            <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/stat/cond",
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

            <div style="width:99%; margin:auto;  border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">
                <br />
                <div style="width:100%;">
                    <?php $form = ActiveForm::begin(['method'=>"post",'action'=>Yii::$app->request->baseUrl.'/stat/cond?id='.$pId, 'options' => ['style' => "direction:rtl;"]]); ?>

                    <div style="float: right; margin:10px; width:25%; max-width: 150px;">
                        <label style='color:#fff;margin-top:10px;width:100%; display: block;'  for="area-input" >منطقه</label>
                        <?= Html::dropDownList('search[area]',$search['area'], $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px; width:100%;"]); ?>
                    </div>

                    <div style="float: right; width:25%;margin:10px; max-width: 300px;">
                        <label style='color:#fff; margin-top:10px;'  for="center-id" >مرکز اصلی</label>
                        <?= Html::dropDownList('search[exchange_id]', $search['exchange_id'],$exchanges[$search['area']] ,['id'=>'eselect', 'style'=>"height:40px; width:100%;display:block;"]); ?>
                    </div>

                    <div style="float: right; width:25%;margin:10px; max-width: 150px;">
                        <label style='color:#fff;margin-top:10px;'  for="phase-input" >فاز</label>
                        <?= Html::dropDownList('search[phaseNo]',$search['phase'], [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100%; max-width:300px;display:block;']); ?>
                    </div>

                    <div style="float: right; width:25%;margin:10px; max-width: 300px;">
                        <label  style='color:#fff;margin-top:10px;' for="reptype-input" >نوع گزارش</label>
                        <?= Html::dropDownList('search[repType]',$search['report'], [1=>'گزارش افقی', 2=>'گزارش عمودی'],['id'=>'repType-input', 'style'=>'height:40px;width:100%; max-width:300px;display:block;']); ?>
                    </div>
                    <br style="clear:both;">
                    <br />
                    <hr style="width:60%;margin:auto;">
                    <h3 style="text-align:center; color:#fff;">ویژگی‌های پروژه</h3>

                    <!--  operations     -->
                    <?php
                    foreach ($operations as $op)
                    {
                        if($op['type_id'] == 1)
                        {
                            $name = $op['id'];
                            echo '<div id="op-'.$op['id'].'" style="float: right; width:50%;margin:10px; max-width: 200px;">';
                            echo "<label style='color:#fff;margin-top:10px;' for='".$name."' >".$op['operation']."</label>";
                            echo Html::dropDownList("search[$name]",$search[$op['id']], $choices[$op['id']],['id'=>$name, 'style'=>'height:40px;width:200px;display:block;']);
                            echo '</div>';
                        }
                    }

                    ?>

                    <input id="from-flag" type="hidden" name="search[from-flag]" value=0>
                    <div id="fromDP" style="color:#fff;float: right; width:50%;margin:10px; max-width: 200px;">
                        <label style='margin-top:10px;' for='".$name."' >ویرایش شده از تاریخ</label>
                        <?= mrlco\datepicker\Datepicker::widget([
                            'name' => 'search[from-mod]',
                            'value' =>$search['from'],
                            'template' => '{addon}{input}',
                            'options'=>['style'=>"height:40px;width:200px;display:block;"],
                            'clientOptions' => ['format' => 'YYYY/MM/DD']
                        ]); ?>
                    </div>

                    <input id="to-flag" type="hidden" name="search[to-flag]" value=0>
                    <div id="toDP"  style="color:#fff;float: right; width:50%;margin:10px; max-width: 200px;">
                        <label style='margin-top:10px;' for='".$name."' >ویرایش شده تا تاریخ</label>
                        <?= mrlco\datepicker\Datepicker::widget([
                            'name' => 'search[to-mod]',
                            'value' => $search['to'],
                            'template' => '{addon}{input}',
                            'options'=>['style'=>"height:40px;width:200px;display:block;"],
                            'clientOptions' => ['format' => 'YYYY/MM/DD']
                        ]);  ?>
                    </div>


                    <br style="clear: both;" />
                    <br />
                    <button type="submit" name="act" value="search" class="btn btn-success" style="height:38px; float: left; width:80px;margin-left:20px;"><i class="fa fa-search text-white" ></i> جستجو </button>
                    <button type="submit" name="act" value="export" class="btn btn-primary" style="height:38px; float: right; width:150px;"><i class="fa fa-file-excel text-white" ></i> خروجی اکسل </button>
                    <?php ActiveForm::end(); ?>
                    <br style="clear: both;" />
                    <br />
                    <hr style="width:60%;margin:auto;">
                    <br />
                    <?php if(!empty($tableInfo)){
                    ?>
                            <div style="width:100%; overflow:auto;">
                        <table class="table table-striped table-hover table-bordered" style="background-color:#eee; margin:auto; color:#eee;direction:rtl;">
                            <tr style="background-color: #1b6d85; color:white; font-weight: bold;">
                                <td style="text-align:center;">منطقه</td>
                                <td style="text-align:center;">مرکز</td>
                                <td style='text-align:center;'>نام</td>
                                <td style='text-align:center;'>شناسه سایت</td>
                                <td style='text-align:center;'>کد کافو</td>
                                <td style='text-align:center;'>آدرس</td>
                                <td style='text-align:center;'>موقعیت</td>
                                <td style='text-align:center;'>فاز</td>
                                <td style='text-align:center;'>زمان آخرین ویرایش</td>
                                <td style='text-align:center;'>درصد پیشرفت</td>

                                <?php
                                foreach($colMap as $col=>$array)
                                {
                                    echo "<td style='text-align:center;'>".$array['title']."</td>";
                                }
                                ?>
                            </tr>

                            <?php
                            $rowId = 0;
                            foreach($tableInfo as $exId=>$record)
                            {
                                $rowId++;
                                echo "<tr class='table-row enFont' style='color:#000; font-weight:bold;direction:ltr;' id='".$rowId."' onclick='activateRow(this);'>";
                                echo "<td style='text-align:center;'>".$record['area']."</td>";
                                echo "<td style='text-align:center;'>".$record['center_name']."</td>";
                                echo "<td style='text-align:center;'>".$record['name']."</td>";
                                echo "<td style='text-align:center;'>".$record['site_id']."</td>";
                                echo "<td style='text-align:center;'>".$record['kv_code']."</td>";
                                echo "<td style='text-align:center;'>".$record['address']."</td>";
                                echo "<td style='text-align:center;'>".$record['position']."</td>";
                                echo "<td style='text-align:center;'>".$record['phase']."</td>";
                                echo "<td style='text-align:center;'>".$record['modified_ts']."</td>";
                                echo "<td style='text-align:center;'>".$record['percentage']." % "."</td>";

                                foreach($colMap as $col=>$array) // [title,opId]
                                {
                                    $opId = $array['id'];
                                    $val = "";
                                    if(isset($record[$opId]))
                                        {
                                            $val = $record[$opId];
                                            $type = $opMap[$opId]['type_id'];
                                            if($type == 1) $val = $choiceMap[$val];
                                        }
                                    echo "<td style='text-align:center;'>".$val."</td>";
                                }

                                echo "</tr>";
                            }
                            ?>

                        </table>
                            </div>
                    <?php }?>

                </div>

            <?php
        }
        else { ?>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/logo.png'; ?>" style="display: block; margin:20px auto; width:100px;">
        <?php } ?>

                <br style="clear: both;" />
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

function openNav(event)
{
  document.getElementById("Sidebar").style.right = "0";
  event.stopPropagation();
}

function closeNav(event)
{
   document.getElementById("Sidebar").style.right = "-450px";
   event.stopPropagation();
}

function checkBoxClicked(obj)
{
    var op_id = $(obj).attr('op-id');
    var ok = $(obj).prop('checked');
    if(ok)
        {
            if(op_id == -1)
                {
                $("#fromDP").css('display', 'block');
                $("#from-flag").val(1);
                }
            else if(op_id == -2)
                {
                $("#toDP").css('display', 'block');
                $("#to-flag").val(1);
                }
            else 
                {
                    $('#op-'+op_id+' select').val("-1");
                    $('#op-'+op_id).css('display', 'block');
                }
                
        }
    else 
        {
            if(op_id == -1)
                {
                $("#fromDP").css('display', 'none');
                $("#from-flag").val(0);
                }
            else if(op_id == -2)
                {
                $("#toDP").css('display', 'none');
                $("#to-flag").val(0);
                }
            else 
                {
                    $('#op-'+op_id+' select').val("-1");
                    $('#op-'+op_id).css('display', 'none');
            }
        }
}






JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>