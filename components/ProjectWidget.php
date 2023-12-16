<?php
namespace app\components;
use yii\base\Widget;
use yii\helpers\Html;

class ProjectWidget extends Widget
{
    public $model, $url,$mng=0;
    public function init()
    {
        parent::init();
    }
    public function run()
    {
        return $this->render("ProjectView", ["model"=>$this->model, 'url'=>$this->url, 'mng'=>$this->mng]);
    }
}
?>
