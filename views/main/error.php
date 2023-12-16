<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="topic-cover bg-gradient" style="direction: ltr;">

    <h1 style="color:white;"><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger" >
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p style="direction: rtl;color:white;">
        صفحه درخواستی شما با خطا مواجه شد
    </p>
    <p style="direction: rtl;color:white;">
        اگر چنانچه فکر می کنید این خطا از سمت سرور می باشد موضوع را به ما اطلاع دهید.
    </p>

</div>
