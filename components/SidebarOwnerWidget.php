<?php
namespace app\components;
use yii\base\Widget;
use yii\helpers\Html;

class SidebarOwnerWidget extends Widget
{
    public $index, $projectId;
    public function init()
    {
        parent::init();
    }
    public function run()
    {
        return $this->render("SidebarOwnerView", ["index"=>$this->index, "projectId"=>$this->projectId]);
    }
}
?>
