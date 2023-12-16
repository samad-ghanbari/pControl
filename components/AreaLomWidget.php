<?php
namespace app\components;
use yii\base\Widget;
use yii\helpers\Html;

class AreaLomWidget extends Widget
{
    public $model;
    public function init()
    {
        parent::init();
    }
    public function run()
    {
        return $this->render("LomView", ["model"=>$this->model]);
    }
}
?>
