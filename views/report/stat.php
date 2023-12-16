<?php

/* @var $this yii\web\View */
/* @var  $operations */
/* @var   $choices */
/* @var  $areas */
/* @var $projectName */
/* @var $phaseNo */
/* @var $aex */
/* @var $exchanges */
/* @var $areaSelection */
/* @var $opVisibility */


$this->title = 'PDCP|Stat Report';
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

Yii::$app->formatter->nullDisplay = "";
$pId = -1;
if(isset($project['id'])) $pId = $project['id'];
?>

    <div class="topic-cover bg-gradient" >
        <div style="width:100% ; padding: 20px; color:white;">
            <h3 style="text-align: center; color:#fff;">گزارش آماری</h3>
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/excel.png'?>" style="height: 100px; width:auto; display: block; margin:auto;">

            <div style="min-width:200px; max-width:500px; margin:10px auto; border-radius:20px; background-color:rgba(100,100,100,0.5); padding:20px;">
                <?php
                $form = ActiveForm::begin([
                    'id'=>"projectsForm",
                    'method' => 'GET',
                    'action' => Yii::$app->request->baseUrl."/report/stat",
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

            <div id="Sidebar" class="sidenav">
                <a  style="cursor: pointer;" class="closebtn" onclick="closeNav(event)">&times;</a>
                <h4 style="text-align: center; color:darkslateblue;">ویژگی های پروژه</h4>
                <br style="clear: both" />
                <?php
                foreach ($operations as $op)
                {
                    echo "<a>";
                    echo Html::checkbox('op-'.$op['id'], false,['label'=>$op['operation'], 'op-id'=>$op['id'],  'style'=>'direction:rtl;', 'class'=>"row-chb", 'onclick'=>'checkBoxClicked(this)']);
                    echo "</a>";
                }

                echo "<a>";
                echo Html::checkbox('fromMod', false,['label'=>'ویرایش شده از تاریخ', 'op-id'=>-1,  'style'=>'direction:rtl;', 'class'=>"row-chb", 'onclick'=>'checkBoxClicked(this)']);
                echo "</a>";

                echo "<a>";
                echo Html::checkbox('toMod', false,['label'=>'ویرایش شده تا تاریخ', 'op-id'=>-2,  'style'=>'direction:rtl;', 'class'=>"row-chb", 'onclick'=>'checkBoxClicked(this)']);
                echo "</a>";

                ?>
                <br style="clear: both;" />
                <br />
            </div>

            <div onclick="closeNav(event);" style="width:95%; margin:auto; background-color: whitesmoke; border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">

                <div style="width: 90%;max-width: 1000px;  margin:auto;">
                    <?php $form = ActiveForm::begin(['method'=>"post",'action'=>Yii::$app->request->baseUrl.'/report/stat?id='.$pId, 'options' => ['style' => "direction:rtl;"]]); //, 'onsubmit'=>"return confirmForm()" ?>

                    <div style="float: right; margin:10px; width:25%; max-width: 150px;">
                        <label style='margin-top:10px;width:100%; display: block;'  for="area-input" >منطقه</label>
                        <?= Html::dropDownList('search[area]',$aex[0], $areaSelection,['onchange'=>"areaChanged(this)", 'style'=>"height:40px; width:100%;"]); ?>
                    </div>

                    <div style="float: right; width:25%;margin:10px; max-width: 300px;">
                        <label style='margin-top:10px;'  for="center-id" >مرکز اصلی</label>
                        <?= Html::dropDownList('search[exchange_id]', $aex[1],$exchanges[$aex[0]] ,['id'=>'eselect', 'style'=>"height:40px; width:100%;display:block;"]); ?>
                    </div>

                    <div style="float: right; width:25%;margin:10px; max-width: 150px;">
                        <label style='margin-top:10px;'  for="phase-input" >فاز</label>
                        <?= Html::dropDownList('search[phaseNo]','', [-1=>'کل فازها', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10'],['id'=>'phase-input', 'style'=>'height:40px;width:100%; max-width:300px;display:block;']); ?>
                    </div>

                    <br style="clear:both;">
                    <hr style="border-top: 1px solid lightgray;">

                    <!--    filter btn    -->
                    <h4 style="text-align: center; color:#1b6d85">لطفا ویژگی های مد نظر خود را انتخاب نمایید</h4>
                    <a onclick="openNav(event);" style="cursor: pointer; text-align:center;display: block; width:80px; margin:auto;" class="hvr-bounce-in"><i class="fa fa-filter fa-2x" style="color:#1b6d85"></i></a>

                    <br style="clear: both" />
                    <!--  operations     -->
                    <?php
                    foreach ($operations as $op)
                    {
                        $name = $op['id'];
                        echo '<div id="op-'.$op['id'].'" style="float: right; display:none; width:50%;margin:10px; max-width: 300px;">';
                        echo "<label style='margin-top:10px;' for='".$name."' >".$op['operation']."</label>";
                        echo Html::dropDownList("search[$name]",'-1', $choices[$op['id']],['id'=>$name, 'style'=>'height:40px;width:300px;display:block;']);
                        echo '</div>';
                    }

                    $t = getdate();
                    $d = $t['mday'];
                    $y = $t['year'];
                    $m = $t['mon'] - 1;
                    $from = $y.'/'.$m.'/'.$d;

                    ?>


                    <input id="from-flag" type="hidden" name="search[from-flag]" value=0>
                    <div id="fromDP" style="float: right; width:50%;margin:10px; max-width: 300px;display: none;">
                        <label style='margin-top:10px;' for='".$name."' >ویرایش شده از تاریخ</label>
                        <?= mrlco\datepicker\Datepicker::widget([
                            'name' => 'search[from-mod]',
                            'value' =>'',
                            'template' => '{addon}{input}',
                            'options'=>['style'=>"height:40px;width:300px;display:block;"],
                            'clientOptions' => ['format' => 'YYYY/MM/DD']
                        ]); ?>
                    </div>

                    <input id="to-flag" type="hidden" name="search[to-flag]" value=0>
                    <div id="toDP" style="float: right; width:50%;margin:10px; max-width: 300px; display: none;">
                        <label style='margin-top:10px;' for='".$name."' >ویرایش شده تا تاریخ</label>
                        <?= mrlco\datepicker\Datepicker::widget([
                            'name' => 'search[to-mod]',
                            'value' => '',
                            'template' => '{addon}{input}',
                            'options'=>['style'=>"height:40px;width:300px;display:block;"],
                            'clientOptions' => ['format' => 'YYYY/MM/DD']
                        ]);
                        echo Html::hiddenInput('toFlag', false, ['id'=>'toFlag']);
                        ?>
                    </div>


                    <br style="clear: both;" />
                    <br />
                    <button type="submit" class="btn btn-success" style="height:40px;width:100px; float: left;"> تایید </button>
                    <?php ActiveForm::end(); ?>
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
$exchs = json_encode($exchanges);
$script =<<< JS
var exchs = $exchs;
var navOpened = false;
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