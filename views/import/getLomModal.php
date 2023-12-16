<!-- Modal -->
<!--data-backdrop="static" data-keyboard="false"-->
<div class="modal fade"  id="getLomModal" role="dialog" style="text-align: right;">
    <div class="modal-dialog modal-lg" style="max-width:450px;">
        <div class="modal-content" style="border:1px solid greenyellow;">

            <div class="modal-header" style="background-color: rgba(200,200,200,0.4);border:none; border-radius:10px;">
                <h4 class="modal-title">دریافت لیست تجهیزات پروژه</h4>
            </div>
            <form method="post" action=<?= Yii::$app->request->baseUrl . "/import/export_lom"; ?> >
                <div class="modal-body" style="background-color: rgba(200,200,200,0.4);border:none; border-radius:10px;">
                    <!-- body -->
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />

                    <label for="projectCB" style="text-align: right;" >انتخاب پروژه</label>
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

                </div>
                <div class="modal-footer" style="background-color: rgba(200,200,200,0.4); border-radius:10px; border:none;">
                    <button style="min-width: 100px;float:left;" class="btn btn-success" onclick="$('#getLomModal').modal('hide');" >تایید</button>
                    <button style="float:right;" class="btn btn-danger" data-dismiss="modal" >بستن</button>
                    <br style="clear: both;" />
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal -->