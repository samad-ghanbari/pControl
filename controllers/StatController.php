<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Helper\Html as HtmlHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;


class StatController extends \yii\web\Controller
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

    private function getGraphColorArray($arraySize = 0)
    {
        $array = [];
        for($i = 0; $i < $arraySize; $i++)
        {
            $r = round(rand(100, 255));
            $g = round(rand(100,255));
            $b = round(rand(100,255));
            $color = 'rgb('.$r.','.$g.','.$b.')';
            array_push($array, $color);
        }

        return $array;
    }

    public function actionPiechart($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);

        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        $project_id = $id;
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$project_id, 'enabled'=>true])->asArray()->one();


            $ae = $userProjects[$project_id];
            $area = $ae['area'];
            $exchange_id = $ae['exchange_id'];
            $areaSelectoin = [];

            $accessLevel=['level'=>-1, 'name'=>''];
            if(empty($ae['area']) && empty($ae['exchange_id']) )
            {
                $accessLevel['level'] = 1;
            }
            else if(($ae['area'] > 0) && empty($ae['exchange_id']))
            {
                $accessLevel['level'] = 2;
            }
            else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
            {
                $accessLevel['level'] = 3;
            }

            $exchanges = [];
            if($accessLevel['level'] == 1)
            {
                $areaSelection = [-1 => 'کل مناطق', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'];
                $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project_id, 'type' => 2])->orderBy('area, name')->asArray()->all();
                $array = [-1=>[-1=>'کل مراکز'], 2=>[-1=>"کل مراکز"], 3=>[-1=>"کل مراکز"], 4=>[-1=>"کل مراکز"], 5=>[-1=>"کل مراکز"], 6=>[-1=>"کل مراکز"], 7=>[-1=>"کل مراکز"], 8=>[-1=>"کل مراکز"]];
                foreach ($exchanges as $exch)
                {
                    $array[$exch['area']][$exch['id']] = $exch['name'];
                }
                $exchanges = $array;
            }
            else if($accessLevel['level'] == 2)
            {
                $areaSelection = [$ae['area']=>$ae['area']];
                $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project_id, 'type' => 2, 'area'=>$ae['area']])->orderBy('area, name')->asArray()->all();

                $array = [-1=>[-1=>'کل مراکز'], $ae['area']=>[-1=>'کل مراکز']];
                foreach ($exchanges as $exch)
                {
                    $array[$exch['area']][$exch['id']] = $exch['name'];
                }
                $exchanges = $array;

            }
            else if($accessLevel['level'] == 3)
            {
                $areaSelection = [$ae['area']=>$ae['area']];
                $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project_id, 'type' => 2, 'id'=>$ae['exchange_id']])->orderBy('area, name')->asArray()->all();

                $array = [-1=>[-1=>'کل مراکز']];
                foreach ($exchanges as $exch)
                {
                    $array[$exch['area']][$exch['id']] = $exch['name'];
                }
                $exchanges = $array;
            }

            if(empty($area)) $area = -1;
            if(empty($exchange_id)) $exchange_id = -1;

            $searchParams = ['area'=>$area, 'exchange_id'=>$exchange_id, 'phaseNo'=>-1];
            $params = Yii::$app->request->post();
            if(isset($params['search']))
            {
                $searchParams['area'] = $params['search']['area'];
                if(isset($params['search']['exchange_id']))
                    $searchParams['exchange_id'] = $params['search']['exchange_id'];
                $searchParams['phaseNo'] = $params['search']['phaseNo'];
            }
            if(empty($searchParams['area'])) $searchParams['area'] = -1;
            if(empty($searchParams['exchange_id'])) $searchParams['exchange_id'] = -1;
            //###################################
            $phaseNo = (integer)$searchParams['phaseNo'];
            $cond = [];
            if($phaseNo > -1)
                $cond['phase'] = $phaseNo;
            if($searchParams['area'] > -1)
                $cond['area'] = (integer)$searchParams['area'];

            $exCond = [];
            if($searchParams['exchange_id'] > -1)
                $exCond = ['or', ['exchange_id'=>(integer)$searchParams['exchange_id']], ['center_id'=>(integer)$searchParams['exchange_id']]];

            $totalRecord = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id])->andWhere($cond)->andWhere($exCond)->scalar();
            if($totalRecord == 0)
            {
                return $this->render('piechart', ['searchParams'=>$searchParams,'projects'=>$projects, 'project'=>$project, 'exchanges'=>$exchanges,'areas'=>[], 'op1Stat'=>[], 'areaSelection'=>$areaSelection]);
            }

            $areas = \app\models\PcViewRecords::find()->select("area, COUNT(DISTINCT exchange_id) as cnt")->where(['project_id'=>$project_id])->andWhere($cond)->andWhere($exCond)->groupBy("area")->asArray()->all();

            //        [[area=>2,cnt=>202], [area=>4,cnt=>2], ... ]
            $data = [];
            $label = [];
            $color = $this->getGraphColorArray(sizeof($areas));
            foreach ($areas as $a)
            {
                array_push($data, round($a['cnt']/$totalRecord*100, 1));
                array_push($label, "Area ".$a['area'].' ['.$a['cnt'].'] ');
            }
            $areas = [$data, $color, $label];

            $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id, 'type_id'=>3])->orderBy('priority')->asArray()->all();
            $condex = \app\models\PcViewRecords::find()->select('exchange_id')->where(['project_id'=>$project_id])->andwhere($cond)->andWhere($exCond);

            //        operations type 1
            $op1Stat = [];// [ kv=>[data, color, label, convasid] , [op2], [op3], ]
            $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id, 'type_id'=>1])->orderBy('priority')->asArray()->all();
            $choices = \app\models\PcViewChoices::find()->where(['project_id'=>$project_id])->asArray()->all();
            $array = [];
            foreach($choices as $ch)
            {
                $array[$ch['id']] = $ch['choice'];
            }
            $choices = $array;

            foreach ($operations as $op)
            {
                $data = [];
                $color = [];
                $label = [];
                $opId = $op['id'];
                //                $opChoices = $choices[$opId]; // [2=>'onu', 3=>'copp' ...]
                $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId])->andWhere(['in', 'exchange_id',$condex])->groupBy("op_value")->asArray()->all();
                $color = $this->getGraphColorArray(sizeof($opRec));
                foreach($opRec as $oR)
                {
                    //array_push($op3Stat,[$op['operation'], $choices[$oR['op_value']].' : '.$oR['cnt']]);
                    if(!empty($choices[$oR['op_value']]))
                    {
                        array_push($data, round($oR['cnt']/$totalRecord*100, 1));
                        array_push($label, $choices[$oR['op_value']].' ['.$oR['cnt'].'] ');
                    }

                }
                if(sizeof($data) >0)
                    $op1Stat[$op['operation']] = [$data, $color,$label, "convas".$op['id']];
                else
                    $op1Stat[$op['operation']] = [[0], $this->getGraphColorArray(1),[''], "convas".$op['id']];
            }

            return $this->render('piechart', ['searchParams'=>$searchParams, 'project'=>$project, 'projects'=>$projects, 'exchanges'=>$exchanges, 'areas'=>$areas, 'op1Stat'=>$op1Stat, 'areaSelection'=>$areaSelection]);

        }
        return $this->render('piechart', ['searchParams'=>[],'project'=>[], 'projects'=>$projects, 'exchanges'=>[], 'areas'=>[], 'op1Stat'=>[], 'areaSelection'=>[]]);
    }

    public function actionTablestat($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        $project_id = $id;
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();

            $session = Yii::$app->session;
            $session->open();
            $areaSelection = [-1=>'کل مناطق', 2=>'2', 3=>"3", 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
            $userProjects = $session['userProjects'];
            if(isset($userProjects[$project['id']]))
                {
                    $ae = $userProjects[$project['id']];
                    $area = $ae['area'];
                    $exchange_id = $ae['exchange_id'];

                    $accessLevel=['level'=>-1, 'name'=>''];
                    if(empty($ae['area']) && empty($ae['exchange_id']) )
                    {
                        $accessLevel['level'] = 1;
                    }
                    else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                    {
                        $accessLevel['level'] = 2;
                    }
                    else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                    {
                        $accessLevel['level'] = 3;
                    }

                    $exchanges = [];
                    if($accessLevel['level'] == 1)
                    {
                        $areaSelection = [-1 => 'کل مناطق', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'];
                        $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2])->orderBy('area, name')->asArray()->all();
                        $array = [-1=>[-1=>'کل مراکز'], 2=>[-1=>"کل مراکز"], 3=>[-1=>"کل مراکز"], 4=>[-1=>"کل مراکز"], 5=>[-1=>"کل مراکز"], 6=>[-1=>"کل مراکز"], 7=>[-1=>"کل مراکز"], 8=>[-1=>"کل مراکز"]];
                        foreach ($exchanges as $exch)
                        {
                            $array[$exch['area']][$exch['id']] = $exch['name'];
                        }
                        $exchanges = $array;
                    }
                    else if($accessLevel['level'] == 2)
                    {
                        $areaSelection = [$ae['area']=>$ae['area']];
                        $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2, 'area'=>$ae['area']])->orderBy('area, name')->asArray()->all();

                        $array = [-1=>[-1=>'کل مراکز'], $ae['area']=>[-1=>'کل مراکز']];
                        foreach ($exchanges as $exch)
                        {
                            $array[$exch['area']][$exch['id']] = $exch['name'];
                        }
                        $exchanges = $array;

                    }
                    else if($accessLevel['level'] == 3)
                    {
                        $areaSelection = [$ae['area']=>$ae['area']];
                        $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2, 'id'=>$ae['exchange_id']])->orderBy('area, name')->asArray()->all();

                        $array = [-1=>[-1=>'کل مراکز']];
                        foreach ($exchanges as $exch)
                        {
                            $array[$exch['area']][$exch['id']] = $exch['name'];
                        }
                        $exchanges = $array;
                    }

                    $searchParams = ['area'=>$area, 'exchange_id'=>$exchange_id, 'phaseNo'=>-1];
                    $params = Yii::$app->request->post();
                    if(isset($params['search']))
                    {
                        $searchParams['area'] = $params['search']['area'];
                        if(isset($params['search']['exchange_id']))
                            $searchParams['exchange_id'] = $params['search']['exchange_id'];
                        $searchParams['phaseNo'] = $params['search']['phaseNo'];
                    }
                    if(empty($searchParams['area'])) $searchParams['area'] = -1;
                    if(empty($searchParams['exchange_id'])) $searchParams['exchange_id'] = -1;

                    //###################################
                    $phaseNo = $searchParams['phaseNo'];
                    $cond = [];
                    if($phaseNo > -1)
                        $cond['phase'] = (integer)$phaseNo;
                    if($searchParams['area'] > -1)
                        $cond['area'] = (integer)$searchParams['area'];
                    $exCond = [];
                    if($searchParams['exchange_id'] > -1)
                        $exCond = ['or', ['exchange_id'=>(integer)$searchParams['exchange_id']], ['center_id'=>(integer)$searchParams['exchange_id']]];

                    $totalRecord = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project['id']])->andWhere($cond)->andWhere($exCond)->scalar();
                    if($totalRecord == 0)
                    {
                        return $this->render('tablestat', ['searchParams'=>$searchParams, 'exchanges'=>$exchanges,'areas'=>[], 'tableInfo'=>[], 'projectName'=>$project['project'], 'areaSelection'=>$areaSelection]);
                    }

                    $recId = 1;
                    $tableInfo = []; //title count percent
                    array_push($tableInfo, ['تعداد کل رکوردها' , $totalRecord, '', $recId]);
                    $recId++;

                //                $areas = \app\models\PcViewRecords::find()->select("area, COUNT(DISTINCT exchange_id) as cnt")->where(['project_id'=>$project['id']])->andWhere($cond)->andWhere($exCond)->groupBy("area")->asArray()->all();
                //                $len = sizeof($areas);
                //                if($len > 1)
                //                {
                //                    foreach ($areas as $a)
                //                    {
                //                        array_push($tableInfo, ['منطقه '.$a['area'], $a['cnt'], ' % '.round($a['cnt']/$totalRecord*100, 1), $recId]);
                //                        $recId++;
                //                    }
                //                }


                //operations type 3 numeric
                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>3])->orderBy('priority')->asArray()->all();
                $condex = \app\models\PcViewRecords::find()->select('exchange_id')->where(['project_id'=>$project['id']])->andwhere($cond)->andWhere($exCond);
                
                foreach ($operations as $op)
                {
                    $opId = $op['id'];
                    $val = \app\models\PcRecords::find()->select("SUM(op_value::decimal)")->where(['op_id'=>$opId, 'project_id'=>$project['id']])->andWhere(['in', 'exchange_id',$condex])->scalar();
                    array_push($tableInfo,[$op['operation'], $val, '', $recId]);
                    $recId++;
                }

                //        operations type 1
                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>1])->orderBy('priority')->asArray()->all();
                $choices = \app\models\PcViewChoices::find()->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($choices as $ch)
                {
                    $array[$ch['id']] = $ch['choice'];
                }
                $choices = $array;
                
                
                foreach ($operations as $op)
                {
                    $opId = $op['id'];
                    $opName = $op['operation'];

                    $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId])->andWhere(['in', 'exchange_id',$condex])->groupBy("op_value")->asArray()->all();
                    foreach($opRec as $oR)
                    {
                        if(!empty($choices[$oR['op_value']]))
                        {
                            //array_push($op3Stat,[$op['operation'], $choices[$oR['op_value']].' : '.$oR['cnt']]);
                            if(str_contains($choices[$oR['op_value']], ' شده'))
                                array_push($tableInfo,[$op['operation'], $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);

                            if(str_contains($choices[$oR['op_value']], 'بزرگ') || str_contains($choices[$oR['op_value']], 'کوچک') )
                                array_push($tableInfo,[$op['operation'].' ['.$choices[$oR['op_value']].'] ', $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);


                            if(str_contains($choices[$oR['op_value']], 'BitStream') || str_contains($choices[$oR['op_value']], 'بیت استریم') )
                                array_push($tableInfo,[$op['operation'].' [ بیت استریم ] ', $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);

                            if(str_contains($choices[$oR['op_value']], 'شرکت'))
                                array_push($tableInfo,[$op['operation'].' ['.$choices[$oR['op_value']].'] ', $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);

                            if(str_contains($choices[$oR['op_value']], 'فشرده'))
                                array_push($tableInfo,[$op['operation'].' ['.$choices[$oR['op_value']].'] ', $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);


                            if(str_contains($opName, 'نصب'))
                            {
                                if(str_contains($choices[$oR['op_value']], 'نشده'))
                                    array_push($tableInfo,[$op['operation'].' [نصب نشده] ', $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);
                            }
                        }
                        $recId++;
                    }
                }
                
                
                
                return $this->render('tablestat', ['searchParams'=>$searchParams, 'exchanges'=>$exchanges, 'tableInfo'=>$tableInfo, 'project'=>$project, 'projects'=>$projects, 'areaSelection'=>$areaSelection]);
            }
            
        }
        
        return $this->render('tablestat', ['searchParams'=>[], 'exchanges'=>[], 'tableInfo'=>[], 'project'=>[], 'projects'=>$projects, 'areaSelection'=>[]]);
    }
    
    public function actionTotaltablestat($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        $project_id = intval($id);
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
            
            $session = Yii::$app->session;
            $session->open();
            $areaSelection = [-1=>'کل مناطق', 2=>'2', 3=>"3", 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
            $userProjects = $session['userProjects'];
            if(isset($userProjects[$project['id']]))
            {
                $ae = $userProjects[$project['id']];
                $area = $ae['area'];
                $exchange_id = $ae['exchange_id'];
                
                $accessLevel=['level'=>-1, 'name'=>''];
                if(empty($ae['area']) && empty($ae['exchange_id']) )
                {
                    $accessLevel['level'] = 1;
                }
                else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                {
                    $accessLevel['level'] = 2;
                }
                else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                {
                    $accessLevel['level'] = 3;
                }
                
                $exchanges = [];
                if($accessLevel['level'] == 1)
                {
                    $areaSelection = [-1 => 'کل مناطق', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2])->orderBy('area, name')->asArray()->all();
                    $array = [-1=>[-1=>'کل مراکز'], 2=>[-1=>"کل مراکز"], 3=>[-1=>"کل مراکز"], 4=>[-1=>"کل مراکز"], 5=>[-1=>"کل مراکز"], 6=>[-1=>"کل مراکز"], 7=>[-1=>"کل مراکز"], 8=>[-1=>"کل مراکز"]];
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;
                }
                else if($accessLevel['level'] == 2)
                {
                    $areaSelection = [$ae['area']=>$ae['area']];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2, 'area'=>$ae['area']])->orderBy('area, name')->asArray()->all();

                    $array = [-1=>[-1=>'کل مراکز'], $ae['area']=>[-1=>'کل مراکز']];
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;

                }
                else if($accessLevel['level'] == 3)
                {
                    $areaSelection = [$ae['area']=>$ae['area']];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2, 'id'=>$ae['exchange_id']])->orderBy('area, name')->asArray()->all();

                    $array = [-1=>[-1=>'کل مراکز']];
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;
                }
                
                $searchParams = ['area'=>$area, 'exchange_id'=>$exchange_id, 'phase'=>-1];
                $params = Yii::$app->request->post();
                if(isset($params['search']))
                {
                    $searchParams['area'] = intval($params['search']['area']);
                    
                    if(isset($params['search']['exchange_id']))
                        $searchParams['exchange_id'] = intval($params['search']['exchange_id']);

                    if(isset($params['search']['phase']))
                        $searchParams['phase'] = intval($params['search']['phase']);
                }
                if(empty($searchParams['area'])) $searchParams['area'] = -1;
                if(empty($searchParams['exchange_id'])) $searchParams['exchange_id'] = -1;
                
                //###################################
                $recId = 1;
                $tableInfo = [];
                // [ 0=>[info, details] ] info:kol  details:areas       area=-1
                // [ 2=>[info, details] ] info:area details:exchanges   area>1 exchange=-1
                // [ 2=>[info, details] ] info:exchange details:sites   area>1 exchange>-1
                
                // info&details : [title=>'', progress=>'' , count=>'', attributes=>[] ]
                // 0: info and areas
                // 2~8: info and exchanges
                
                //info array [title, progress, count, attributes] -> [area2, 40%, 22, [ [op,cnt,%] , [] ...]]
                
                if($accessLevel['level'] ==  1)
                {
                    $tableInfo = $this->getTableInfo($project_id, $searchParams['area'], $searchParams['exchange_id'], $searchParams['phase'] );
                }
                else if($accessLevel['level'] == 2)
                {
                    $tableInfo = $this->getTableInfo($project_id, $area, $searchParams['exchange_id'], $searchParams['phase']);
                }
                else if($accessLevel['level'] == 3)
                {
                    $tableInfo = $this->getTableInfo($project_id, $area, $exchange_id, $searchParams['phase']);
                }
                
                $column = 4;
                $temp = [];  // [id=>op,  ]
                $opMap = []; // [ column=>[opId,title]]*
                $ops = \app\models\PcOperations::find()->where(['project_id'=>$project_id])->andWhere(['type_id'=>[1,3]])->orderBy('priority')->asArray()->all();
                foreach($ops as $o)
                {
                    $temp[$o['id']] = $o['operation'];
                }
                //                if(isset( $tableInfo['info']['attributes'] ))
                //                {
                //                    $attrs = $tableInfo['info']['attributes']; //[id=>[op,cnt,%]]
                //                    foreach($attrs as $id=>$attr)
                //                        $temp[$id] = $attr[0];
                //                }
                //                if( isset($tableInfo['details']) )
                //                {
                //                    $dets = $tableInfo['details'];
                //                    foreach($dets as $det)
                //                    {
                //                        if(isset($det['attributes']))
                //                        {
                //                            $attrs = $det['attributes'];
                //                            foreach($attrs as $id=>$attr)
                //                                $temp[$id] = $attr[0];
                //                        }
                //                    }
                //                }

                foreach($temp as $id=>$op)
                {
                    $opMap[$column] = ['id'=>$id, 'title'=>$op];
                    $column++;
                }
                
                //return var_dump($tableInfo);
                return $this->render('totaltablestat', ['searchParams'=>$searchParams, 'exchanges'=>$exchanges, 'tableInfo'=>$tableInfo, 'project'=>$project, 'projects'=>$projects, 'areaSelection'=>$areaSelection, 'opMap'=>$opMap]);
            }
            
        }
        
        return $this->render('totaltablestat', ['searchParams'=>[], 'exchanges'=>[], 'tableInfo'=>[], 'project'=>[], 'projects'=>$projects, 'areaSelection'=>[], 'opMap'=>[]]);
    }

    private function getTableInfo($project_id, $area, $exchange_id, $phase)
    {
        // [ 0=>[info, details] ] info:kol  details:areas       area=-1
        // [ 2=>[info, details] ] info:area details:exchanges   area>1 exchange=-1
        // [ 2=>[info, details] ] info:exchange details:sites   area>1 exchange>-1
        
        // info&details : [title=>'', progress=>'' , count=>'', attributes=>[] ]
        // 0: info and areas
        // 2~8: info and exchanges
        
        //info array [title, progress, count, attributes] -> [area2, 40%, 22, [ [op,cnt,%] , [] ...]]
        
        $tableInfo = [];
        $array = [];
        if($area == -1)
        {
            //all areas
            $info = [];
            $details = [];
            $info = $this->getTableInfoArray($project_id, -1, -1, $phase);
            
            $array = $this->getTableInfoArray($project_id, 2, -1, $phase);
            array_push($details, $array);
            
            $array = $this->getTableInfoArray($project_id, 3, -1, $phase);
            array_push($details, $array);
            
            $array = $this->getTableInfoArray($project_id, 4, -1, $phase);
            array_push($details, $array);
            
            $array = $this->getTableInfoArray($project_id, 5, -1, $phase);
            array_push($details, $array);
            
            $array = $this->getTableInfoArray($project_id, 6, -1, $phase);
            array_push($details, $array);
            
            $array = $this->getTableInfoArray($project_id, 7, -1, $phase);
            array_push($details, $array);
            
            $array = $this->getTableInfoArray($project_id, 8, -1, $phase);
            array_push($details, $array);
            
            $tableInfo = ['info'=>$info, 'details'=>$details];
        }
        else if( ($area > 1) && ($exchange_id == -1) )
        { // all exchanges in area
            $info = [];    //area
            $details = []; //exchanges
            $array = [];
            $info = $this->getTableInfoArray($project_id, $area, -1, $phase);
            
            $exchanges = [];
            if($phase > -1)
                $exchanges = \app\models\PcViewRecords::find()->select("center_id")->distinct()->where(['project_id'=>$project_id, 'area'=>$area, 'phase'=>$phase])->asArray()->all();
            else 
                $exchanges = \app\models\PcViewRecords::find()->select("center_id")->distinct()->where(['project_id'=>$project_id, 'area'=>$area])->asArray()->all();
                
            foreach($exchanges as $exchange)
            {
                $array = $this->getTableInfoArray($project_id, $area, $exchange['center_id'], $phase);
                array_push($details, $array);
            }
            
            $tableInfo = ['info'=>$info, 'details'=>$details];
        }
        else if( ($area > 1) && ($exchange_id > -1) )
        {
            // specific exchange
            $info = [];    //exchange
            $details = []; //sites
            $array = [];
            $info = $this->getTableInfoArray($project_id, $area, $exchange_id, $phase);
            
            if($phase > -1)
                $sites = \app\models\PcViewRecords::find()->select("exchange_id")->distinct()->where(['project_id'=>$project_id, 'area'=>$area, 'center_id'=>$exchange_id, 'phase'=>$phase])->asArray()->all();
            else
                $sites = \app\models\PcViewRecords::find()->select("exchange_id")->distinct()->where(['project_id'=>$project_id, 'area'=>$area, 'center_id'=>$exchange_id])->asArray()->all();
                
            foreach($sites as $site)
            {
                $array = $this->getTableInfoArray($project_id, $area, $site['exchange_id'], $phase);
                array_push($details, $array);
            }
            
            $tableInfo = ['info'=>$info, 'details'=>$details];
        }
        
        return $tableInfo;
    }

    private function getTableInfoArray($project_id, $area, $exchange_id, $phase=-1)
    {
        // [ 0=>[ info=>[title=>'', progress=>'' , count=>'', attributes=>[] ], details=>[ area=>[...] ] ], 2=>[ info=>[], details=>[] ], 3=>[], 4=>[], ... ]
        // 0: info and areas
        // 2~8: info and exchanges
        
        // [title, progress, count, attributes] -> [area2, 40%, 22, [ [op,cnt,%] , [] ...]]
        
        $info = [];
        if($area == -1)
        {
            $title = "کل مناطق";
            $progress = $this->getProgress($project_id, -1, -1, $phase);
            $count = $this->getCount($project_id, -1, -1, $phase);
            $dedicated = $this->getDedicated($project_id , -1);
            $onAction = $this->getOnAction($project_id, -1);
            $attributes = $this->getAttributes($project_id,-1, -1, $phase); //[ opId=>[op,cnt,perc], [], [], ...];
            $info = ['title'=>$title, 'progress'=>$progress, 'count'=>$count, 'dedicated'=>$dedicated, 'onAction'=>$onAction,  'attributes'=>$attributes, 'phase'=>$phase];
        }
        else if( ($area > 1) && ($exchange_id == -1) )
        {
            // area
            $title = "منطقه " . $area;
            $progress = $this->getProgress($project_id, $area, -1, $phase);
            $count = $this->getCount($project_id, $area, -1, $phase);
            $dedicated = $this->getDedicated($project_id , $area);
            $onAction = $this->getOnAction($project_id, $area);
            $attributes = $this->getAttributes($project_id, $area, -1, $phase); //[ [op,cnt,perc], [], [], ...];
            $info = ['title' => $title, 'progress' => $progress, 'count' => $count, 'dedicated'=>$dedicated, 'onAction'=>$onAction, 'attributes' => $attributes, 'phase'=>$phase];
        }
        else if( ($area > 1) && ($exchange_id > -1) )
        {
            $exchange = \app\models\PcExchanges::find()->select('name, type')->where(['id'=>$exchange_id])->one();
            $type = $exchange['type'];
            if($type == 2) $title = "مرکز ".$exchange['name'];
            if($type == 3) $title = "سایت ".$exchange['name'];
            $progress = $this->getProgress($project_id, $area, $exchange_id, $phase);
            $count = $this->getCount($project_id, $area, $exchange_id, $phase);
            $attributes = $this->getAttributes($project_id, $area, $exchange_id, $phase); //[ [op,cnt,perc], [], [], ...];
            $info = ['title'=>$title, 'progress'=>$progress, 'count'=>$count, 'usedPerDedicate'=>-1,  'attributes'=>$attributes, 'phase'=>$phase];
        }
        
        return $info;
    }

    private function getAttributes($project_id, $area, $exchange_id, $phase=-1)
    {
        //[ id=>[op,cnt,perc], [], [], ...]
        $exCond = [];
        if($exchange_id > -1)
            $exCond = ['or', ['exchange_id'=>(integer)$exchange_id], ['center_id'=>(integer)$exchange_id]];
        else if( ($exchange_id == -1) && ($area > 1) )
            $exCond = ['area'=>$area];

        $totalRecord = $this->getCount($project_id, $area, $exchange_id, $phase);

        //        operations type 1
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id])->andWhere(['type_id'=>1])->orderBy('priority')->asArray()->all();
        $choices = \app\models\PcViewChoices::find()->where(['project_id'=>$project_id])->asArray()->all();
        $array = [];
        foreach($choices as $ch)
        {
            if($ch['choice_weight'] > 0)
                $array[$ch['id']] = $ch['choice'];
        }
        $choices = $array;
        $array = [];
        foreach ($operations as $op)
        {
            $opId = $op['id'];
            $opName = $op['operation'];

            if($phase > -1)
                  $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId, 'phase'=>$phase])->andWhere($exCond)->groupBy("op_value")->asArray()->all();
            else
                  $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId])->andWhere($exCond)->groupBy("op_value")->asArray()->all();


            foreach($opRec as $oR)
            {
                if($op['type_id'] == 1)
                {
                    if(isset($choices[$oR['op_value']]))
                    {
                        //array_push($array,[$op['operation'], $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1)]);
                        $array[$opId] = [$op['operation'], $oR['cnt'],  round($oR['cnt']/$totalRecord*100, 1)." % "];
                    }
                }
                
                //                if(!empty($choices[$oR['op_value']]))
              //                  {
               //                        //array_push($op3Stat,[$op['operation'], $choices[$oR['op_value']].' : '.$oR['cnt']]);
              //                        if(str_contains($choices[$oR['op_value']], ' شده'))
             //                            array_push($attributes,[$op['operation'], $oR['cnt'],  ' % '.round($oR['cnt']/$totalRecord*100, 1), $recId]);
            //                    }
            }

        }

        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id])->andWhere(['type_id'=>3])->orderBy('priority')->asArray()->all();
        foreach ($operations as $op) // numeric
        {
            $opId = $op['id'];
            $opName = $op['operation'];

            $summ = 0;
            if($phase > -1)
                $summ = \app\models\PcViewRecords::find()->select("SUM(op_value::INTEGER) as summ")->where(['op_id'=>$opId, 'phase'=>$phase])->andWhere($exCond)->scalar();
            else 
                $summ = \app\models\PcViewRecords::find()->select("SUM(op_value::INTEGER) as summ")->where(['op_id'=>$opId])->andWhere($exCond)->scalar();

            $array[$opId] = [$op['operation'], $summ, "-"];
        }

        return $array;
    }

    private function getProgress($project_id, $area, $exchange_id, $phase=-1)
    {

        if($exchange_id > 0)
        {
            $type = \app\models\PcExchanges::find()->select(['type'])->where(['id'=>$exchange_id])->scalar();
            if($type == 3)
            {
                if($phase > -1)
                    $prog = \app\models\PcViewExchanges::find()->select('(weight*100/project_weight) as percentage')->where(['project_id'=>$project_id, 'id'=>$exchange_id, 'phase'=>$phase])->andWhere(['>', 'project_weight', 0])->scalar();
                else 
                    $prog = \app\models\PcViewExchanges::find()->select('(weight*100/project_weight) as percentage')->where(['project_id'=>$project_id, 'id'=>$exchange_id])->andWhere(['>', 'project_weight', 0])->scalar();
            }
            else
            {
                if($phase > -1)
                {
                    $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project_id, 'area'=>$area, 'center_id'=>$exchange_id, 'phase'=>$phase]);
                    $prog = \app\models\PcViewExchanges::find()->select(' SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project_id, 'phase'=>$phase])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->asArray()->one();
                    if($prog['cnt'] == 0) 
                        $prog = 0;
                    else 
                        $prog = round($prog['percentage'] / $prog['cnt']);
                }
                else 
                {
                    $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project_id, 'area'=>$area, 'center_id'=>$exchange_id]);
                    $prog = \app\models\PcViewExchanges::find()->select(' SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project_id])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->asArray()->one();
                    if($prog['cnt'] == 0) 
                        $prog = 0;
                    else 
                        $prog = round($prog['percentage'] / $prog['cnt']);
                }
            }
        }
        else if ($area > 1)
        {
            $field = 'area'.$area; // area2 ...  field
            $lomCount = \app\models\PcLom::find()->select('SUM('.$field.')')->where(['project_id'=>$project_id])->scalar();
            $used = \app\models\PcViewLomDetail::find()->select('SUM(quantity)')->where(['project_id'=>$project_id, 'area'=>$area])->scalar();
            
            if($phase > -1)
            {
                $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project_id, 'area'=>$area, 'phase'=>$phase]); 
                $prog = \app\models\PcViewExchanges::find()->select(' SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project_id, 'phase'=>$phase])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->asArray()->one();
            }
            else 
            {
                $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project_id, 'area'=>$area]); 
                $prog = \app\models\PcViewExchanges::find()->select(' SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project_id])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->asArray()->one();
            }
            
            if(($prog['cnt'] == 0) || ($lomCount == 0) )$prog = 0; 
            else  
                $prog = round($prog['percentage'] / $prog['cnt'] * $used/$lomCount);
                //$prog = round($prog['percentage'] / $lomCount);
            
        }
        else
        {
            $lomCount = \app\models\PcLom::find()->select('SUM(quantity)')->where(['project_id'=>$project_id])->scalar();
            $used = \app\models\PcViewLomDetail::find()->select('SUM(quantity)')->where(['project_id'=>$project_id])->scalar();

            if($phase > -1)
            {
                $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project_id, 'phase'=>$phase]);
                $prog = \app\models\PcViewExchanges::find()->select('SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project_id, 'phase'=>$phase])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->asArray()->one();
            }
            else 
            {
                $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project_id]);
                $prog = \app\models\PcViewExchanges::find()->select('SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project_id])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->asArray()->one();
            }

            if(($prog['cnt'] == 0) || ($lomCount == 0) )$prog = 0; 
            else 
            $prog = round($prog['percentage'] / $prog['cnt'] * $used/$lomCount); // )

        }
        if($prog > 100)
            $prog = "-";
        else 
            $prog = $prog." % ";
        
        return $prog;
    }

    private function getCount($project_id, $area, $exchange_id, $phase=-1)
    {
        $count = 0;
        if($exchange_id > -1)
        {
            if($phase > -1)
            {
                $exCond = ['or', ['exchange_id'=>(integer)$exchange_id], ['center_id'=>(integer)$exchange_id]];
                $count = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id, 'phase'=>$phase])->andWhere($exCond)->scalar();
            }
            else 
            {
                $exCond = ['or', ['exchange_id'=>(integer)$exchange_id], ['center_id'=>(integer)$exchange_id]];
                $count = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id])->andWhere($exCond)->scalar();
            }

        }
        else if ($area > 1)
        {
            if($phase > -1)
            {
                $exCond = ['area'=>$area];
                $count = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id, 'phase'=>$phase])->andWhere($exCond)->scalar();
            }
            else 
            {
                $exCond = ['area'=>$area];
                $count = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id])->andWhere($exCond)->scalar();
            }

        }
        else
        {
            if($phase > -1)
            {
                $count = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id, 'phase'=>$phase])->scalar();
            }
            else 
            {
                $count = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project_id])->scalar();
            }
        }

        return $count;
    }

    private function getDedicated($project_id, $area)
    {
        $dedicate = 0;
        
        if ($area > 1)
        {
                $exCond = ['area'=>$area];
                $field = 'area'.$area; // area2 ...  field
                $dedicate = \app\models\PcLom::find()->select('SUM('.$field.')')->where(['project_id'=>$project_id])->scalar();
        }
        else
        {
            $allRec = \app\models\PcLom::find()->where(['project_id'=>$project_id])->asArray()->all();
            $sum = 0;
            foreach($allRec as $rec)
                $sum = $sum + $rec['area2'] + $rec['area3'] + $rec['area4'] + $rec['area5'] + $rec['area6'] + $rec['area7'] + $rec['area8'];

            $dedicate = $sum;

        }
        
        return $dedicate;
    }

    private function getOnAction($project_id, $area)
    {
        $count = 0;
        
        if ($area > 1)
        {
                $exCond = ['area'=>$area];
                $field = 'area'.$area; // area2 ...  field
                $count = \app\models\PcViewLomDetail::find()->select('SUM(quantity)')->where(['project_id'=>$project_id, 'area'=>$area])->scalar();
        }
        else
        {
            $count = \app\models\PcViewLomDetail::find()->select('SUM(quantity)')->where(['project_id'=>$project_id])->scalar();
        }
        
        if(empty($count)) $count = 0;
        return $count;
    }

    public function actionReport_tablestat($id, $AREA, $EXCHANGE_ID, $PHASE=-1)
    {
        {
            $session = Yii::$app->session;
            $session->open();
            if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
            $userProjects = $session['userProjects'];
            $upId = [];
            foreach ($userProjects as $i=>$up) array_push($upId, $i);
            $project_id = $id;
            if($id > -1)
            {
                $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
                $session = Yii::$app->session;
                $session->open();
                $userProjects = $session['userProjects'];
                if(isset($userProjects[$project['id']]))
                {
                    $ae = $userProjects[$project['id']];
                    $area = $ae['area'];
                    $exchange_id = $ae['exchange_id'];

                    $accessLevel=['level'=>-1, 'name'=>''];
                    if(empty($ae['area']) && empty($ae['exchange_id']) )
                    {
                        $accessLevel['level'] = 1;
                    }
                    else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                    {
                        $accessLevel['level'] = 2;
                    }
                    else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                    {
                        $accessLevel['level'] = 3;
                    }

                    //###################################
                    $recId = 1;
                    $tableInfo = [];
                    // [ 0=>[info, details] ] info:kol  details:areas       area=-1
                    // [ 2=>[info, details] ] info:area details:exchanges   area>1 exchange=-1
                    // [ 2=>[info, details] ] info:exchange details:sites   area>1 exchange>-1

                    // info&details : [title=>'', progress=>'' , count=>'', attributes=>[] ]
                    // 0: info and areas
                    // 2~8: info and exchanges

                    //info array [title, progress, count, attributes] -> [area2, 40%, 22, [ [op,cnt,%] , [] ...]]

                    if($accessLevel['level'] ==  1)
                    {
                        $tableInfo = $this->getTableInfo($project_id, $AREA, $EXCHANGE_ID, $PHASE );
                    }
                    else if($accessLevel['level'] == 2)
                    {
                        $tableInfo = $this->getTableInfo($project_id, $area, $EXCHANGE_ID, $PHASE);
                    }
                    else if($accessLevel['level'] == 3)
                    {
                        $tableInfo = $this->getTableInfo($project_id, $area, $exchange_id, $PHASE);
                    }

                    $column = 4;
                    $temp = [];  // [id=>op,  ]
                    $opMap = []; // [ column=>[opId,title]]*
                    $ops = \app\models\PcOperations::find()->where(['project_id'=>$project_id, 'type_id'=>1])->orderBy('priority')->asArray()->all();
                    foreach($ops as $o)
                    {
                        $temp[$o['id']] = $o['operation'];
                    }

                    foreach($temp as $id=>$op)
                    {
                        $opMap[$column] = ['id'=>$id, 'title'=>$op];
                        $column++;
                    }

                   //                    REPORT
                    $this->ReportTableStat($project['project'], $tableInfo, $opMap );
                }

            }

            return $this->redirect(['stat/totaltablestat']);
        }
    }

    private function ReportTableStat($project, $tableInfo, $opMap )
    {
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>16, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'bb6d85']
                ],

                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $HeaderStyle =
            [
                'font' => ['bold' => false,'size'=>14, 'color' => ['rgb' => '1b6d85'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'eeeeef']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],

                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                ],
            ];

        $ContentStyle =
            [
                'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'],'name'=>"Tahoma"],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                ],
            ];

        $SummaryStyle =
            [
                'font' => ['bold' => true,'size'=>18, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '806201']
                ],

                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        //topic
        $sheet->mergeCells('A1:C1');
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "جدول آماری");

        $sheet->mergeCells('A2:C2');
        $sheet->getRowDimension('2')->setRowHeight(30);
        $sheet->getStyle('A2')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A2', $project);

        $sheet->mergeCells('A3:C3');
        $sheet->getRowDimension('3')->setRowHeight(30);
        $sheet->getStyle('A3')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A3', \app\components\Jdf::jdate('Y/m/d', time()));

            //header
            $row = 4;
            $sheet->getRowDimension($row)->setRowHeight(40);

            $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('A'.$row, 'عنوان');
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('B'.$row, 'درصد پیشرفت');
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getStyle('C'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('C'.$row, 'تعداد');
            $sheet->getColumnDimension('C')->setWidth(30);

            $index = 4;
            foreach ($opMap as $col => $array)
            {
                $col = Cell\Coordinate::stringFromColumnIndex($index);
                $sheet->getStyle($col.$row)->applyFromArray($HeaderStyle);
                $sheet->setCellValue($col.$row, $array['title']);
                $sheet->getColumnDimension($col)->setWidth(30);
                $index++;
            }

            $row++;
           //            info
        $sheet->getRowDimension($row)->setRowHeight(30);

        $info = $tableInfo['info'];
        $sheet->getStyle('A'.$row)->applyFromArray($ContentStyle);
        $sheet->setCellValue('A'.$row, $info['title']);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
        $sheet->setCellValue('B'.$row, $info['progress']);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getStyle('C'.$row)->applyFromArray($ContentStyle);
        $sheet->setCellValue('C'.$row, $info['count']);
        $sheet->getColumnDimension('C')->setWidth(30);

        $index = 4;
        foreach($opMap as $col=>$array)
        {
            $val = 0;
            if(isset($info['attributes'][$array['id']]))
            {
                $temp = $info['attributes'][$array['id']];
                $opId = $array['id'];
                $count = $temp[1];
                $perc = $temp[2];
                $val = $count;
            }
            $column = Cell\Coordinate::stringFromColumnIndex($index);
            $sheet->getStyle($column.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue($column.$row, $val);
            $sheet->getColumnDimension($column)->setWidth(30);

            $index++;
        }

        $row++;
          //            details
            $details = $tableInfo['details'];
        foreach($details as $detail)
        {
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('A'.$row, $detail['title']);
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $detail['progress']);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getStyle('C'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('C'.$row, $detail['count']);
            $sheet->getColumnDimension('C')->setWidth(30);

            $index = 4;
            foreach($opMap as $col=>$array)
            {
                $val = 0;
                if(isset($detail['attributes'][$array['id']]))
                {
                    $temp = $detail['attributes'][$array['id']];
                    $opId = $array['id'];
                    $count = $temp[1];
                    $perc = $temp[2];
                    $val = $count;
                }

                $column = Cell\Coordinate::stringFromColumnIndex($index);
                $sheet->getStyle($column.$row)->applyFromArray($ContentStyle);
                $sheet->setCellValue($column.$row, $val);
                $sheet->getColumnDimension($column)->setWidth(30);

                $index++;
            }
            $row++;
        }


        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Report-Table-Stat'.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    public function actionProgress($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        $project_id = $id;
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();

            $session = Yii::$app->session;
            $session->open();
            $areaSelection = [];

            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            $areaSelection = [];
            if(isset($userProjects[$project['id']]))
            {
                $ae = $userProjects[$project['id']];
                $area = $ae['area'];

                $accessLevel=['level'=>-1, 'name'=>''];
                if(empty($ae['area']) && empty($ae['exchange_id']) )
                {
                    $accessLevel['level'] = 1;
                }
                else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                {
                    $accessLevel['level'] = 2;
                }
                else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                {
                    $accessLevel['level'] = 3;
                }

                $exchanges = [];
                if($accessLevel['level'] == 1)
                {
                    $areaSelection = [-1 => 'کل مناطق', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'];
                }
                else if($accessLevel['level'] == 2)
                {
                    $areaSelection = [$ae['area']=>$ae['area']];
                }
                else if($accessLevel['level'] == 3)
                {
                    $areaSelection = [$ae['area']=>$ae['area']];
                }

                $params = Yii::$app->request->post();
                $phase = -1;
                if(isset($params['search']))
                {
                    $area = $params['search']['area'];

                    if(isset($params['search']['phase']))
                        $phase = $params['search']['phase'];

                }

                if(empty($searchParams['area'])) $searchParams['area'] = -1;

                //###################################
                if($area < 2) $area = -1;

                $progressInfo =[];
                if($area < 2)
                {
                    $progressInfo[0]=[];
                    $ids = [];
                    if($phase > -1)
                        $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project['id'], 'phase'=>$phase]);
                    else 
                        $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project['id']]);
                    
                    $prog = \app\models\PcViewExchanges::find()->select('area, SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project['id']])->andWhere(['>', 'project_weight', 0])->andWhere(['in', 'id', $ids])->groupBy('area')->orderBy(['area'=>SORT_ASC])->asArray()->all();
                    $total = 0;
                    $cnt = 0;

                    foreach ($prog as $p)
                    {
                        $cnt++;
                        $total += $p['percentage']/$p['cnt'];
                        $progressInfo[$p['area']] = ['name'=>' منطقه '.$p['area'], 'percentage'=>round($p['percentage']/$p['cnt'], 1)];
                    }
                    if($cnt > 0) $progressInfo[0] = ['name'=>'کل مناطق', 'percentage'=>round($total/$cnt, 1)];
                }
                else
                {
                    $ids = [];
                    if($phase > -1)
                        $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project['id'], 'phase'=>$phase]);
                    else
                        $ids = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$project['id']]);
                    $prog = \app\models\PcViewExchanges::find()->select('SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project['id'], 'area'=>$area])->andWhere(['in', 'id', $ids])->andWhere(['>', 'project_weight', 0])->asArray()->all();

                    foreach ($prog as $p)
                    {
                        if($p['cnt'] > 0)
                            $progressInfo[0] = ['name'=>' منطقه '.$area, 'percentage'=>round($p['percentage']/$p['cnt'], 1)];
                    }
                    //find all exchanges
                    $prog = \app\models\PcViewExchanges::find()->select('center_name, SUM(weight*100/project_weight) as percentage, COUNT(id) as cnt')->where(['project_id'=>$project['id'], 'area'=>$area])->andWhere(['in', 'id', $ids])->andWhere(['>', 'project_weight', 0])->groupBy('center_name')->orderBy(['center_name'=>SORT_ASC])->asArray()->all();
                    $i = 1;
                    foreach ($prog as $p)
                    {
                        if(empty($p['center_name'])) continue;
                        $progressInfo[$i] = ['name'=>' مرکز '.$p['center_name'], 'percentage'=>round($p['percentage']/$p['cnt'], 1)];
                        $i++;
                    }
                }


                return $this->render('progress', ['area'=>$area, 'phase'=>$phase,  'project'=>$project, 'projects'=>$projects,  'areaSelection'=>$areaSelection,  'progressInfo'=>$progressInfo]);
            }

        }
        return $this->render('progress', ['area'=>-1,  'phase'=>-1,  'project'=>[], 'projects'=>$projects, 'areaSelection'=>[],  'progressInfo'=>[]]);
    }

    public function actionDedication($id=-1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        $project_id = $id;
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$id, 'enabled'=>true])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            if(isset($userProjects[$id]))
            {
                $ae = $userProjects[$id];
                $area = $ae['area'];
                if(empty($area)) $area  = -1;
                $area = $area * 1;
                $lom = \app\models\PcLom::find()->where(['project_id'=>$id])->all();
                $details= [];
                if($area == -1)
                    $details = \app\models\PcViewLomDetail::find()->select("area, lom_id, SUM(quantity) AS usage")->where(['project_id'=>$id])->groupBy("area, lom_id")->asArray()->all();
                else
                    $details = \app\models\PcViewLomDetail::find()->select("area, lom_id, SUM(quantity) AS usage")->where(['project_id'=>$id, 'area'=>$area])->groupBy("area, lom_id")->asArray()->all();

                $detailArray = [];
                foreach($details as $d)
                    $detailArray[$d['lom_id']][$d['area']] = $d['usage'];

                $lomArray = [];
                foreach($lom as $lm)
                    $lomArray[$lm->id] = ['equipment'=>$lm->equipment, 'quantity'=>$lm->quantity, 'desc'=>$lm->description, 'area2'=>$lm->area2, 'area3'=>$lm->area3, 'area4'=>$lm->area4, 'area5'=>$lm->area5, 'area6'=>$lm->area6, 'area7'=>$lm->area7, 'area8'=>$lm->area8];

                $modelArray = [];
                if($area == -1)
                {
                    $modelArray = [0 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => []]; // [all, area2, area3 ... ]
                    // area2=>[modelArray, ]
                    foreach ($lomArray as $id=>$param)
                    {
                        //0
                        $AREA = 0;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['quantity'];
                        $used = \app\models\PcViewLomDetail::find()->select("SUM(quantity) as usage")->where(['project_id'=>$project_id, 'lom_id'=>$id])->scalar();
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //2
                        $AREA = 2;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //3
                        $AREA = 3;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //4
                        $AREA = 4;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //5
                        $AREA = 5;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //6
                        $AREA = 6;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //7
                        $AREA = 7;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);

                        //8
                        $AREA = 8;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);
                    }

                }
                else
                {
                    $modelArray[$area] = [];
                    
                    foreach ($lomArray as $id=>$param)
                    {
                        $AREA = $area;
                        $model = ['lom_id'=>-1, 'equipment'=>"", 'quantity'=>0, 'data'=>[], 'labels'=>[], 'colors'=>[], 'convas'=>0];
                        $model['lom_id'] = $id;
                        $model['equipment'] = $param['equipment'];
                        $model['quantity'] = $param['area'.$AREA];
                        $used = (isset($detailArray[$id][$AREA]))? $detailArray[$id][$AREA] : 0;
                        $val = 0;
                        if($model['quantity'] > 0)
                            $val = round($used / $model['quantity'] *100, 1);
                        $usedPer = $val;
                        $leftPer = 100.0 - $usedPer;
                        $model['data'] = [$leftPer , $usedPer];
                        $model['labels'] = ["تخصیصی"." [ ".$model['quantity']." ] ", "مصرفی"." [ ".$used." ] "];
                        $model['colors'] = $this->getGraphColorArray(2);
                        $model['convas'] = "convas".$AREA.$id;
                        array_push($modelArray[$AREA], $model);
                    }
                }
                //modelArray : [ 0=>[lom1=>model1 , lom2=>model2 ], 2=>[] ... ]
                //return var_dump($modelArray[2]);
                return $this->render('dedication', ['lom'=>$lom, 'modelArray'=>$modelArray,'area'=>$area, 'projects'=>$projects, 'project'=>$project]);
            }
        }
        
        return $this->render('dedication', ['lom'=>"", 'modelArray'=>[], 'area'=>"", 'projects'=>$projects, 'project'=>"", 'modelArray'=>[]]);
    }

    public function actionProvince()
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['project'])) return $this->redirect(['main/login']);
        $project = $session['project'];
        $projectName = ' پروژه '.$project['project'];
        $phaseNo = -1;
        $phase = ['operation'=>'فاز'];
        if(isset(Yii::$app->request->post()['phaseNo']))
        {
            $phaseNo = Yii::$app->request->post()['phaseNo'];
            if($phaseNo > -1)
                $phase = ['operation'=>'فاز', 'op_value'=>$phaseNo];
            else
                $phaseNo = -1;
        }

        $totalRecord = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project['id']])->andWhere($phase)->scalar();
        if($totalRecord == 0)
        {
            $areas = [];
            $op1Stat =[];
            $op3Stat =[];
            return $this->render('province', ['phaseNo'=>$phaseNo, 'areas'=>$areas, 'op1Stat'=>$op1Stat, 'op3Stat'=>$op3Stat, 'projectName'=>$projectName]);
        }
        $areas = \app\models\PcViewRecords::find()->select("area, COUNT(DISTINCT exchange_id) as cnt")->where(['project_id'=>$project['id']])->andWhere($phase)->groupBy("area")->asArray()->all();
        //        [[area=>2,cnt=>202], [area=>4,cnt=>2], ... ]
        $data = [];
        $label = [];
        $color = $this->getGraphColorArray(sizeof($areas));
        foreach ($areas as $a)
        {
            array_push($data, round($a['cnt']/$totalRecord*100, 1));
            array_push($label, "Area ".$a['area'].' ['.$a['cnt'].'] ');
        }
        $areas = [$data, $color, $label];

        //operations type 3 numeric
        $op3Stat = [];
        array_push($op3Stat, ['تعداد کل', $totalRecord]);
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>3])->orderBy('priority')->asArray()->all();
        $phasex = \app\models\PcViewRecords::find()->select('exchange_id')->where(['project_id'=>$project['id']])->andwhere($phase);
        foreach ($operations as $op)
        {
            $opId = $op['id'];
            $val = \app\models\PcRecords::find()->select("SUM(op_value::decimal)")->where(['op_id'=>$opId, 'project_id'=>$project['id']])->andWhere(['in', 'exchange_id',$phasex])->scalar();
            array_push($op3Stat,[$op['operation'], $val]);
        }

        //        operations type 1
        $op1Stat = [];// [ kv=>[data, color, label, convasid] , [op2], [op3], ]
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>1])->orderBy('priority')->asArray()->all();
        $choices = \app\models\PcViewChoices::find()->where(['project_id'=>$project['id']])->asArray()->all();
        $array = [];
        foreach($choices as $ch)
        {
            $array[$ch['id']] = $ch['choice'];
        }
        $choices = $array;

        foreach ($operations as $op)
        {
            $data = [];
            $color = [];
            $label = [];
            $opId = $op['id'];
            //            $opChoices = $choices[$opId]; // [2=>'onu', 3=>'copp' ...]
            $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId])->andWhere(['in', 'exchange_id',$phasex])->groupBy("op_value")->asArray()->all();
            $color = $this->getGraphColorArray(sizeof($opRec));
            foreach($opRec as $oR)
            {
                //array_push($op3Stat,[$op['operation'], $choices[$oR['op_value']].' : '.$oR['cnt']]);

                array_push($data, round($oR['cnt']/$totalRecord*100, 1));
                array_push($label, $choices[$oR['op_value']].' ['.$oR['cnt'].'] ');
            }
            if(sizeof($data) >0)
                $op1Stat[$op['operation']] = [$data, $color,$label, "convas".$op['id']];
            else
                $op1Stat[$op['operation']] = [[0], $this->getGraphColorArray(1),[''], "convas".$op['id']];
        }


        return $this->render('province', ['phaseNo'=>$phaseNo, 'areas'=>$areas, 'op1Stat'=>$op1Stat, 'op3Stat'=>$op3Stat, 'projectName'=>$projectName]);
    }

    public function actionArea()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $ae = $userProjects[$project['id']];
            $area = $ae['area'];
            $exchange_id = $ae['exchange_id'];
            if( empty($area) && empty($exchange_id) )
            {
                //admin
                if(Yii::$app->request->isPost)
                {
                    $area = Yii::$app->request->post()['area'];
                    if($area > 0)
                    {
                        return $this->stat_area($area);
                    }
                }

                $areas = [2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
                return $this->render('getStatArea', ['areas'=>$areas]);
            }
            else if($area > 0)
            {
                // area
                return $this->stat_area($area);
            }
        }

        return $this->redirect(['main/login']);
    }
    private function stat_area($area = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['project'])) return $this->redirect(['main/login']);
        $project = $session['project'];
        $projectName = ' پروژه '.$project['project'];
        $phaseNo = -1;
        $phase = ['operation'=>'فاز'];
        if(isset(Yii::$app->request->post()['phaseNo']))
        {
            $phaseNo = Yii::$app->request->post()['phaseNo'];
            if($phaseNo > -1)
                $phase = ['operation'=>'فاز', 'op_value'=>$phaseNo];
            else
                $phaseNo = -1;
            
            if(isset(Yii::$app->request->post()['area'])) $area = Yii::$app->request->post()['area'];
        }

        $totalRecord = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['project_id'=>$project['id'], 'area'=>$area])->andWhere($phase)->scalar();
        if($totalRecord == 0)
        {
            $op1Stat =[];
            $op3Stat =[];
            return $this->render('area', ['phaseNo'=>$phaseNo, 'op1Stat'=>$op1Stat,'op3Stat'=>$op3Stat, 'area'=>$area, 'projectName'=>$projectName]);
        }
         //operations type 3 numeric
        $op3Stat = [];
        array_push($op3Stat, ['تعداد کل', $totalRecord]);
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>3])->orderBy('priority')->asArray()->all();
        $phasex = \app\models\PcViewRecords::find()->select('exchange_id')->where(['project_id'=>$project['id']])->andwhere($phase);

        foreach ($operations as $op)
        {
            $opId = $op['id'];
            $val = \app\models\PcViewRecords::find()->select("SUM(op_value::decimal)")->where(['op_id'=>$opId,'area'=>$area, 'project_id'=>$project['id']])->andWhere(['in', 'exchange_id',$phasex])->scalar();
            array_push($op3Stat,[$op['operation'], $val]);
        }
        
        //        operations
        $op1Stat = [];// [ kv=>[data, color, label, convasid] , [op2], [op3], ]
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>1])->orderBy('priority')->asArray()->all();
        $choices = \app\models\PcViewChoices::find()->where(['project_id'=>$project['id']])->asArray()->all();
        $array = [];
        foreach($choices as $ch)
        {
            $array[$ch['id']] = $ch['choice'];
        }
        $choices = $array;

        foreach ($operations as $op)
        {
            $data = [];
            $label = [];
            $opId = $op['id'];
            $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId, 'area'=>$area])->andWhere(['in', 'exchange_id',$phasex])->groupBy("op_value")->asArray()->all();
            $color = $this->getGraphColorArray(sizeof($opRec));
            foreach($opRec as $oR)
            {
             //   array_push($op3Stat,[$op['operation'], $choices[$oR['op_value']].' : '.$oR['cnt']]);
                
                array_push($data, round($oR['cnt']/$totalRecord*100, 1));
                array_push($label, $choices[$oR['op_value']].' ['.$oR['cnt'].'] ');
            }

            if(sizeof($data) >0)
                $op1Stat[$op['operation']] = [$data, $color,$label, "convas".$op['id']];
            else
                $op1Stat[$op['operation']] = [[0], $this->getGraphColorArray(1),[''], "convas".$op['id']];
        }



        return $this->render('area', ['phaseNo'=>$phaseNo, 'op1Stat'=>$op1Stat,'op3Stat'=>$op3Stat, 'area'=>$area, 'projectName'=>$projectName]);
    }

    public function actionExchange()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $ae = $userProjects[$project['id']];
            $area = $ae['area'];
            $exchange_id = $ae['exchange_id'];

            if( empty($area) && empty($exchange_id) )
            {
                //admin
                if(Yii::$app->request->isPost)
                {
                    $exchange_id = Yii::$app->request->post()['exchange_id'];
                    if($exchange_id > 0)
                    {
                        return $this->stat_exchange($exchange_id);
                    }
                }

                $areas = [-1=>'', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
                $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id'=>$project['id'], 'type'=>2])->orderBy('area, name')->asArray()->all();
                $array = [];
                foreach ($exchanges as $exch)
                {
                    $array[$exch['area']][$exch['id']] = $exch['name'];
                }

                return $this->render('getStatExchange', ['areas'=>$areas, 'exchanges'=>$array]);
            }
            else if(($area > 0) && empty($exchange_id) )
            {
                // area
                if(Yii::$app->request->isPost)
                {
                    $exchange_id = Yii::$app->request->post()['exchange_id'];
                    if($exchange_id > 0)
                    {
                        return $this->stat_exchange($exchange_id);
                    }
                }

                $areas = [-1=>'', $area=>(string)$area];
                $exchanges = \app\models\PcExchanges::find()->select('id, name')->where(['project_id'=>$project['id'], 'type'=>2, 'area'=>$area])->orderBy('name')->asArray()->all();
                $array = [];
                foreach ($exchanges as $exch)
                {
                    $array[$area][$exch['id']] = $exch['name'];
                }

                return $this->render('getStatExchange', ['areas'=>$areas, 'exchanges'=>$array]);
            }
            else if($exchange_id > 0)
            {
                //exchange
                return $this->stat_exchange($exchange_id);
            }
        }

        return $this->redirect(['main/login']);
    }

    private function stat_exchange($exchange_id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['project'])) return $this->redirect(['main/login']);
        $project = $session['project'];
        $projectName = ' پروژه '.$project['project'];
        $exchangeName = \app\models\PcExchanges::find()->select('area,name')->where(['project_id'=>$project['id'] ,'id'=>$exchange_id])->one();
        $area = $exchangeName['area'];
        $exchangeName = " مرکز " .$exchangeName['name'];
        $phaseNo = -1;
        $phase = ['operation'=>'فاز'];
        if(isset(Yii::$app->request->post()['phaseNo']))
        {
            $phaseNo = Yii::$app->request->post()['phaseNo'];
            if($phaseNo > -1)
                $phase = ['operation'=>'فاز', 'op_value'=>$phaseNo];
            else
                $phaseNo = -1;

            if(isset(Yii::$app->request->post()['exchange_id'])) $exchange_id = Yii::$app->request->post()['exchange_id'];
        }

        $totalRecord = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->Where(['OR',['exchange_id'=>$exchange_id], ['center_id'=>$exchange_id]])->andWhere($phase)->scalar();
        if($totalRecord == 0)
        {
            $op1Stat =[];
            $op3Stat =[];
            return $this->render('exchange', ['phaseNo'=>$phaseNo, 'op1Stat'=>$op1Stat, 'op3Stat'=>$op3Stat, 'area'=>$area, 'projectName'=>$projectName, 'exchangeName'=>$exchangeName, 'exchange_id'=>$exchange_id]);
        }

        //operations type 3 numeric
        $op3Stat = [];
        array_push($op3Stat, ['تعداد کل', $totalRecord]);
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>3])->orderBy('priority')->asArray()->all();
        $phasex = \app\models\PcViewRecords::find()->select('exchange_id')->where(['project_id'=>$project['id']])->andwhere($phase);

        foreach ($operations as $op)
        {
            $opId = $op['id'];
            $val = \app\models\PcViewRecords::find()->select("SUM(op_value::decimal)")->where(['op_id'=>$opId, 'project_id'=>$project['id']])->andWhere(['or', ['exchange_id'=>$exchange_id], ['center_id'=>$exchange_id]])->andWhere(['in', 'exchange_id',$phasex])->scalar();
            array_push($op3Stat,[$op['operation'], $val]);
        }

        //        operations
        $op1Stat = [];// [ kv=>[data, color, label, convasid] , [op2], [op3], ]
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>1])->orderBy('priority')->asArray()->all();
        $choices = \app\models\PcViewChoices::find()->where(['project_id'=>$project['id']])->asArray()->all();
        $array = [];
        foreach($choices as $ch)
        {
            $array[$ch['id']] = $ch['choice'];
        }
        $choices = $array;

        foreach ($operations as $op)
        {
            $data = [];
            $label = [];
            $opId = $op['id'];
            $opRec = \app\models\PcViewRecords::find()->select("op_value, COUNT(DISTINCT exchange_id) as cnt")->where(['op_id'=>$opId])->andWhere(['OR',['exchange_id'=>$exchange_id], ['center_id'=>$exchange_id]])->andWhere(['in', 'exchange_id',$phasex])->groupBy("op_value")->asArray()->all();
            $color = $this->getGraphColorArray(sizeof($opRec));
            foreach($opRec as $oR)
            {
                // array_push($op3Stat,[$op['operation'], $choices[$oR['op_value']].' : '.$oR['cnt']]);

                array_push($data, round($oR['cnt']/$totalRecord*100, 1));
                array_push($label, $choices[$oR['op_value']].' ['.$oR['cnt'].'] ');
            }

            if(sizeof($data) >0)
                $op1Stat[$op['operation']] = [$data, $color,$label, "convas".$op['id']];
            else
                $op1Stat[$op['operation']] = [[0], $this->getGraphColorArray(1),[''], "convas".$op['id']];
        }


        return $this->render('exchange', ['phaseNo'=>$phaseNo, 'op1Stat'=>$op1Stat, 'op3Stat'=>$op3Stat, 'area'=>$area, 'projectName'=>$projectName, 'exchangeName'=>$exchangeName, 'exchange_id'=>$exchange_id]);
    }

    //lom
    private function getTotalDedicateModel($id)
    {
        $models = []; // [  id=>[equip, desc, total, left, area2=>[dedication, used] , ...  ] ]

        $loms = \app\models\PcLom::find()->where(['project_id'=>$id])->asArray()->all();
        $used = \app\models\PcViewLomDetail::find()->select("area, lom_id, SUM(quantity) as sum")->where(['project_id'=>$id])->groupBy('area, lom_id')->asArray()->all();
        foreach ($loms as $lom)
        {
            $models[$lom['id']][2] = 0;
            $models[$lom['id']][3] = 0;
            $models[$lom['id']][4] = 0;
            $models[$lom['id']][5] = 0;
            $models[$lom['id']][6] = 0;
            $models[$lom['id']][7] = 0;
            $models[$lom['id']][8] = 0;

        }
        foreach ($used as $rec)
        {
            $models[$rec['lom_id']][$rec['area']] = $rec['sum'];
        }
        $used = $models;

        $models = [];
        foreach ($loms as $lom)
        {
            $left = $lom['quantity'] - ($lom['area2']+$lom['area3']+$lom['area4']+$lom['area5']+$lom['area6']+$lom['area7']+$lom['area8']);
            if($left < 0) $left = 0;
            $models[$lom['id']] = ['equipment'=>$lom['equipment'] , 'description'=>$lom['description'] , 'total'=>$lom['quantity'], 'left'=>$left, 'area2'=>[$lom['area2'], $used[$lom['id']][2]], 'area3'=>[$lom['area3'], $used[$lom['id']][3]], 'area4'=>[$lom['area4'], $used[$lom['id']][4]], 'area5'=>[$lom['area5'], $used[$lom['id']][5]], 'area6'=>[$lom['area6'], $used[$lom['id']][6]], 'area7'=>[$lom['area7'], $used[$lom['id']][7]], 'area8'=>[$lom['area8'], $used[$lom['id']][8]] ];
        }

        return $models;
    }
    private function getAreaDedicateModel($id, $area)
    {
        $models = []; // [  id=>[equip, desc, total, left, area2=>[dedication, used] , ...  ] ]

        $loms = \app\models\PcLom::find()->where(['project_id'=>$id])->asArray()->all();
        $used = \app\models\PcViewLomDetail::find()->select("area, lom_id, SUM(quantity) as sum")->where(['project_id'=>$id, 'area'=>$area])->groupBy('area, lom_id')->asArray()->all();
        foreach ($loms as $lom)
        {
            $models[$lom['id']][$area] = 0;

        }
        foreach ($used as $rec)
        {
            $models[$rec['lom_id']][$rec['area']] = $rec['sum'];
        }
        $used = $models;

        $models = [];
        foreach ($loms as $lom)
        {
            $AREA = 'area'.$area;
            $VAL = [$lom['area'.$area], $used[$lom['id']][$area]];
            $models[$lom['id']] = ['equipment'=>$lom['equipment'] , 'description'=>$lom['description'] , 'total'=>-1, 'left'=>-1, "$AREA"=>$VAL ];
        }

        return $models;
    }

    public function actionDedicate($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();        $project_id = $id;

        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();

            $ae = $userProjects[$project['id']];
            $area = $ae['area'];
            $exchange_id = $ae['exchange_id'];
            $accessLevel=-1; // 1:all; 2:area 3:center
            if(empty($ae['area']) && empty($ae['exchange_id']) )
                $accessLevel = 1;
            else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                $accessLevel = 2;
            else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                $accessLevel = 3;

            $models = [];
            if($accessLevel == 1) // all
                $models = $this->getTotalDedicateModel($id);
            else
                // area and center
                $models = $this->getAreaDedicateModel($id, $area);

            return $this->render('dedicate', ['projects'=>$projects, 'project'=>$project, 'models'=>$models, 'area'=>$area]);
        }

        return $this->render('dedicate', ['projects'=>$projects]);
    }

    public function actionExport_dedicate($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();        $project_id = $id;

        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();

            $ae = $userProjects[$project['id']];
            $area = $ae['area'];
            $exchange_id = $ae['exchange_id'];
            $accessLevel=-1; // 1:all; 2:area 3:center
            if(empty($ae['area']) && empty($ae['exchange_id']) )
                $accessLevel = 1;
            else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                $accessLevel = 2;
            else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                $accessLevel = 3;

            $models = [];
            if($accessLevel == 1) // all
                $models = $this->getTotalDedicateModel($id);
            else
                // area and center
                $models = $this->getAreaDedicateModel($id, $area);

            //export
            $this->exportDedicate($models, $project['project'], $area);
        }

        return $this->redirect(['stat/dedicate']);
    }

    private function exportDedicate($models, $project, $area)
    {
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>16, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'bb6d85']
                ],

                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $HeaderStyle =
            [
                'font' => ['bold' => false,'size'=>14, 'color' => ['rgb' => '1b6d85'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'eeeeef']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],

                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                ],
            ];

        $ContentStyle =
            [
                'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'],'name'=>"Tahoma"],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                ],
            ];

        $SummaryStyle =
            [
                'font' => ['bold' => true,'size'=>18, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '806201']
                ],

                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        //topic
        $sheet->mergeCells('A1:C1');
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "تخصیص تجهیزات");

        $sheet->mergeCells('A2:C2');
        $sheet->getRowDimension('2')->setRowHeight(30);
        $sheet->getStyle('A2')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A2', $project);

        $sheet->mergeCells('A3:C3');
        $sheet->getRowDimension('3')->setRowHeight(30);
        $sheet->getStyle('A3')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A3', \app\components\Jdf::jdate('Y/m/d', time()));

        if($area > 1)
        {
            //header
            $row = 4;
            $sheet->getRowDimension($row)->setRowHeight(40);

            $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('A'.$row, 'منطقه');
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('B'.$row, 'تجهیز');
            $sheet->getColumnDimension('B')->setWidth(50);
            $sheet->getStyle('C'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('C'.$row, 'توضیحات');
            $sheet->getColumnDimension('C')->setWidth(50);
            $sheet->getStyle('D'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('D'.$row, 'تخصیص یافته');
            $sheet->getColumnDimension('D')->setWidth(40);
            $sheet->getStyle('E'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('E'.$row, 'ثبت شده در سامانه');
            $sheet->getColumnDimension('E')->setWidth(50);

            $row++;
            $ded = 0;$used = 0;
            foreach ($models as $id=>$item)
            {
                $ded = $ded + $item['area'.$area][0];
                $used = $used + $item['area'.$area][1];
                $sheet->getRowDimension($row)->setRowHeight(40);
                $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray($ContentStyle);
                $sheet->setCellValue('A'.$row, $area);
                $sheet->setCellValue('B'.$row, $item['equipment']);
                $sheet->setCellValue('C'.$row, $item['description']);
                $sheet->setCellValue('D'.$row, $item['area'.$area][0]);
                $sheet->setCellValue('E'.$row, $item['area'.$area][1]);
                $row++;
            }

            $sheet->getRowDimension($row)->setRowHeight(50);
            $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray($SummaryStyle);
            $sheet->mergeCells('A'.$row.':C'.$row);
            $sheet->setCellValue('A'.$row, "مجموع");
            $sheet->setCellValue('D'.$row, $ded);
            $sheet->setCellValue('E'.$row, $used);

        }
        else
        {
            //header
            $row = 4;
            $sheet->getRowDimension($row)->setRowHeight(40);

            $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('A'.$row, 'تجهیز');
            $sheet->getColumnDimension('A')->setWidth(50);
            $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('B'.$row, 'توضیحات');
            $sheet->getColumnDimension('B')->setWidth(50);
            $sheet->getStyle('C'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('C'.$row, 'تعداد کل');
            $sheet->getColumnDimension('C')->setWidth(50);
            $sheet->getStyle('D'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('D'.$row, 'تعداد باقیمانده');
            $sheet->getColumnDimension('D')->setWidth(50);
            $sheet->getStyle('E'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('E'.$row, 'منطقه ۲');
            $sheet->getColumnDimension('E')->setWidth(50);
            $sheet->getStyle('F'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('F'.$row, 'منطقه ۳');
            $sheet->getColumnDimension('F')->setWidth(50);
            $sheet->getStyle('G'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('G'.$row, 'منطقه ۴');
            $sheet->getColumnDimension('G')->setWidth(50);
            $sheet->getStyle('H'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('H'.$row, 'منطقه ۵');
            $sheet->getColumnDimension('H')->setWidth(50);
            $sheet->getStyle('I'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('I'.$row, 'منطقه ۶');
            $sheet->getColumnDimension('I')->setWidth(50);
            $sheet->getStyle('J'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('J'.$row, 'منطقه ۷');
            $sheet->getColumnDimension('J')->setWidth(50);
            $sheet->getStyle('K'.$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue('K'.$row, 'منطقه ۸');
            $sheet->getColumnDimension('K')->setWidth(50);

            $ded = 0; $used =0;
            $ded2 =0; $used2=0;
            $ded3 =0; $used3=0;
            $ded4 =0; $used4=0;
            $ded5 =0; $used5=0;
            $ded6 =0; $used6=0;
            $ded7 =0; $used7=0;
            $ded8 =0; $used8=0;

            $row++;
            foreach ($models as $id=>$item)
            {
                $ded = $ded + $item['total'];
                $used = $used +$item['left'];

                $ded2 = $ded2 + $item['area2'][0]; $used2 = $used2 + $item['area2'][1];
                $ded3 = $ded3 + $item['area3'][0]; $used3 = $used3 + $item['area3'][1];
                $ded4 = $ded4 + $item['area4'][0]; $used4 = $used4 + $item['area4'][1];
                $ded5 = $ded5 + $item['area5'][0]; $used5 = $used5 + $item['area5'][1];
                $ded6 = $ded6 + $item['area6'][0]; $used6 = $used6 + $item['area6'][1];
                $ded7 = $ded7 + $item['area7'][0]; $used7 = $used7 + $item['area7'][1];
                $ded8 = $ded8 + $item['area8'][0]; $used8 = $used8 + $item['area8'][1];

                $sheet->getRowDimension($row)->setRowHeight(50);
                $sheet->getStyle('A'.$row.':K'.$row)->applyFromArray($ContentStyle);
                $sheet->setCellValue('A'.$row, $item['equipment']);
                $sheet->setCellValue('B'.$row, $item['description']);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->setCellValue('C'.$row, $item['total']);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->setCellValue('D'.$row, $item['left']);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->setCellValue('E'.$row, $item['area2'][1]. ' / '.$item['area2'][0]);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->setCellValue('F'.$row, $item['area3'][1]. ' / '.$item['area3'][0]);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->setCellValue('G'.$row, $item['area4'][1]. ' / '.$item['area4'][0]);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->setCellValue('H'.$row, $item['area5'][1]. ' / '.$item['area5'][0]);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->setCellValue('I'.$row, $item['area6'][1]. ' / '.$item['area6'][0]);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->setCellValue('J'.$row, $item['area7'][1]. ' / '.$item['area7'][0]);
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->setCellValue('K'.$row, $item['area8'][1]. ' / '.$item['area8'][0]);
                $row++;
            }

            $sheet->getRowDimension($row)->setRowHeight(50);
            $sheet->getStyle('A'.$row.':K'.$row)->applyFromArray($SummaryStyle);
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, "مجموع");
            $sheet->setCellValue('C'.$row, $ded);
            $sheet->setCellValue('D'.$row, $used);
            $sheet->setCellValue('E'.$row, $used2. ' / '.$ded2);
            $sheet->setCellValue('F'.$row, $used3. ' / '.$ded3);
            $sheet->setCellValue('G'.$row, $used4. ' / '.$ded4);
            $sheet->setCellValue('H'.$row, $used5. ' / '.$ded5);
            $sheet->setCellValue('I'.$row, $used6. ' / '.$ded6);
            $sheet->setCellValue('J'.$row, $used7. ' / '.$ded7);
            $sheet->setCellValue('K'.$row, $used8. ' / '.$ded8);

        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Report-LOM'.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    //detail stat
    // conditional report
    public function actionCond($id = -1)
    {
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $act = $post['act'];
            if($act == "export") return $this->export_cond($id, $post);
        }

        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();        $project_id = $id;
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            if (isset($userProjects[$project['id']]))
            {
                $ae = $userProjects[$project['id']];
                $area = $ae['area'];
                $exchange_id = $ae['exchange_id'];
                $areaSelectoin = [];

                $accessLevel=['level'=>-1, 'name'=>''];
                if(empty($ae['area']) && empty($ae['exchange_id']) )
                {
                    $accessLevel['level'] = 1;
                }
                else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                {
                    $accessLevel['level'] = 2;
                }
                else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                {
                    $accessLevel['level'] = 3;
                }

                $exchanges = [];
                $aex = [];// area, exchange_id
                if($accessLevel['level'] == 1)
                {
                    $areaSelection = [-1 => 'کل مناطق', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2])->orderBy('area, name')->asArray()->all();
                    $array = [-1=>[-1=>'کل مراکز'], 2=>[-1=>"کل مراکز"], 3=>[-1=>"کل مراکز"], 4=>[-1=>"کل مراکز"], 5=>[-1=>"کل مراکز"], 6=>[-1=>"کل مراکز"], 7=>[-1=>"کل مراکز"], 8=>[-1=>"کل مراکز"]];
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;
                    $aex = [-1, -1];
                }
                else if($accessLevel['level'] == 2)
                {
                    $areaSelection = [$ae['area']=>$ae['area']];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2, 'area'=>$ae['area']])->orderBy('area, name')->asArray()->all();

                    $array = [$ae['area']=>[-1=>'کل مراکز']];
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;
                    $aex = [$ae['area'], -1];
                }
                else if($accessLevel['level'] == 3)
                {
                    $areaSelection = [$ae['area']=>$ae['area']];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['project_id' => $project['id'], 'type' => 2, 'id'=>$ae['exchange_id']])->orderBy('area, name')->asArray()->all();

                    $array = [];
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                        $aex = [$ae['area'], $exch['id']];
                    }
                    $exchanges = $array;

                }

                if(empty($area)) $area = -1;
                if(empty($exchange_id)) $exchange_id = -1;

                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>1])->orderBy('priority')->asArray()->all();
                $choices = \app\models\PcViewChoices::find()->select('id,op_id,choice')->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($choices as $ch)
                {
                    $array[$ch['op_id']][-1] = "";
                    $array[$ch['op_id']][$ch['id']] = $ch['choice'];
                }
                $choices = $array;

                $t = getdate();
                $d = $t['mday']*1;
                $y = $t['year']*1;
                $m = $t['mon']*1 - 1;
                $from = $y.'/'.$m.'/'.$d;
                $m++;
                $to = $y.'/'.$m.'/'.$d;

                $search = ['area'=>-1, 'exchange_id'=>-1,'phase'=>-1, 'report'=>1, 'from'=>$from, 'to'=>$to];
                if($accessLevel['level'] == 2) $search['area'] = $area;
                if($accessLevel['level'] == 3){ $search['area'] = $area; $search['exchange_id'] = $exchange_id; }
                foreach($operations as $op) $search[$op['id']] = -1;

                if(Yii::$app->request->isPost)
                {
                    $post = Yii::$app->request->post();

                    if(isset($post['search']))
                    {
                        $search['area'] = $post['search']['area'];
                        $search['exchange_id'] = $post['search']['exchange_id'];
                        $search['phase'] = $post['search']['phaseNo'];
                        $search['report'] = $post['search']['repType'];
                        if($accessLevel['level'] == 2) $search['area'] = $area;
                        if($accessLevel['level'] == 3){ $search['area'] = $area; $search['exchange_id'] = $exchange_id; }
                        $search['from'] = $this->dateToGregorian($post['search']['from-mod']);
                        $search['to'] = $this->dateToGregorian($post['search']['to-mod']);
                        foreach($operations as $op)
                        {
                            if(isset($post['search'][$op['id']]))
                            {
                                $search[$op['id']] = $post['search'][$op['id']];
                            }
                        }
                    }
                    $tableInfo = $this->getCondTableInfo($id, $post);
                    $opMap = [];
                    $choiceMap = [];
                    $colMap = [];
                    $ops = \app\models\PcOperations::find()->where(['project_id'=>$project_id])->orderBy('priority')->asArray()->all();
                    $chs = \app\models\PcViewChoices::find()->where(['project_id'=>$project_id])->asArray()->all();
                    foreach($ops as $op)
                        $opMap[$op['id']] = ['title'=>$op['operation'], 'type_id'=>$op['type_id']];

                    foreach($chs as $ch)
                        $choiceMap[$ch['id']] = $ch['choice'];

                    $col = 11;
                    foreach($ops as $op)
                    {
                        $colMap[$col] = ['title'=>$op['operation'], 'id'=>$op['id']];
                        $col++;
                    }

                    return $this->render('cond', ['exchanges'=>$exchanges, 'project'=>$project, 'projects'=>$projects, 'areaSelection'=>$areaSelection, 'operations'=>$operations, 'choices'=>$choices, 'search'=>$search, 'tableInfo'=>$tableInfo, 'opMap'=>$opMap, 'choiceMap'=>$choiceMap, 'colMap'=>$colMap]);
                }
                else
                    return $this->render('cond', ['exchanges'=>$exchanges, 'project'=>$project, 'projects'=>$projects, 'areaSelection'=>$areaSelection, 'operations'=>$operations, 'choices'=>$choices, 'search'=>$search, 'tableInfo'=>[]]);

            }

        }

        return $this->render('cond', ['exchanges'=>[], 'project'=>[], 'projects'=>$projects, 'areaSelection'=>[], 'operations'=>[], 'choices'=>[], 'search'=>[], 'tableInfo'=>[]]);
    }

    private function dateToGregorian($jalali) // 1400/02/01
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $num = range(0, 9);
        $jalali = str_replace($persian, $num, $jalali);
        $array = explode("/",$jalali);
        $y = $array[0]*1;
        $m = $array[1]*1;
        $d = $array[2]*1;
        $date = \app\components\Jdf::jalali_to_gregorian($y,$m,$d);
        $date = $date[0].'/'.$date[1].'/'.$date[2];
        return $date;
    }

    public function jalaliToUnix($time_string, $end = false)
    { //۱۳۹۹/۱۰/۱
        $time_string = str_replace(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $time_string);
        $ymd = explode('/', $time_string);
        $year = (integer)$ymd[0];
        $month = (integer)$ymd[1];
        $day = (integer)$ymd[2];

        $ymd = \app\components\Jdf::jalali_to_gregorian($year, $month, $day);
        $year = (integer)$ymd[0];
        $month = (integer)$ymd[1];
        $day = (integer)$ymd[2];

        if($end)
            $ts = mktime(23, 59,59,$month, $day,$year);
        else
            $ts = mktime(0, 1,1,$month, $day,$year);

        return $ts;
    }

    private function getCondTableInfo($project_id, $post)
    {
        set_time_limit(1000);// set max exec time
        $tableInfo = [];
        if($project_id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            if (isset($userProjects[$project['id']])) {
                
                $ae = $userProjects[$project['id']];
                $area = $ae['area'];
                $exchange_id = $ae['exchange_id'];
                
                $accessLevel=['level'=>-1, 'name'=>''];
                if(empty($ae['area']) && empty($ae['exchange_id']) )
                {
                    $accessLevel['level'] = 1;
                }
                else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                {
                    $accessLevel['level'] = 2;
                }
                else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                {
                    $accessLevel['level'] = 3;
                } 
                
                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id']])->orderBy('priority')->asArray()->all();
                $choices = \app\models\PcViewChoices::find()->select('id,choice')->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($choices as $ch)
                {
                    $array[$ch['id']] = $ch['choice'];
                }
                $choices = $array;
                
                
                if(empty($area)) $area = -1;
                if(empty($exchange_id)) $exchange_id = -1;
                
                $searchParams = ['area'=>$area, 'exchange_id'=>$exchange_id, 'phaseNo'=>-1];
                $params = $post;
                $repType = 1;// horizontally report
                $fromMod ="";
                $toMod = "";
                $modString = "";
                if(isset($params['search']))
                {
                    $searchParams['area'] = $params['search']['area'];
                    if(isset($params['search']['exchange_id']))
                        $searchParams['exchange_id'] = $params['search']['exchange_id'];
                    $searchParams['phaseNo'] = $params['search']['phaseNo'];
                    $repType = $params['search']['repType'];
                    $fromMod = $params['search']['from-mod'];
                    $toMod = $params['search']['to-mod'];
                    $fromFlag = (integer)$params['search']['from-flag'];
                    $toFlag = (integer)$params['search']['to-flag'];
                    if(!$fromFlag) $fromMod = "";
                    if(!$toFlag) $toMod = "";
                    if( (!empty($fromMod)) || (!empty($toMod)) )
                        $modString = $fromMod." ~ ".$toMod;
                    $searchParams['fromMod'] = $fromMod;
                    $searchParams['toMod'] = $toMod;
                    
                    foreach ($operations as $op)
                    {
                        if(isset($params['search'][$op['id']]))
                        {
                            if(!empty($params['search'][$op['id']]))
                                $searchParams[$op['id']] = $params['search'][$op['id']];
                        }
                    }
                }
                if(empty($searchParams['area'])) $searchParams['area'] = -1;
                if(empty($searchParams['exchange_id'])) $searchParams['exchange_id'] = -1;
                
                //###################################
                $phaseNo = $searchParams['phaseNo'];
                $cond = [];
                if($phaseNo > -1)
                    $cond['phase'] = (integer)$phaseNo;
                $cond['project_id'] = $project['id'];
                if($searchParams['area'] > -1)
                    $cond['area'] = (integer)$searchParams['area'];
                $exCond = [];
                if($searchParams['exchange_id'] > -1)
                    $exCond = ['or', ['exchange_id'=>(integer)$searchParams['exchange_id']], ['center_id'=>(integer)$searchParams['exchange_id']]];
                
                //time
                $modCond = [];
                if( (!empty($fromMod)) && (!empty($toMod)) )
                {
                    $fromMod = $this->jalaliToUnix($fromMod);
                    $toMod = $this->jalaliToUnix($toMod, true);
                    $modCond = ['AND' ,['>', 'modified_ts', $fromMod], ['<', 'modified_ts', $toMod] ];
                }
                else if(!empty($fromMod))//from mod time
                {
                    $fromMod = $this->jalaliToUnix($fromMod);
                    $modCond = ['>', 'modified_ts', $fromMod];
                }
                else if(!empty($toMod))//to mod time
                {
                    $toMod = $this->jalaliToUnix($toMod, true);
                    $modCond = ['<', 'modified_ts', $toMod];
                } 
                
                //select  exchanges cascade 1
                $exchanges = \app\models\PcViewRecords::find()->select('exchange_id')->where($cond)->andWhere($exCond)->andWhere($modCond);
                
                foreach ($operations as $op)
                {
                    if(isset($searchParams[$op['id']]))
                    {
                        $ov = $searchParams[$op['id']];
                        if( $ov > -1)
                        {
                            $cond = ['op_id'=>$op['id'], 'op_value'=>(string)$ov ];
                            //select exchanges cascade 3
                            $exchanges = \app\models\PcViewRecords::find()->select('exchange_id')->where(['exchange_id'=>$exchanges])->andWhere($cond);
                        }
                    }
                }
                
                
                //select records
                $records = \app\models\PcViewRecords::find()->where(['exchange_id'=>$exchanges])->orderBy('area, center_name, name', 'priority')->asArray()->all();
                
                $array = []; // [ [area=>2, x=>, ....], []... ]
                $ex1 = -1;
                foreach ($records as $rec)
                {
                    $ex2 = $rec['exchange_id'];
                    if($ex1 != $ex2)
                    {
                        $ex1 = $ex2;
                        $array[$rec['exchange_id']]['area'] = $rec['area'];
                        $array[$rec['exchange_id']]['center_name'] = $rec['center_name'];
                        $array[$rec['exchange_id']]['name'] = $rec['name'];
                        $array[$rec['exchange_id']]['site_id'] = $rec['site_id'];
                        $array[$rec['exchange_id']]['kv_code'] = $rec['kv_code'];
                        $array[$rec['exchange_id']]['address'] = $rec['address'];
                        $array[$rec['exchange_id']]['position'] = $rec['position'];
                        $array[$rec['exchange_id']]['phase'] = $rec['phase'];
                        $array[$rec['exchange_id']]['modified_ts'] = \app\components\Jdf::jdate('Y/m/d', $rec['modified_ts']);
                        $array[$rec['exchange_id']]['register_ts'] = \app\components\Jdf::jdate('Y/m/d', $rec['register_ts']);
                        $array[$rec['exchange_id']]['percentage'] = ($rec['project_weight'] > 0)? round($rec['weight']*100/$rec['project_weight'], 1) : 0;
                        $array[$rec['exchange_id']]['weight'] = $rec['weight'];
                        $array[$rec['exchange_id']]['project_weight'] = $rec['project_weight'];
                    }
                    
                    $array[$rec['exchange_id']][$rec['op_id']] = $rec['op_value'];
                }
                $tableInfo = $array;
            }
        }
        
        return $tableInfo;
    }

    private function export_cond($id, $post)
    {
        set_time_limit(1000);// set max exec time
        $project_id = $id;
        if($id > -1) {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            if (isset($userProjects[$project['id']])) {

                $ae = $userProjects[$project['id']];
                $area = $ae['area'];
                $exchange_id = $ae['exchange_id'];

                $accessLevel=['level'=>-1, 'name'=>''];
                if(empty($ae['area']) && empty($ae['exchange_id']) )
                {
                    $accessLevel['level'] = 1;
                }
                else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                {
                    $accessLevel['level'] = 2;
                }
                else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                {
                    $accessLevel['level'] = 3;
                }

                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id']])->orderBy('priority')->asArray()->all();
                $choices = \app\models\PcViewChoices::find()->select('id,choice')->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($choices as $ch)
                {
                    $array[$ch['id']] = $ch['choice'];
                }
                $choices = $array;


                if(empty($area)) $area = -1;
                if(empty($exchange_id)) $exchange_id = -1;

                $searchParams = ['area'=>$area, 'exchange_id'=>$exchange_id, 'phaseNo'=>-1];
                $params = $post;
                $repType = 1;// horizontally report
                $fromMod ="";
                $toMod = "";
                $modString = "";
                if(isset($params['search']))
                {
                    $searchParams['area'] = $params['search']['area'];
                    if(isset($params['search']['exchange_id']))
                        $searchParams['exchange_id'] = $params['search']['exchange_id'];
                    $searchParams['phaseNo'] = $params['search']['phaseNo'];
                    $repType = $params['search']['repType'];
                    $fromMod = $params['search']['from-mod'];
                    $toMod = $params['search']['to-mod'];
                    $fromFlag = (integer)$params['search']['from-flag'];
                    $toFlag = (integer)$params['search']['to-flag'];
                    if(!$fromFlag) $fromMod = "";
                    if(!$toFlag) $toMod = "";
                    if( (!empty($fromMod)) || (!empty($toMod)) )
                        $modString = $fromMod." ~ ".$toMod;
                    $searchParams['fromMod'] = $fromMod;
                    $searchParams['toMod'] = $toMod;

                    foreach ($operations as $op)
                    {
                        if(isset($params['search'][$op['id']]))
                        {
                            if(!empty($params['search'][$op['id']]))
                                $searchParams[$op['id']] = $params['search'][$op['id']];
                        }
                    }
                }
                if(empty($searchParams['area'])) $searchParams['area'] = -1;
                if(empty($searchParams['exchange_id'])) $searchParams['exchange_id'] = -1;

                //###################################
                $phaseNo = $searchParams['phaseNo'];
                $cond = [];
                if($phaseNo > -1)
                    $cond['phase'] = (integer)$phaseNo;
                $cond['project_id'] = $project['id'];
                if($searchParams['area'] > -1)
                    $cond['area'] = (integer)$searchParams['area'];
                $exCond = [];
                if($searchParams['exchange_id'] > -1)
                    $exCond = ['or', ['exchange_id'=>(integer)$searchParams['exchange_id']], ['center_id'=>(integer)$searchParams['exchange_id']]];

                //time
                $modCond = [];
                if( (!empty($fromMod)) && (!empty($toMod)) )
                {
                    $fromMod = $this->jalaliToUnix($fromMod);
                    $toMod = $this->jalaliToUnix($toMod, true);
                    $modCond = ['AND' ,['>', 'modified_ts', $fromMod], ['<', 'modified_ts', $toMod] ];
                }
                else if(!empty($fromMod))//from mod time
                {
                    $fromMod = $this->jalaliToUnix($fromMod);
                    $modCond = ['>', 'modified_ts', $fromMod];
                }
                else if(!empty($toMod))//to mod time
                {
                    $toMod = $this->jalaliToUnix($toMod, true);
                    $modCond = ['<', 'modified_ts', $toMod];
                }

                //select exchanges cascade 1
                $exchanges = \app\models\PcViewRecords::find()->select('exchange_id')->where($cond)->andWhere($exCond)->andWhere($modCond);

                foreach ($operations as $op)
                {
                    if(isset($searchParams[$op['id']]))
                    {
                        $ov = $searchParams[$op['id']];
                        if( $ov > -1)
                        {
                            $cond = ['op_id'=>$op['id'], 'op_value'=>(string)$ov ];
                            //select exchanges cascade 3
                            $exchanges = \app\models\PcViewRecords::find()->select('exchange_id')->where(['exchange_id'=>$exchanges])->andWhere($cond);
                        }
                    }
                }


                //select records
                $records = \app\models\PcViewRecords::find()->where(['exchange_id'=>$exchanges])->orderBy('area, center_name, name', 'priority')->asArray()->all();

                $array = []; // [ [area=>2, x=>, ....], []... ]
                $ex1 = -1;
                foreach ($records as $rec)
                {
                    $ex2 = $rec['exchange_id'];
                    if($ex1 != $ex2)
                    {
                        $ex1 = $ex2;
                        $array[$rec['exchange_id']]['area'] = $rec['area'];
                        $array[$rec['exchange_id']]['center_name'] = $rec['center_name'];
                        $array[$rec['exchange_id']]['name'] = $rec['name'];
                        $array[$rec['exchange_id']]['site_id'] = $rec['site_id'];
                        $array[$rec['exchange_id']]['kv_code'] = $rec['kv_code'];
                        $array[$rec['exchange_id']]['address'] = $rec['address'];
                        $array[$rec['exchange_id']]['position'] = $rec['position'];
                        $array[$rec['exchange_id']]['phase'] = $rec['phase'];
                        $array[$rec['exchange_id']]['modified_ts'] = \app\components\Jdf::jdate('Y/m/d', $rec['modified_ts']);
                        $array[$rec['exchange_id']]['register_ts'] = \app\components\Jdf::jdate('Y/m/d', $rec['register_ts']);
                        $array[$rec['exchange_id']]['percentage'] = ($rec['project_weight'] > 0)? round($rec['weight']*100/$rec['project_weight'], 1) : 0;
                        $array[$rec['exchange_id']]['weight'] = $rec['weight'];
                        $array[$rec['exchange_id']]['project_weight'] = $rec['project_weight'];
                    }

                    $array[$rec['exchange_id']][$rec['op_id']] = $rec['op_value'];
                }
                $records = $array;

                $queryParams = [];
                if($searchParams['area'] > 0) $queryParams['area'] = $searchParams['area'];
                if($searchParams['phaseNo'] > -1)
                {
                    $queryParams['phase'] = $searchParams['phaseNo'];
                }
                if((!empty($searchParams['fromMod'])) || (!empty($searchParams['toMod']) ))
                {
                    $queryParams['modified_ts'] = $modString;
                }

                if($searchParams['exchange_id'] > 0) $queryParams['center_name'] = \app\models\PcExchanges::find()->select('name')->where(['id'=>$searchParams['exchange_id']])->scalar();

                if( ((integer)$fromMod > 0) || ((integer)$toMod > 0) )
                {
                    $opId = \app\models\PcOperations::find()->select('id')->where(['project_id'=>$project['id'], 'type_id'=>4])->andWhere(['like', 'operation', 'ویرایش'])->scalar();
                    $queryParams[$opId] = $modString;
                }


                foreach ($operations as $op)
                {
                    if(isset($searchParams[$op['id']]))
                    {
                        if($searchParams[$op['id']] > -1)
                            $queryParams[$op['id']] = $searchParams[$op['id']];
                    }
                }

                if( ((integer)$fromMod > 0) || ((integer)$toMod > 0) )
                {
                    $opId = \app\models\PcOperations::find()->select('id')->where(['project_id'=>$project['id'], 'type_id'=>4])->andWhere(['like', 'operation', 'ویرایش'])->scalar();
                    $queryParams[$opId] = $modString;
                }

                if($repType == 1)
                    return $this->exportHorizontally($records,$operations, $choices, $project, $queryParams);
                else
                    return $this->exportVertically($records,$operations, $choices, $project, $queryParams);

            }
        }

        return $this->redirect(['stat/cond']);
    }

    //  report methods
    public function exportHorizontally($records, $operations, $choices, $project, $queryParams)
    {
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>20, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1b6d85']
                ],

                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $HeaderStyle =
            [
                'font' => ['bold' => false,'size'=>14, 'color' => ['rgb' => '1b6d85'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'eeeeee']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],

                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $ContentStyle =
            [
                'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'],'name'=>"Tahoma"],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,],
                ],
            ];

        $SearchStyle =
            [
                'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'],'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'e6f2ff']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                ],
            ];


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $columnCount = 12 + sizeof($operations);
        $maxColumn = Cell\Coordinate::stringFromColumnIndex($columnCount);
        $maxRows = sizeof($records)+7;
        //topic
        $sheet->mergeCells('A1:F1');
        $sheet->getRowDimension('1')->setRowHeight(50);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "P D C P");
        //topic
        $sheet->mergeCells('A2:F2');
        $sheet->getRowDimension('2')->setRowHeight(50);
        $sheet->getStyle('A2')->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A2', $project['project']);
        //date
        $sheet->mergeCells('A3:F3');
        $sheet->getRowDimension('3')->setRowHeight(40);
        $sheet->getStyle('A3')->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A3', \app\components\Jdf::jdate('Y/m/d', time()));
        //date
        $sheet->mergeCells('A4:F4');
        $sheet->getRowDimension('4')->setRowHeight(40);
        $sheet->getStyle('A4')->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A4', " تعداد کل ".sizeof($records));

        //assign operation column
        $opCol =[]; // op_id=>col
        $i = 13;
        foreach ($operations as $op)
        {
            $opCol[$op['id']] = Cell\Coordinate::stringFromColumnIndex($i);
            $i++;
        }

        $row = 5;

        if(!empty($queryParams))
        {
            $ss =
                [
                    'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'],'name'=>"Tahoma"],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'e6f2ff']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                        'wrapText'=>true
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                        'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                        'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    ],
                ];

            $sheet->getStyle('A5:'.$maxColumn.'5')->applyFromArray($ss);
            $sheet->mergeCells('A5:'.$maxColumn.'5');
            $sheet->getRowDimension(5)->setRowHeight(50);
            $filterParam = "";
            if(isset($queryParams['area'])) $filterParam =  'منطقه: '.$queryParams['area'];
            if(isset($queryParams['center_name'])) $filterParam = $filterParam.  '# مرکز: '.$queryParams['center_name'];
            if(isset($queryParams['name'])) $filterParam = $filterParam.  '# نام: '.$queryParams['name'];
            if(isset($queryParams['site_id'])) $filterParam = $filterParam.  '# شناسه سایت: '.$queryParams['site_id'];
            if(isset($queryParams['kv_code'])) $filterParam = $filterParam.  '# کد کافو: '.$queryParams['kv_code'];
            if(isset($queryParams['address'])) $filterParam = $filterParam.  '# آدرس: '.$queryParams['address'];
            if(isset($queryParams['position'])) $filterParam = $filterParam.  '# موقعیت: '.$queryParams['position'];
            if(isset($queryParams['phase'])) $filterParam = $filterParam.  '# فاز: '.$queryParams['phase'];
            if(isset($queryParams['modified_ts'])) $filterParam = $filterParam.  '# ویرایش: '.$queryParams['modified_ts'];
            foreach ($operations as $op)
            {
                if(isset($queryParams[$op['id']]))
                {
                    $val = $queryParams[$op['id']];
                    $type = $op['type_id'];
                    if($type == 1) $val = $choices[$val];
                    if($type == 4) if(!empty($val)) $val = \app\components\Jdf::jdate('Y/m/d', $val*1);
                    $filterParam = $filterParam.  '#'.$op['operation'].': '.$val;
                }
            }

            $sheet->setCellValue('A5', $filterParam);

            $row++;
        }



        //header
        $sheet->getRowDimension($row)->setRowHeight(40);

        $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A'.$row, 'منطقه');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('B'.$row, 'مرکز');
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getStyle('C'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('C'.$row, 'نام');
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getStyle('D'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('D'.$row, 'شناسه سایت');
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getStyle('E'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('E'.$row, 'کد کافو');
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getStyle('F'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('F'.$row, 'آدرس');
        $sheet->getColumnDimension('F')->setWidth(40);
        $sheet->getStyle('G'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('G'.$row, 'موقعیت');
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getStyle('H'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('H'.$row, 'فاز');
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getStyle('I'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('I'.$row, 'زمان آخرین ویرایش');
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getStyle('J'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('J'.$row, 'درصد پیشرفت');
        $sheet->getColumnDimension('J')->setWidth(30);
        $sheet->getStyle('K'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('K'.$row, 'ضریب پیشرفت');
        $sheet->getColumnDimension('K')->setWidth(30);
        $sheet->getStyle('L'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('L'.$row, 'ضریب کل');
        $sheet->getColumnDimension('L')->setWidth(30);
        
        foreach ($operations as $op)
        {
            $sheet->getStyle($opCol[$op['id']].$row)->applyFromArray($HeaderStyle);
            $sheet->setCellValue($opCol[$op['id']].$row, $op['operation']);
            $sheet->getColumnDimension($opCol[$op['id']])->setWidth(25);
        }
            
        $row++;
        if(!empty($queryParams))
        {
            //filter row row 5
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row.':'.$maxColumn.$row)->applyFromArray($SearchStyle);
            
            if(isset($queryParams['area']))
            {
                $sheet->setCellValue('A'.$row, $queryParams['area']);
                $sheet->getStyle('A'.$row.':A'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['center_name']))
            {
                $sheet->setCellValue('B'.$row, $queryParams['center_name']);
                $sheet->getStyle('B'.$row.':B'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['name']))
            {
                $sheet->setCellValue('C'.$row, $queryParams['name']);
                $sheet->getStyle('C'.$row.':C'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['site_id']))
            {
                $sheet->setCellValue('D'.$row, $queryParams['site_id']);
                $sheet->getStyle('D'.$row.':D'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['kv_code']))
            {
                $sheet->setCellValue('E'.$row, $queryParams['kv_code']);
                $sheet->getStyle('E'.$row.':E'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['address']))
            {
                $sheet->setCellValue('F'.$row, $queryParams['address']);
                $sheet->getStyle('F'.$row.':F'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['position']))
            {
                $sheet->setCellValue('G'.$row, $queryParams['position']);
                $sheet->getStyle('G'.$row.':G'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['phase']))
            {
                $sheet->setCellValue('H'.$row, $queryParams['phase']);
                $sheet->getStyle('H'.$row.':H'.$maxRows)->applyFromArray($SearchStyle);
            }
            if(isset($queryParams['modified_ts']))
            {
                $sheet->setCellValue('I'.$row, $queryParams['modified_ts']);
                $sheet->getStyle('I'.$row.':I'.$maxRows)->applyFromArray($SearchStyle);
            }
            
            foreach ($operations as $op)
            {
                if(isset($queryParams[$op['id']]))
                {
                    $val = $queryParams[$op['id']];
                    $type = $op['type_id'];
                    if($type == 1){$val = (integer)$val; $val = $choices[$val];}
                    //if($type == 4) if($val > 0) $val = \app\components\Jdf::jdate('Y/m/d', (integer)$val); else $val = "";
                    $sheet->getStyle($opCol[$op['id']].$row.':'.$opCol[$op['id']].$maxRows)->applyFromArray($SearchStyle);
                    $sheet->setCellValue($opCol[$op['id']].$row, $val);
                }
            }
            
            $row++;
        }
        
        
        foreach ($records as $id=>$rec)
        {
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row.':'.$maxColumn.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('A'.$row, $rec['area']);
            $sheet->setCellValue('B'.$row, $rec['center_name']);
            $sheet->setCellValue('C'.$row, $rec['name']);
            $sheet->setCellValue('D'.$row, $rec['site_id']);
            $sheet->setCellValue('E'.$row, $rec['kv_code']);
            $sheet->setCellValue('F'.$row, $rec['address']);
            $sheet->setCellValue('G'.$row, $rec['position']);
            $sheet->setCellValue('H'.$row, $rec['phase']);
            $sheet->setCellValue('I'.$row, $rec['modified_ts']);
            $sheet->setCellValue('J'.$row, $rec['percentage'].'%');
            $sheet->setCellValue('K'.$row, $rec['weight']);
            $sheet->setCellValue('L'.$row, $rec['project_weight']);
            
            foreach ($operations as $op)
            {
                if(isset($rec[$op['id']]))
                {
                    $val = $rec[$op['id']];
                    $type = $op['type_id'];
                    if($type == 1) $val = $choices[$val];
                    if($type == 4) $val = \app\components\Jdf::jdate('Y/m/d', $val*1);
                    $sheet->setCellValue($opCol[$op['id']].$row, $val);
                }
            }
            $row++;
        }
        
        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Report'.$project['project'].'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    public function exportVertically($records, $operations, $choices, $project, $queryParams)
    {
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>20, 'color' => ['rgb' => 'ffffff'], 'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1b6d85']
                ],

                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $HeaderStyle =
            [
                'font' => ['bold' => false,'size'=>14, 'color' => ['rgb' => '1b6d85'], 'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'eeeeee']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],

                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,],
                ],
            ];

        $TitleStyle =
            [
                'font' => ['bold' => true,'size'=>12, 'color' => ['rgb' => '000088'], 'name'=>"Tahoma"],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
            ];

        $ContentStyle =
            [
                'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'], 'name'=>"Tahoma"],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText'=>true,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                ],
            ];

        $SearchStyle =
            [
                'font' => ['bold' => false,'size'=>12, 'color' => ['rgb' => '000055'], 'name'=>"Tahoma"],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'e6f2ff']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'readOrder' => \PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL,
                    'wrapText'=>true
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,],
                ],
            ];


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(50);

        //topic
        $sheet->mergeCells('A1:B1');
        $sheet->getRowDimension('1')->setRowHeight(50);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "P D C P");
        //topic
        $sheet->mergeCells('A2:B2');
        $sheet->getRowDimension('2')->setRowHeight(50);
        $sheet->getStyle('A2')->applyFromArray($ContentStyle);
        $sheet->setCellValue('A2', $project['project']);
        //date
        $sheet->mergeCells('A3:B3');
        $sheet->getRowDimension('3')->setRowHeight(40);
        $sheet->getStyle('A3')->applyFromArray($ContentStyle);
        $sheet->setCellValue('A3', \app\components\Jdf::jdate('Y/m/d', time()));
        // count
        $sheet->mergeCells('A4:B4');
        $sheet->getRowDimension('4')->setRowHeight(40);
        $sheet->getStyle('A4')->applyFromArray($ContentStyle);
        $sheet->setCellValue('A4', " تعداد کل ".sizeof($records));

        if(empty($queryParams))
        {
            $row = 5;
            //header
            $sheet->getRowDimension('5')->setRowHeight(40);
            $sheet->getStyle('A5')->applyFromArray($HeaderStyle);
            $sheet->setCellValue('A5', 'فعالیت');
            $sheet->getStyle('B5')->applyFromArray($HeaderStyle);
            $sheet->setCellValue('B5', 'نتیجه فعالیت');
        }
        else
        {
            $row =7;
            //filter row row 5
            $sheet->mergeCells('A5:B5');
            $sheet->getRowDimension(5)->setRowHeight(50);
            $sheet->getStyle('A5')->applyFromArray($SearchStyle);
            $filterParam = "";
            if(isset($queryParams['area'])) $filterParam =  'منطقه: '.$queryParams['area'];
            if(isset($queryParams['center_name'])) $filterParam = $filterParam.  '# مرکز: '.$queryParams['center_name'];
            if(isset($queryParams['name'])) $filterParam = $filterParam.  '# نام: '.$queryParams['name'];
            if(isset($queryParams['site_id'])) $filterParam = $filterParam.  '# شناسه سایت: '.$queryParams['site_id'];
            if(isset($queryParams['kv_code'])) $filterParam = $filterParam.  '# کد کافو: '.$queryParams['kv_code'];
            if(isset($queryParams['address'])) $filterParam = $filterParam.  '# آدرس: '.$queryParams['address'];
            if(isset($queryParams['position'])) $filterParam = $filterParam.  '# موقعیت: '.$queryParams['position'];
            if(isset($queryParams['phase'])) $filterParam = $filterParam.  '# فاز: '.$queryParams['phase'];
            if(isset($queryParams['modified_ts'])) $filterParam = $filterParam.  '# ویرایش: '.$queryParams['modified_ts'];
            foreach ($operations as $op)
            {
                if(isset($queryParams[$op['id']]))
                {
                    $val = $queryParams[$op['id']];
                    $type = $op['type_id'];
                    if($type == 1) $val = $choices[$val];
                    if($type == 4) if(!empty($val)) $val = \app\components\Jdf::jdate('Y/m/d', $val*1);
                    $filterParam = $filterParam.  '#'.$op['operation'].': '.$val;
                }
            }

            $sheet->setCellValue('A5', $filterParam);


            //header
            $sheet->getRowDimension('6')->setRowHeight(40);
            $sheet->getStyle('A6')->applyFromArray($HeaderStyle);
            $sheet->setCellValue('A6', 'فعالیت');
            $sheet->getStyle('B6')->applyFromArray($HeaderStyle);
            $sheet->setCellValue('B6', 'نتیجه فعالیت');
            
        }
        
        
        foreach ($records as $id=>$rec)
        {
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->getStyle('A'.$row)->applyFromArray($TopicStyle);
            $sheet->setCellValue('A'.$row, $rec['name']);
            
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'منطقه');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['area']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'مرکز');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['center_name']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'شناسه سایت');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['site_id']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'کد کافو');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['kv_code']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'آدرس');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['address']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'موقعیت');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['position']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'فاز');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['phase']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'زمان آخرین ویرایش');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['modified_ts']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'درصد پیشرفت');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['percentage'].'%');
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'ضریب پیشرفت');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['weight']);
            $row++;
            $sheet->getRowDimension($row)->setRowHeight(30);
            $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
            $sheet->setCellValue('A'.$row, 'ضریب کل');
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['project_weight']);
            $row++;
            
            foreach ($operations as $op)
            {
                if(isset($rec[$op['id']]))
                {
                    $val = $rec[$op['id']];
                    $type = $op['type_id'];
                    if($type == 1) $val = $choices[$val];
                    if($type == 4) $val = \app\components\Jdf::jdate('Y/m/d', $val*1);
                    $sheet->getStyle('A'.$row)->applyFromArray($TitleStyle);
                    $sheet->getRowDimension($row)->setRowHeight(30);
                    $sheet->setCellValue('A'.$row, $op['operation']);
                    $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
                    $sheet->setCellValue('B'.$row, $val);
                    $row++;
                }
            }
        }
        
        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Report'.$project['project'].'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }


}

