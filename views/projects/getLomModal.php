<!-- Modal -->
<div class="modal fade"  id="getLomModal" data-backdrop="static" data-keyboard="false" role="dialog" style="text-align: right;">
    <div class="modal-dialog modal-lg" style="max-width:450px;">
        <div class="modal-content" style="border:1px solid dodgerblue;">

            <div class="modal-header" style="background-color: rgba(0,0,0,0.5);color:#fff; border:none; border-radius:5px 5px 0 0;">
                <h4 style="direction:rtl;" class="modal-title">دریافت فایل LOM</h4>
            </div>
            <form method="post" enctype="multipart/form-data" action=<?= Yii::$app->request->baseUrl . "/projects/parse_lom"; ?> >
                <div class="modal-body" style="background-color: rgba(0,0,0,0.6);color:#fff; border:none; border-radius:0;">
                    <!-- body -->
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
                    <input type="hidden" name="project-id" id="project-id" value="-1" />
                    <br />
                    <label for="file-in" style="text-align: right;color:white;">انتخاب فایل اکسل ورودی</label>
                    <input id="file-in" name="file-upload" type="file" accept="application/vnd.ms-excel"  class="form-control" style="direction: rtl;"  required/>
                    <br />

                </div>
                <div class="modal-footer" style="background-color: rgba(0,0,0,0.5); border:none;border-top:1px solid dodgerblue; border-radius:0px 0px 5px 5px;">
                    <button style="min-width: 100px;float:left;" class="btn btn-success" >تایید</button>
                    <button style="float:right;" class="btn btn-danger" data-dismiss="modal" >بستن</button>
                    <br style="clear: both;" />
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal -->