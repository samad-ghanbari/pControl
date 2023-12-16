<?php
/* @var $this yii\web\View */
/* @var $funcNum */
$this->title = 'PDCP|Brows';

use yii\helpers\Html;
?>
<div class="container">

    <h3 style="text-align: center; ">تصاویر بارگذاری شده در سرور</h3>

    <?php if (!empty($files)): ?>
        <div class="row">
            <?php foreach ($files as $file):?>
                <div class="col-md-4 mb-2">
                    <img
                            src="<?= $file ?>"
                            class="img-thumbnail"
                            style="cursor: pointer; margin-bottom: 2rem; width:200px; height:auto;"
                            onClick="selectImage(<?= $funcNum ?>, '<?= $file ?>')"
                    />
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script type="text/javascript">
    function selectImage(funcNum, url){
        window.opener.CKEDITOR.tools.callFunction(funcNum, url)
        window.close()
    }
</script>