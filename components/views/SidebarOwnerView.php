<?php
/* @var $index */
/* @var $projectId */
?>

<img src="<?= Yii::$app->request->baseUrl.'/web/images/setting.png'; ?>">
<h4 class="disappear-text" style="text-align: center; color:white;">مدیریت پروژه</h4>
<br />

<ul>

    <li class="<?php if($index==1) echo 'active'; ?>" title=" پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/owner/edit_project?id='.$projectId; ?>" ><span class="disappear-text"> پروژه </span><i class='fas fa-edit' style="color:white;"></i></a>
    </li>


    <li class="<?php if($index==4) echo 'active'; ?>"  title=" کاربران پروژه ">
        <a style="display:block;" href="<?= Yii::$app->request->baseurl.'/owner/project_users?id='.$projectId; ?>" ><span class="disappear-text"> کاربران پروژه </span><i class='fas fa-users' style="color:white;"></i></a>
    </li>

</ul>