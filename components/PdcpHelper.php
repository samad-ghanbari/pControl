<?php

namespace app\components;
use Yii;
use yii\base\Component;

class PdcpHelper extends Component
{
    public static function getWeight($project_id , $exchId )
    {
        $weight = 0;
        $records = \app\models\PcViewRecords::find()->select('op_weight, op_value, op_type')->where(['project_id'=>$project_id, 'exchange_id'=>$exchId])->andWhere(['>', 'op_weight', 0])->asArray()->all();
        if(empty($records)) return $weight;
        $choices = \app\models\PcViewChoices::find()->select('id, choice_weight')->where(['project_id'=>$project_id])->asArray()->all();

        $array =[];
        foreach ($choices as $ch)
        {
            $array[$ch['id']] = $ch['choice_weight'];
        }
        $choices = $array;
        $array=[];

        foreach ($records as $rec)
        {
            $val = $rec['op_value'];
            if($rec['op_type'] == 1)
            {
                $val = (integer)$val;
                $weight += $choices[$val];
            }
            else if($rec['op_type'] == 3) //numeric
            {
                if((integer)$val  > 0)
                    $weight += $rec['op_weight'];
            }
            else
            {
                if(!empty($val))
                    $weight += $rec['op_weight'];
            }
        }

        return $weight;
    }

    public static function setUserProjectSession()
    {
        $session = Yii::$app->session;
        $session->open();
        $id = -1;
        if(isset($session['user']))
            $id = $session['user']['id'];

        $userProjects = \app\models\PcUserProjects::find()->where(['user_id'=>$id])->asArray()->all();
        $array = [];
        foreach ($userProjects as $up)
        {
            $array[$up['project_id']] = ['area'=>$up['area'] , 'exchange_id'=>$up['exchange_id'], 'rw'=>$up['rw'], 'site_editable'=>$up['site_editable']];
        }
        if (isset($session['userProjects']))
            $session->remove("userProjects");

        $session['userProjects'] = $array;
    }

    public static function setUserProjectOwnerSession()
    {
        $session = Yii::$app->session;
        $session->open();
        $id = -1;
        if(isset($session['user']))
            $id = $session['user']['id'];
        
        $owner = \app\models\PcProjectOwner::find()->where(['user_id'=>$id])->asArray()->all();
        $array = [];
        foreach ($owner as $po)
            array_push($array, $po['project_id']);
        
        $session['owner'] = $array;
    }

    public static function setUserSession()
    {
        $session = Yii::$app->session;
        $session->open();
        $id = -1;
        if(isset($session['user']))
            $id = $session['user']['id'];

        $user = \app\models\PcUsers::find()->where(['id'=>$id])->asArray()->one();
        if (isset($session['user']))
            $session->remove("user");

        $session['user'] = $user;
    }







}
