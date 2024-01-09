<?php

/* @var $this yii\web\View */
/* @var  $operations */
/* @var $choices */
/* @var  $records */
/* @var  $lom */
/* @var  $exchange_id */
/* @var $exchangeModel */
/* @var $areas */
/* @var $exchanges */
/* @var $userProject */
/* @var $user */
/* @var $searchParams */
/* @var $bs */
/* @var $weight */
/* @var $project_weight */
/* @var $bsExcept */
/* @var $lomDetailModel */
/* @var $projectLom */

$rw = $userProject['rw'];
$siteEditable = $userProject['site_editable'];

$USER_ROLE = $user['action_role'];

use yii\bootstrap\ActiveForm;

$project = ['project'=>''];
$sessoin = Yii::$app->session;
if(isset($sessoin['project']))
    $project= $sessoin['project'];

$this->title = 'PDCP|View';
use yii\helpers\Html;
Yii::$app->formatter->nullDisplay = "";

$qp="";
foreach ($searchParams as $p=>$v)
{
    if(empty($qp))
        $qp = $qp. "search[" . $p . ']=' . $v;
    else
        $qp = $qp. "&search[" . $p . ']=' . $v;
}


if(isset($searchParams['page']))
    $qp = $qp.'&page='.$searchParams['page'];

if($project_weight > 0) $weight = round(100*$weight/$project_weight, 1);
$progressBarClass = "progress-bar progress-bar-danger progress-bar-striped active";
if( ($weight <= 70) && ($weight >= 50) )
    $progressBarClass = "progress-bar progress-bar-warning progress-bar-striped active";
else if($weight > 70)
    $progressBarClass = "progress-bar progress-bar-success progress-bar-striped active";

?>

    <p class="backicon">
        <a href="<?= 'index?'.$qp; ?>"><i class="fa fa-chevron-left fa-2x" style="color:white;position: relative;"></i></a>
    </p>

    <div class="topic-cover bg-gradient">
        <h2 style="text-align: center; color:#fff;"> نمایش اطلاعات رکورد </h2>
        <h3 style="text-align: center; color:#fff;"  class="enFont"><?= $exchangeModel->name; ?></h3>
        <h4 style="text-align: center; color:#fff;direction: rtl;"><?= ' پروژه '.$project['project']; ?></h4>

        <div style="width:95%; margin:auto; background-color: rgba(100,100,100,0.2); border-radius: 10px;padding:10px; min-height:80vh;" class="box-shadow-dark">

<!-- && ($USER_ROLE == 'design') -->
            <?php if( (($rw == 1) && ($siteEditable == 1)  ) || ($rw && $user['admin']) ){ ?>
                <p style="width:80%; margin:auto;">
                    <button class="btn btn-info" onclick="$('#editExchangeOverlay').css('height','100%')" title='ویرایش مرکز / سایت'>ویرایش مرکز / سایت</button>
                </p>
            <?php } ?>


            <h4 style="text-align: center; color:#fff; ">درصد پیشرفت</h4>
            <div class="progress" style="height: 30px; border:1px solid green; border-radius:0px; margin:5px auto; width: 80%; max-width: 400px;">
                <?php if( $weight < 30) echo $weight. "%"; ?>
                <div class=" <?= $progressBarClass; ?> " role="progressbar"
                     aria-valuenow="<?= $weight; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $weight; ?>%">
                    <?php if($weight >= 30) echo $weight. "%"; ?>
                </div>
            </div>


            <table class="table table-hover" style="width:80%; margin: auto;direction:rtl; background-color:#fff ; font-size: 16px;">

                <!-- LOM-->
                <tr>
                    <?php if( (($rw == 1) && ($siteEditable == 1)) || ( ($rw==1) && $user['admin']) ){ ?>
                        <td colspan="2" style="color: white; background-color: orange;text-align: center;">
                            LOM
                        </td>
                        <td>
                            <button exch-id="<?= $exchange_id; ?>" style='width:100%; border: none;background:transparent;' onclick='addLom(this.getAttribute("exch-id"))'><i class='fa fa-plus text-success'></i></button>
                        </td>
                    <?php } else { ?>
                        <td colspan="3" style="color: white; background-color: orange;text-align: center;">
                            LOM
                        </td>
                    <?php } ?>
                </tr>
                <?php
                foreach ($lom as $id=>$lm){?>
                    <tr>
                        <td id="lom-equipment" class="enFont"><?= $lm['equipment']; ?> </td>
                        <td id="lom-quantity"><?= $lm['quantity']; ?></td>
                        <?php if( (($rw == 1) && ($siteEditable == 1)) || ($rw && $user['admin']) ){ ?>
                            <td><button lom-id="<?= $id; ?>" lom-equipment="<?= $lm['equipment']; ?>" lom-quantity="<?= $lm['quantity']; ?>" style='width:100%; border: none;background:transparent;' onclick='remLom(this);'><i class='fa fa-times text-danger'></i></button></td>
                        <?php } else { ?>
                            <td></td>
                        <?php } ?>
                    </tr>
                <?php } ?>

                <tr><td colspan="3" style="color: white; background-color: orange;"></td></tr>

                <?php

                foreach($records as $id=>$array)
                {
                    $exchId = $id;
                    foreach ($array as $param=>$value)
                    {
                        $style ="direction:rtl;";
                        $value['value'] = ($value['value'] == null)? "" : $value['value'];
                        if(str_contains($value['value'], 'نشده') || (str_contains($value['value'], 'ندارد')) )
                            $style = 'style="background-color:pink;direction:rtl;"';


                        if( ($bs == true) && (in_array($value['opId'],$bsExcept)) )
                        {
                            continue;
                        }
                        else
                        {
                            echo "<tr onclick='activateRow(this);' ".$style." ><td style='font-weight: bold; direction: ltr; text-align:right;'>";
                            echo $param;
                            echo "</td><td class='enFont' style='font-size:16px;'>";
                            if(isset($value['value']))
                                echo $value['value'];
                            echo "</td><td >";
                            $kv_size = (!str_contains($value['value'], 'بزرگ')) && (!str_contains($value['value'], 'کوچک'));
                            $choice_done = (!str_contains($value['value'], ' شده') || $user['admin']);


                            $PERMISSION = false;
                            $permissions = [];
                            if($value['opId'] > 0)
                                if(isset($operations[$value['opId']]['permission']))
                                    $permissions = $operations[$value['opId']]['permission'];

                            if(isset($permissions[$USER_ROLE]))
                                if($permissions[$USER_ROLE] == true)
                                    $PERMISSION = true;

                            if($USER_ROLE == "no-action") $PERMISSION = false;

//                          if(($siteEditable == 1) && $choice_done) $choice_done = 1;
                            if($PERMISSION)
                            if(($rw == 1) && (($value['opId'] > 0) && ($value['type'] != 4) && ($value['type'] != 0) && ($kv_size && $choice_done)))
                                echo "<button val='".$value['value']."' op-id='".$value['opId']."' ch-id='".$value['chId']."' type-id='".$value['type']."' style='border: none;background:transparent;' onclick='editOperation(this)'><i class='fa fa-edit text-success'></i></button>";
                            echo "</td></tr>";
                        }

                    }
                }
                ?>
            </table>

            <br /><br /><br />
            <?php if((($rw == 1) && ($siteEditable == 1) && ($USER_ROLE == 'design') ) || ( ($rw ==1) && $user['admin'])) { ?>
                <div style="width:80%; margin:auto;">
                    <?php $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl.'/project/remove_record?'.$qp, 'options'=>['style'=>'float:left;','onsubmit' =>"return confirm('آیا از حذف رکورد اطمینان دارید؟');"]]); ?>
                    <div class="form-group">
                        <input type="hidden" name="id" value="<?= $exchange_id; ?>">
                        <?= Html::submitButton(' حذف <i class="fa fa-trash text-danger fa-3x" title="حذف رکورد"></i>', ['class'=>'hvr-bounce-in', 'style' =>'border:none; background:transparent;color:white;']) ?>
                    </div>
                    <br/>
                    <?php ActiveForm::end(); ?>
                    <?php $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl.'/project/done_record?'.$qp, 'options'=>['style'=>'float:right;','onsubmit' =>"return confirm('بعد از ثبت نهایی امکان ویرایش وجود ندارد. آیا از ثبت نهایی اطمینان دارید؟');"]]); ?>
                    <div class="form-group" style="display:none;">
                        <input type="hidden" name="id" value="<?= $exchange_id; ?>">
                        <?= Html::submitButton(' اتمام کار <i class="fa fa-check text-success fa-3x" title="اتمام کار"></i>', ['class'=>'hvr-bounce-in', 'style' =>'border:none; background:transparent;color:white;']) ?>
                    </div>
                    <br/>
                    <?php ActiveForm::end(); ?>
                </div>
            <?php } ?>
            <br style="clear: both;" />
            <br />
        </div>

    </div>

    <!-- operation nav  -->
    <div id="editOperationOverlay" class="overlay bg-gradient">
        <a href="javascript:void(0)" class="closebtn" onclick="$('#editOperationOverlay').css('height','0')">&times;</a>
        <div class="overlay-content">
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/update.png'; ?>" style="width:64px;display: block; margin:auto;">
            <h4 class="text-center">ویرایش اطلاعات</h4>
            <div id="op-type-1">
                <form method="post" style="direction: rtl;" action="<?= Yii::$app->request->baseUrl.'/project/update_operation?'.$qp; ?> ">
                    <input type="hidden" name="operation_id" class="operation_id" value="-1">
                    <input type="hidden" name="exchange_id" value="<?= $exchange_id; ?>">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>">
                    <label  for="input-type-1" id="it1"></label>
                    <select class="form-control" id="choice-select" name="operation" value="" required>
                    </select>
                    <br />
                    <button type="submit" value="update" class="btn btn-success" style="width:80px; float: left;" > تایید </button>
                </form>
            </div>
            <div id="op-type-2">
                <form method="post"  style="direction: rtl;" action="<?= Yii::$app->request->baseUrl.'/project/update_operation?'.$qp; ?> ">
                    <input type="hidden" name="operation_id" class="operation_id"  value="-1">
                    <input type="hidden" name="exchange_id" value="<?= $exchange_id; ?>">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>">
                    <label for="input-type-2" id="it2"></label>
                    <input class="form-control" type="text" name="operation" id="op-value">
                    <br />
                    <button type="submit" value="update" class="btn btn-success" style="width:80px; float: left;" > تایید </button>
                </form>
            </div>
            <br style="clear:both;" />
        </div>
    </div>

    <!-- exch nav  -->
    <div id="editExchangeOverlay" class="overlay  bg-gradient">
        <a href="javascript:void(0)" class="closebtn" onclick="$('#editExchangeOverlay').css('height','0')">&times;</a>
        <div class="overlay-content">
            <img src="<?= Yii::$app->request->baseUrl.'/web/images/update.png'; ?>" style="width:32px;display: block; margin:auto;">
            <h4 class="text-center">ویرایش مرکز / سایت</h4>
            <?php
            $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl."/project/update_exchange?".$qp,
                'id'=>"recForm",
                'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']
            ]); ?>

            <?= $form->field($exchangeModel, 'id')->hiddenInput()->label(false); ?>

            <?= $form->field($exchangeModel, 'area', ['options'=>['style'=>'float:right; width: 30%;']])->dropDownList($areas, ['onchange'=>"areaChanged();",'id'=>'areaCB']); ?>
            <?= $form->field($exchangeModel, 'name', ['options'=>['style'=>'float:left; width: 70%;', 'class'=>"enFont"]])->textInput(['maxlength' => true, 'style'=>"direction:rtl"]) ?>

            <?= $form->field($exchangeModel, 'type', ['options'=>['style'=>'float:right; width: 30%;']])->dropDownList([2=>"مرکز", 3=>"سایت"], ['onchange'=>"typeChanged();",'id'=>'typeCB']); ?>
            <?= $form->field($exchangeModel, 'center_id', ['options'=>['style'=>'float:left; width:70%;', 'class'=>'siteFrame']])->dropDownList($exchanges[$exchangeModel->area], ['id'=>'centerCB']); ?>

            <?= $form->field($exchangeModel, 'site_id', ['options'=>['style'=>'float:right; width: 33%;', 'class'=>'siteFrame enFont']])->textInput(); ?>
            <?= $form->field($exchangeModel, 'kv_code', ['options'=>['style'=>'float:right; width: 33%;', 'class'=>'siteFrame enFont']])->textInput(); ?>
            <?= $form->field($exchangeModel, 'phase', ['options'=>['style'=>'float:right; width: 33%;', 'class'=>'siteFrame']])->dropDownList([1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9', 10=>'10']) ?>

            <?= $form->field($exchangeModel, 'address', ['options'=>['style'=>'clear:both;']])->textarea(['rows'=>2]); ?>
            <?= $form->field($exchangeModel, 'position', ['options'=>['style'=>'clear:both;',  'class'=>"enFont"]])->textInput(['maxlength' => true, 'style'=>"direction:ltr"]); ?>

            <div class="form-group" style="clear:both;">
                <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>

    <!-- add lom  -->
    <div id="addLomOverlay" class="overlay  bg-gradient">
        <a href="javascript:void(0)" class="closebtn" onclick="$('#addLomOverlay').css('height','0')">&times;</a>
        <div class="overlay-content">
            <h4 class="text-center" style="direction: rtl;">افزودن تجهیز جدید به LOM</h4>
            <?php
            $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl."/project/exchange_add_lom?".$qp,
                'id'=>"lomForm",
                'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']
            ]); ?>


            <?= $form->field($lomDetailModel, 'exchange_id')->hiddenInput()->label(false); ?>

            <?= $form->field($lomDetailModel, 'lom_id', ['options'=>['class'=>'enFont']])->dropDownList($projectLom); ?>
            <?= $form->field($lomDetailModel, 'quantity', ['options'=>['style'=>'']])->textInput([ 'type'=>'number', 'required'=>true]) ?>


            <div class="form-group" style="clear:both;">
                <?= Html::submitButton('تایید', ['class' => 'btn btn-success pull-left']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- rem lom  -->
    <div id="remLomOverlay" class="overlay bg-gradient">
        <a href="javascript:void(0)" class="closebtn" onclick="$('#remLomOverlay').css('height','0')">&times;</a>
        <div class="overlay-content">
            <h4 class="text-center" style="direction: rtl;">حذف کردن تجهیز از LOM</h4>
            <?php
            $form = ActiveForm::begin(['action'=>Yii::$app->request->baseUrl."/project/exchange_remove_lom?".$qp,
                'id'=>"lomForm",
                'options'=>['style'=>'direction:rtl;max-width:400px;min-width:100px; margin:auto']
            ]); ?>

            <input id="rem_lom_id" type="hidden" name="lom_id" value="-1" />
            <label for="rem_equipmentId" style="display:block;" >تجهیز</label>
            <input id="rem_equipmentId" type="text" name="equipment" value="" class="form-control enFont" disabled="" />
            <label for="rem_quantityId" style="display: block;" >تعداد</label>
            <input id="rem_quantityId" type="text" name="quantity" value="" class="form-control" disabled="" />

            <br style="clear: both;" />

            <div class="form-group" style="clear:both;">
                <?= Html::submitButton('حذف', ['class' => 'btn btn-danger pull-left']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

<?php
$jsonOperations = json_encode($operations);
$jsonChoices = json_encode($choices);
$aexJason = json_encode($exchanges);
$centerId = $exchangeModel->center_id;
$script =<<< JS
    var jsonOperations = $jsonOperations;
    var jsonChoices = $jsonChoices;
    typeChanged();
    $("#centerCB").val($centerId);
function editOperation(obj)
{
    var opId = $(obj).attr('op-id');
    var chId = $(obj).attr('ch-id');
    var typeId = $(obj).attr('type-id');
    var value = $(obj).attr('val');
    $("#op-type-1").css('display', 'none');
    $("#op-type-2").css('display', 'none');
    $(".operation_id").val(opId);
    
    if(typeId == 1)
        {
            $("#op-type-1").css('display', 'block');
            $("#it1").text(jsonOperations[opId]['operation']);
            $("#choice-select").empty();

            var choices = jsonChoices[opId];
            for (key in choices)
                {
                    var o = new Option(choices[key]['choice'], key);
                    $("#choice-select").append(o);
                }
            
            $("#choice-select").val(chId);
        }
    else
        {
            $("#op-type-2").css('display', 'block');
            $("#it2").text(jsonOperations[opId]['operation']);
            $("#op-value").val(value);
        }

    
    
    $('#editOperationOverlay').css('height','100%');
}


function areaChanged()
{ 
    var center = $("#centerCB");
    $(center).empty();
    $(center).append('<option value=-1></option>');
    var type = $("#typeCB").val();
    if(type == 3)
        {
            var area = $("#areaCB").val();
            if(area > 0)
                {
                    var json = $aexJason;
                    json = json[area];
                    for(var id in json)
                    {
                        $(center).append('<option value='+id+'>'+json[id]+'</option>');
                    }
              }   
        }
}

function typeChanged()
{
    var type = $("#typeCB").val();
    var center = $("#centerCB");
    $(center).empty();
    if(type == 3)
        {
            var siteFrame = $(".siteFrame").css('display', 'block');
            areaChanged();
        }
    else 
        {
            var siteFrame = $(".siteFrame").css('display', 'none');
        }
}

function activateRow(obj)
{
    $(".selectedRow").removeClass("selectedRow");
    $(obj).addClass("selectedRow");
}

function addLom(exchId)
{
    $('#addLomOverlay').css('height','100%');
}

function remLom(obj)
{
    var lom_id = $(obj).attr("lom-id");
    var lom_equip = $(obj).attr("lom-equipment");
    var lom_quantity = $(obj).attr("lom-quantity");

    $("#rem_lom_id").val(lom_id);
    $("#rem_equipmentId").val(lom_equip);
    $("#rem_quantityId").val(lom_quantity);

    $('#remLomOverlay').css('height','100%');
}

JS;
$this->registerJs($script, Yii\web\View::POS_END);
?>
