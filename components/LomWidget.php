<?php
namespace app\components;
use yii\base\Widget;
use yii\helpers\Html;

class LomWidget extends Widget
{
    public $model, $area=-1, $edit=false;
    public function init()
    {
        parent::init();
    }
    public function run()
    {
        return $this->render("LomView", ["model"=>$this->model, 'area'=>$this->area, 'edit'=>$this->edit]);
    }
}
?>
