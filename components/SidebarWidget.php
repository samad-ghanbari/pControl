<?php
namespace app\components;
use yii\base\Widget;
use yii\helpers\Html;

class SidebarWidget extends Widget
{
    public $index, $projectId;
    public function init()
    {
        parent::init();
    }
    public function run()
    {
        return $this->render("SidebarView", ["index"=>$this->index, "projectId"=>$this->projectId]);
    }
}
?>
