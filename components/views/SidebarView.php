<?php
/* @var $index */
/* @var $projectId */
?>

<img src="<?= Yii::$app->request->baseUrl.'/web/images/setting.png'; ?>">
<h4 class="disappear-text" style="text-align: center; color:white;">مدیریت پروژه</h4>
<br />

<ul>

    <li class="<?php if($index==1) echo 'active'; ?>" title=" پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/projects/edit_project?id='.$projectId; ?>" ><span class="disappear-text"> پروژه </span><i class='fas fa-edit' style="color:white;"></i></a>
    </li>

    <li class="<?php if($index==2) echo 'active'; ?>" title=" پارامترهای پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/projects/setting?id='.$projectId; ?>" ><span class="disappear-text"> پارامترهای پروژه </span><i class='fa fa-tasks' style="color:white;"></i></a>
    </li>

    <li class="<?php if($index==3) echo 'active'; ?>"  title=" کاربران ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/projects/users'; ?>" ><span class="disappear-text"> کاربران </span><i class='fas fa-users' style="color:white;"></i></a>
    </li>

    <li class="<?php if($index==4) echo 'active'; ?>"  title=" کاربران پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/projects/project_users?id='.$projectId; ?>" ><span class="disappear-text"> کاربران پروژه </span><i class='fas fa-users' style="color:white;"></i></a>
    </li>

    <li class="<?php if($index==6) echo 'active'; ?>"  title=" مسئول پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/projects/project_owner?id='.$projectId; ?>" ><span class="disappear-text"> مسئول پروژه </span><i class='fas fa-users' style="color:white;"></i></a>
    </li>

    <li class="<?php if($index==5) echo 'active'; ?>" title=" حذف پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/projects/remove_project?id='.$projectId; ?>" ><span class="disappear-text"> حذف پروژه </span><i class='fa fa-times' style="color:white;"></i></a>
    </li>

</ul>

<hr />
<p>
    <a title="پروژه جدید" style="width:90%; margin:auto; display: block;" href="<?= Yii::$app->request->baseUrl.'/projects/new_project'; ?>" class="btn btn-success"> <span class="disappear-text">پروژه جدید</span> <i class="fa fa-plus"></i></a>
</p>
