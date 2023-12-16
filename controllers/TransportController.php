<?php

namespace app\controllers;

use phpDocumentor\Reflection\Types\Scalar;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;

class TransportController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $session->open();
        if (isset($session['user']))
        {
            return parent::beforeAction($action);
        }
        else
        {
            return $this->redirect(["main/login"]);
        }
    }

    public function action__Copy_project()
    {
        $old_project_id = 8;
        $new_project_id = 9;
        $new_phase = 4;

        $oldExchanges = \app\models\PcExchanges::find()->where(['project_id'=>$old_project_id])->all();
        foreach($oldExchanges as $sitex)
        {
            if($sitex->type < 3)
                continue;
            
            $old_center_id = $sitex->center_id;
            $new_center_id = -1;
            if(!empty($old_center_id))
            {
                $old_center = \app\models\PcExchanges::findOne($old_center_id);
                $area = $old_center->area;
                $abbr = $old_center->abbr;

                $new_center_id = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>$new_project_id, 'area'=>$area, 'abbr'=>$abbr])->scalar();
            }

            $newExch = new \app\models\PcExchanges();
            //id, project_id, area, name, abbr, type, center_id, site_id, kv_code, address, "position", done, modifier_id, register_ts, modified_ts, phase, weight
            $newExch->project_id = $new_project_id;
            $newExch->area = $sitex->area;
            $newExch->name = $sitex->name;
            $newExch->abbr = $sitex->abbr;
            $newExch->type = $sitex->type;
            if($new_center_id == -1)
                $newExch->center_id = NULL;
            else 
                $newExch->center_id = $new_center_id;

            $newExch->site_id = $sitex->site_id;
            $newExch->kv_code = $sitex->kv_code;
            $newExch->address = $sitex->address;
            $newExch->position = $sitex->position;
            $newExch->done = $sitex->done;
            $newExch->modifier_id = (int) $sitex->modifier_id;
            $newExch->register_ts = $sitex->register_ts;
            $newExch->modified_ts = $sitex->modified_ts;
            $newExch->phase = (int) $new_phase;
            $newExch->weight = (int) $sitex->weight;

            $newExch->save(false);

            $new_id = $newExch->id;
            $old_id = $sitex->id;

            //records
            $old_records = \app\models\PcRecords::find()->where(['project_id'=>$old_project_id, 'exchange_id'=>$old_id])->all();
            foreach($old_records as $record)
            {
                $new_record = new \app\models\PcRecords();
                $new_record->project_id = $new_project_id;
                $new_record->exchange_id = $new_id;
                $opVal = $this->getOpVal($old_project_id, $new_project_id, $record->op_id, $record->op_value);
                $new_record->op_id = $opVal[0];
                $new_record->op_value = $opVal[1];


                $new_record->save(false);
            }

            //lom_detail
            $old_lom_details = \app\models\PcLomDetail::find()->where(['exchange_id'=> $old_id])->all();
            foreach($old_lom_details as $old_ld)
            {
                $old_lom_id = $old_ld->lom_id;
                $eq = \app\models\PcLom::find()->select("equipment")->where(['id'=>$old_lom_id])->scalar();
                $new_lom_id = \app\models\PcLom::find()->select('id')->where(['project_id'=>$new_project_id, 'equipment'=>$eq])->scalar();

                if($new_lom_id)
                {
                    $new_lom = new \app\models\PcLomDetail();
                    $new_lom->lom_id = $new_lom_id;
                    $new_lom->exchange_id = $new_id;
                    $new_lom->quantity = $old_ld->quantity;
    
                    $new_lom->save(false);
                }


            }
        }
    }

    public function actionAaa()
    {
        $new_lom_id = \app\models\PcLom::find()->select('id')->where(['project_id'=>9, 'equipment'=>'hgj'])->scalar();
        return var_dump($new_lom_id);

        //return var_dump($this->getOpVal(3,9,76,142));
    }
    private function getOpVal($old_project_id , $new_project_id, $old_op_id, $old_op_value)
    {
        //  [ op_id ,  op_value ]
        //types: 1:select   2:text   3:number    4:date
        $operation = \app\models\PcOperations::findOne($old_op_id);
        $op_name = $operation->operation;
        $op_name = trim($op_name);
        $new_op_id = \app\models\PcOperations::find()->select('id')->where(['project_id'=>$new_project_id, 'operation'=>$op_name])->scalar();

        $SELECT_BOOL = false;
        if($operation->type_id == 1)
            $SELECT_BOOL = true;
        
        if($SELECT_BOOL)
        {
            $old_op_value = (int) $old_op_value;
            $choice = \app\models\PcChoices::find()->select('choice')->where(['id'=>$old_op_value])->scalar();
            $choice = trim($choice);
            $new_op_value = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$new_op_id, 'choice'=>$choice])->scalar();

            return [$new_op_id, $new_op_value];
        }
        else 
            return [$new_op_id, $old_op_value];
    }



    public function actionSet_sites_default_choice()
    {
        $project_id = 9;

        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id, 'type_id'=>1])->all();
        $exids = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>$project_id, 'type'=>3])->all();

        foreach($exids as $ex)
        {
            $eid = $ex->id;
            foreach($operations as $operation)
            {
                $op_id = $operation->id;
                $rec = \app\models\PcRecords::find()->where(['project_id'=>$project_id, 'exchange_id'=>$eid,'op_id'=>$op_id])->one();
                if(empty($rec))
                {
                    $new_rec = new \app\models\PcRecords();
                    $new_rec->project_id = $project_id;
                    $new_rec->exchange_id = $eid;
                    $new_rec->op_id = $op_id;

                    $op_val = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$op_id, 'default'=>true])->scalar();
                    $new_rec->op_value = (string) $op_val;
                    $new_rec->save(false);
                }
                else 
                {
                    $record = \app\models\PcRecords::findOne($rec->id);

                    $op_value = (int) $record->op_value;
                    if($op_value > 0)
                    {
                        continue;
                    } 
                    else
                    {
                        $op_val = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$op_id, 'default'=>true])->scalar();
                        $record->op_value = $op_val;
                        $record->update(false);
                    }


                }
            }
        }

    }





    public function actionCopy_site()
    {
        $site_id = 3072;
        $from_project_id = 3;
        $to_project_id = 8;

        $site = \app\models\PcExchanges::findOne($site_id);

        $old_center_id = $site->center_id;
        $new_center_id = -1;
        if(!empty($old_center_id))
        {
            $old_center = \app\models\PcExchanges::findOne($old_center_id);
            $area = $old_center->area;
            $abbr = $old_center->abbr;

            $new_center_id = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>$to_project_id, 'area'=>$area, 'abbr'=>$abbr])->scalar();
        }


        $newExch = new \app\models\PcExchanges();
        //id, project_id, area, name, abbr, type, center_id, site_id, kv_code, address, "position", done, modifier_id, register_ts, modified_ts, phase, weight
        $newExch->project_id = $to_project_id;
        $newExch->area = $site->area;
        $newExch->name = $site->name;
        $newExch->abbr = $site->abbr;
        $newExch->type = $site->type;
        if($new_center_id == -1)
            $newExch->center_id = NULL;
        else 
            $newExch->center_id = $new_center_id;

        $newExch->site_id = $site->site_id;
        $newExch->kv_code = $site->kv_code;
        $newExch->address = $site->address;
        $newExch->position = $site->position;
        $newExch->done = $site->done;
        $newExch->modifier_id = (int) $site->modifier_id;
        $newExch->register_ts = $site->register_ts;
        $newExch->modified_ts = $site->modified_ts;
        $newExch->phase = $site->phase;
        $newExch->weight = (int) $site->weight;

        $newExch->save(false);


        $new_id = $newExch->id;
        $old_id = $site->id;

        //records
        $old_records = \app\models\PcRecords::find()->where(['project_id'=>$from_project_id, 'exchange_id'=>$old_id])->all();
        foreach($old_records as $record)
        {
            $new_record = new \app\models\PcRecords();
            $new_record->project_id = $to_project_id;
            $new_record->exchange_id = $new_id;
            $opVal = $this->getOpVal($from_project_id, $to_project_id, $record->op_id, $record->op_value);
            $new_record->op_id = $opVal[0];
            $new_record->op_value = $opVal[1];


            $new_record->save(false);
        }

        //lom_detail
        $old_lom_details = \app\models\PcLomDetail::find()->where(['exchange_id'=> $old_id])->all();
        foreach($old_lom_details as $old_ld)
        {
            $old_lom_id = $old_ld->lom_id;
            $eq = \app\models\PcLom::find()->select("equipment")->where(['id'=>$old_lom_id])->scalar();
            $new_lom_id = \app\models\PcLom::find()->select('id')->where(['project_id'=>$to_project_id, 'equipment'=>$eq])->scalar();

            if($new_lom_id)
            {
                $new_lom = new \app\models\PcLomDetail();
                $new_lom->lom_id = $new_lom_id;
                $new_lom->exchange_id = $new_id;
                $new_lom->quantity = $old_ld->quantity;

                $new_lom->save(false);
            }

        }
    }   
}
