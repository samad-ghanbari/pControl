<?php

namespace app\controllers;

use phpDocumentor\Reflection\Types\Scalar;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;

class ProjectController extends \yii\web\Controller
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

    public function actionRegex()
    {
        return $this->render('regex');
    }

    public function actionIndex()
    {
        $id = -1;
        $areaSelection = [];

        $projects = \app\models\PcProjects::find()->where(['enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();


        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
//        $searchParams = new \app\models\PcExchanges();
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['id'])) //project_id
            $id = $qp['id'];
        if(isset($qp['search']))
            $searchParams = $qp['search'];

        if(isset($qp['page'])) $searchParams['page'] = (integer)$qp['page'];

        $condArea = [];
        $condPhase = [];
        if($searchParams['phaseNo'] > 0) $condPhase = ['phase'=>$searchParams['phaseNo']];
        if($searchParams['area'] > 0) $condArea = ['area'=>$searchParams['area']];

        if($searchParams['area'] == -1) $searchParams['area'] = '';


        $session = Yii::$app->session;
        $session->open();

        $user = '';
        $userId = -1;
        if(isset($session['user']))
        {
            $user = $session['user'];
            $userId = $user['id'];
        }
        if($id == -1)
        {
            if(isset($session['project']))
                $id = $session['project']['id'];
        }

        $userProject = \app\models\PcViewUserProjects::find()->where(['user_id'=>$userId, 'project_id'=>$id, 'project_enabled'=>true])->asArray()->one();

        if(!empty($userProject))
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();
            if(isset($session['project'])) $session->remove('project');
            $session['project'] = $project;
            if(isset($session['accessLevel'])) $session->remove('accessLevel');
            $accessLevel=['level'=>-1, 'name'=>''];
            $rw='';
            if($userProject['rw'] == 1) $rw = ' دسترسی ویرایش '; else $rw = ' دسترسی مشاهده ';
            if(empty($userProject['area']) && empty($userProject['exchange_id']) )
            {
                $accessLevel['level'] = 1;
                $accessLevel['name'] = 'دسترسی ادمین';
                $accessLevel['rw'] = $rw;
            }
            else if(($userProject['area'] > 0) && empty($userProject['exchange_id']))
            {
                $accessLevel['level'] = 2;
                $accessLevel['name'] = ' دسترسی سطح منطقه '.$userProject['area'];
                $accessLevel['rw'] = $rw;
            }
            else if(($userProject['area'] > 0) && ($userProject['exchange_id'] > 0))
            {
                $accessLevel['level'] = 3;
                $accessLevel['name'] = ' دسترسی سطح مرکز '.$userProject['exchange'];
                $accessLevel['rw'] = $rw;
            }

            $session['accessLevel'] = $accessLevel;
            $session['project'] = $project;

            $operations = \app\models\PcOperations::find()->where(['project_id'=>$id])->orderBy('priority')->asArray()->all();
            $choices = \app\models\PcViewChoices::find()->select('id,choice, choice_weight')->where(['project_id'=>$id])->asArray()->all();
            $array = [];
            foreach($choices as $ch)
            {
                $array[$ch['id']] = ['choice'=>$ch['choice'], 'weight'=>$ch['choice_weight']];
            }
            $choices = $array; // [id=>[choice, weight]  ,  ]
            $allExchanges = [];
            if($accessLevel['level'] == 1)
            {
                $areaSelection = [-1=>'کل مناطق', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];

                $allExchanges= \app\models\PcViewExchanges::find()->select('id')->where(['project_id'=>$id])
                    ->andWhere($condArea)->andWhere($condPhase)
                    ->andWhere(['or',['like', 'name', $searchParams['name']], ['like', 'center_name', $searchParams['name']]])
                    ->andWhere(['like', 'center_name', $searchParams['center_name']])
                    ->andWhere(['like', 'site_id', $searchParams['site_id']])
                    ->andWhere(['like', 'kv_code', $searchParams['kv_code']])
//                    ->andWhere(['like', 'address', $searchParams['address']])
                    ->orderBy("area,name");
            }
            else if($accessLevel['level'] == 2)
            {
                $areaSelection = [$userProject['area']=>$userProject['area']];

                $area = $userProject['area'];
                $allExchanges= \app\models\PcViewExchanges::find()->select('id')
                    ->where(['project_id'=>$id, 'area'=>$area])
                    ->andWhere($condPhase)
                    ->andWhere(['or',['like', 'name', $searchParams['name']], ['like', 'center_name', $searchParams['name']]])
                    ->andWhere(['like', 'center_name', $searchParams['center_name']])
                    ->andWhere(['like', 'site_id', $searchParams['site_id']])
                    ->andWhere(['like', 'kv_code', $searchParams['kv_code']])
//                    ->andWhere(['like', 'address', $searchParams['address']])
                    ->orderBy("area,name");
            }
            else if($accessLevel['level'] == 3)
            {
                $areaSelection = [$userProject['area']=>$userProject['area']];

                $exchange_id = $userProject['exchange_id'];

                $allExchanges= \app\models\PcViewExchanges::find()->select('id')
                    ->where(['project_id'=>$id])->andWhere(['or',['id'=>$exchange_id],['center_id'=>$exchange_id]])
                    ->andWhere($condPhase)
                    ->andWhere(['or',['like', 'name', $searchParams['name']], ['like', 'center_name', $searchParams['name']]])
                    ->andWhere(['like', 'center_name', $searchParams['center_name']])
                    ->andWhere(['like', 'site_id', $searchParams['site_id']])
                    ->andWhere(['like', 'kv_code', $searchParams['kv_code']])
//                    ->andWhere(['like', 'address', $searchParams['address']])
                    ->orderBy("area,name");
            }


            $totalCount = $allExchanges->count();
            $pages = new Pagination(['totalCount'=>$totalCount]);
            $pages->pageSize = 20;
            $exchanges = $allExchanges->offset($pages->offset)->limit($pages->limit);
            // lom
            $lom = []; // [exch=>[id, equip, quantity], ...  ]
            $lom_detail = \app\models\PcViewLomDetail::find()->where(['in', 'exchange_id', $exchanges])->orderBy('exchange_id')->asArray()->all();
            foreach ($lom_detail as $ld)
            {
                $lom[$ld['exchange_id']][$ld['id']] = ['equipment'=>$ld['equipment'], 'quantity'=>$ld['quantity'] ];
            }

            $records = \app\models\PcViewRecords::find()->where(['in', 'exchange_id', $exchanges])->orderBy('area,name, exchange_id, priority')->asArray()->all();

            //[ exch=>[op1=>val1, op2=>val2], ... ]
            $array = [];
            $exchId = -1;
            foreach ($records as $rec)
            {
                $exchId2 = $rec['exchange_id'];
                if($exchId2 != $exchId)
                {
                    $exchId = $exchId2;

                    $array[$rec['exchange_id']]['id'] = $exchId;
                    $array[$rec['exchange_id']]['area'] = $rec['area'];
                    $array[$rec['exchange_id']]['exchange'] = $rec['name'];
                    $array[$rec['exchange_id']]['extype'] = $rec['extype'];
                    $array[$rec['exchange_id']]['center_abbr'] = $rec['center_abbr'];
                    $array[$rec['exchange_id']]['center_name'] = $rec['center_name'];
                    $array[$rec['exchange_id']]['site_id'] = $rec['site_id'];
                    $array[$rec['exchange_id']]['kv_code'] = $rec['kv_code'];
                    $array[$rec['exchange_id']]['address'] = $rec['address'];
                    $array[$rec['exchange_id']]['position'] = $rec['position'];
                    $array[$rec['exchange_id']]['done'] = $rec['done'];
                    $array[$rec['exchange_id']]['modifier'] = $rec['modifier_name'].' '.$rec['modifier_lastname'];
                    $array[$rec['exchange_id']]['modified_ts'] = \app\components\Jdf::jdate('Y/m/d', $rec['modified_ts']);
                    $array[$rec['exchange_id']]['register_ts'] = \app\components\Jdf::jdate('Y/m/d', $rec['register_ts']);
                    $array[$rec['exchange_id']]['phase'] = $rec['phase'];
                    $array[$rec['exchange_id']]['project_weight'] = $rec['project_weight'];
                    $array[$rec['exchange_id']]['weight'] = $rec['weight'];
                    $array[$rec['exchange_id']]['bitstream'] = false;

                    //lom
                    if(isset($lom[$exchId]))
                        $array[$rec['exchange_id']]['lom'] = $lom[$exchId];
                    else
                        $array[$rec['exchange_id']]['lom'] = [];

                    foreach($operations as $op)
                    {
                        $array[$rec['exchange_id']][$op['operation']] = ['type'=>$op['type_id'], 'value'=>'', 'op_value'=>$rec['op_value']];
                    }
                }

                $value = $rec['op_value'];

                if($rec['op_type'] == 1)
                {
                    if(isset($choices[$value]['choice']))
                        $value = $choices[$value]['choice'];
                    else
                        $value = null;

                    // kv_type
                    if(str_contains($rec['operation'], "نوع کافو"))
                    {
                        $value = ($value == null)? "": $value;
                        if(str_contains($value, "BitStream") || str_contains($value, "بیت استریم") )
                            $array[$rec['exchange_id']]['bitstream'] = true;
                    }
                }
                if($rec['op_type'] == 4)
                {
                    $value = \app\components\Jdf::jdate("l, Y/m/d h:i", (integer)$value);
                }


                $array[$rec['exchange_id']][$rec['operation']] = ['type'=>$rec['op_type'], 'value'=>$value, 'op_value'=>$rec['op_value']];
            }

            if(empty($searchParams['area'])) $searchParams['area'] = -1;

            return $this->render('index', ['pages' => $pages,'userProject'=>$userProject, 'project'=>$project, 'projects'=>$projects,'totalCount'=>$totalCount, 'records'=>$array, 'operations'=>$operations, 'choices'=>$choices , 'searchParams'=>$searchParams, 'areaSelection'=>$areaSelection]);
        }
        else
        {
            if(isset($session['project'])) $session->remove('project');
            return $this->redirect(['main/home']);
        }
    }

    public function actionAttributes($pid = -1)
    {
        $project = \app\models\PcProjects::find()->where(['id'=>$pid])->asArray()->one();
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project'])) $session->remove('project');
        $session['project'] = $project;

        //operations
        $qry = \app\models\PcOperations::find()->where(['project_id'=>$pid])->orderBy('priority');
        $operationsDP = new \yii\data\ActiveDataProvider(['query' => $qry]);
        $operationsDP->pagination->pageSize = 50;
        $operations = $operationsDP->getModels();
        $array =[];// [op_id=>'background-color:green', ]
        $project_weight = 0;
        foreach ($operations as $op)
        {
            $id = $op['id'];
            $weight = $op['op_weight'];
            $project_weight += $weight;
            $weight = ($weight == null)? 0 : $weight;
            $totalChoiceWeight = \app\models\PcChoices::find()->select('SUM(choice_weight)')->where(['op_id'=>$id])->scalar();
            $default = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$id, 'default'=>true])->scalar();
            $totalChoiceWeight = ($totalChoiceWeight == null)? 0 : $totalChoiceWeight;
            $default = ($default == null)? 0 : $default;

            if(($default > 0) || ($op['type_id'] > 1) )
            {
                if(($weight > 0) && (($totalChoiceWeight >= $weight) || ($op['type_id'] > 1) ) )
                    $array[$id] = 'background-color: lightgreen';
                else if(($weight > 0) && ($totalChoiceWeight < $weight) )
                    $array[$id] = 'background-color: orange';
                else
                    $array[$id] = '';
            }
            else
            {
                if(($weight > 0) && (($totalChoiceWeight >= $weight) || ($op['type_id'] > 1) ) )
                    $array[$id] = 'background-color: lightgreen; color:darkred;';
                else if(($weight > 0) && ($totalChoiceWeight < $weight) )
                    $array[$id] = 'background-color: orange; color:darkred;';
                else
                    $array[$id] = 'color:darkred;';
            }
        }
        $colors = $array;

        $opType = \app\models\PcOpType::find()->asArray()->all();
        $array= [];
        foreach ($opType as $t)
        {
            $array[$t['id']] = $t['description'];
        }
        $opType = $array;
        unset($array);

        return $this->render('attributes', ['project'=>$project, 'project_weight'=>$project_weight, 'operationsDP'=>$operationsDP, 'colors'=>$colors, 'opType'=>$opType]);
    }

    public function actionNew_record()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $project = $session['project'];
            $project_id = $project['id'];
            //has any attribute/operations
            $cnt = \app\models\PcOperations::find()->select("COUNT(*)")->where(['project_id'=>$project_id])->scalar();
            if($cnt == 0)
            {
                Yii::$app->session->setFlash("error","پروژه حداقل یک ویژگی بایستی داشته باشد.");
                return $this->redirect(['project/index']);
            }

            $userProjects = $session['userProjects'];
            $upArea = $userProjects[$project['id']]['area'];
            $upeId = $userProjects[$project['id']]['exchange_id'];
            $areas=[];
            $exchanges = [];
            if((empty($upArea)) && (empty($upeId)))
            {
                $areas = [2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
                $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['type'=>2, 'project_id'=>$project_id])->orderBy('area, name')->all();
                $array = []; // [ 2=>[id=>exch, ... ] , 3=>[] ]
                foreach ($exchanges as $exch)
                {
                    $array[$exch['area']][$exch['id']] = $exch['name'];
                }

                $exchanges = $array;
            }
            else if( ($upArea > 0) && (empty($upeId)))
            {
                $areas = [$upArea => "$upArea"];
                $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['type'=>2, 'area'=>$upArea, 'project_id'=>$project_id])->orderBy('area, name')->all();
                $array = []; // [ 2=>[id=>exch, ... ] , 3=>[] ]
                foreach ($exchanges as $exch)
                {
                    $array[$exch['area']][$exch['id']] = $exch['name'];
                }
                $exchanges = $array;
            }
            else if(($upArea > 0) && ($upeId > 0))
            {
                $areas = [$upArea => "$upArea"];
                $name = \app\models\PcExchanges::find()->select('name')->where(['id'=>$upeId])->scalar();
                $exchanges[$upArea][$upeId] = $name;
            }

            $model = new \app\models\PcExchanges();
            $model->project_id = $session['project']['id'];
            $model->type = 3;

            $model->area = 2;
            if($upArea > 0)
                $model->area = $upArea;
            return $this->render('new_record', ['model'=>$model, 'areas'=>$areas, 'exchanges'=>$exchanges]);

        }

        return $this->redirect(['project/index']);
    }

    public function actionAdd_record()
    {
        $session = Yii::$app->session;
        $session->open();
        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $upArea = $userProjects[$project['id']]['area'];
            $upeId = $userProjects[$project['id']]['exchange_id'];

            $model = new \app\models\PcExchanges();
            if($model->load(Yii::$app->request->post()))
            {
                $access = false;
                if((empty($upArea)) && (empty($upeId)))
                {
                    $access = true;
                }
                else if(($upArea == $model->area) && (empty($upeId)))
                {
                    $access = true;
                }
                else if(($upArea == $model->area) && ($upeId == $model->exchange_id))
                {
                    $access = true;
                }

                if($access)
                {
                    if($model->type == 2)
                    {
                        $model->site_id = null;
                        $model->kv_code = null;
                        $model->center_id = null;
                    }
                    $model->project_id = $project['id'];

                    // check operations default
                    $defaults = $this->checkOperationsDefault($project['id']);
                    if(!$defaults['pass'])
                    {
                        Yii::$app->session->setFlash('error',' مقدار پیشفرض برای '.$defaults['value'].' موجود نیست. ');
                        return $this->redirect(['project/index']);
                    }

                    //check update or save
                    $current = \app\models\PcExchanges::find()->where(['area'=>$model->area, 'name'=>$model->name, 'type'=>$model->type,'site_id'=>$model->site_id, 'kv_code'=>$model->kv_code])->one();
                    if(empty($current))
                    {
                        $model->modifier_id = $session['user']['id'];
                        $model->modified_ts = time();
                        $model->register_ts = time();

                        //save
                        if($model->save())
                        {
                            //save operations
                            $this->saveOperations($model->id, $project['id']);
                            //update weight
                            $weight = \app\components\PdcpHelper::getWeight($model->project_id , $model->id);
                            $model->weight = $weight;
                            $model->update();

                            Yii::$app->session->setFlash('success','عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('error','ورود اطلاعات با خطا مواجه شد.');

                    }
                    else
                    {
                        //update
                        $model->modifier_id = $session['user']['id'];
                        $model->modified_ts = time();
                        $model->register_ts = time();
                        if($model->update())
                        {
                            //save operations
                            $this->saveOperations($model->id, $project['id']);

                            Yii::$app->session->setFlash('success','عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('error','ورود اطلاعات با خطا مواجه شد.');
                    }
                }
                else
                    Yii::$app->session->setFlash('error', 'شما مجاز به اعمال این عملیات نمی باشید.');
            }
            else
                Yii::$app->session->setFlash('error','ورود اطلاعات با خطا مواجه شد.');
        }

        return $this->redirect(['project/index']);
    }

    public function checkOperationsDefault($project_id)
    {
        $ok = true;
        $value = "";
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id, 'type_id'=>1])->asArray()->all();
        foreach ($operations as $op)
        {
            $id = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$op['id'], 'default'=>true])->scalar();
            $id = ($id == null)? 0 : $id;
            if($id < 1)
            {
                $ok = false;
                $value = $op['operation'];
                break;
            }
        }

        return ['pass'=>$ok, 'value'=>$value];

    }

    public function saveOperations($exchange_id, $project_id)
    {
        //save all operations to not choice
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project_id, 'type_id'=>1])->asArray()->all();
        //choices
        $choices = \app\models\PcViewChoices::find()->select('id,op_id')->where(['project_id'=>$project_id, 'default'=>true])->asArray()->all();
        $array = [];// [op1=>chid   ]
        foreach ($choices as $ch)
        {
            $array[$ch['op_id']] = $ch['id'];
        }
        $choices = $array;
        $array = [];

        foreach ($operations as $op)
        {
            $opId = $op['id'];
            if(isset($choices[$opId]))
            {
                $model = new \app\models\PcRecords();
                $model->op_id = $opId;
                $model->exchange_id = $exchange_id;
                $model->op_value = (string)$choices[$opId];
                $model->project_id = $project_id;
                $model->save();
            }
        }

    }

    public function actionView_record($eId = -1)
    {
        // check query:
        // (dep_identifier = ANY (ARRAY['plan'::text, 'install'::text, 'operation'::text, 'test'::text, 'it'::text, 'district'::text]))
        $session = Yii::$app->session;
        $session->open();

        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }

        if($searchParams['area'] == -1) $searchParams['area'] = '';

        if( ($eId > 0) && (isset($session['user'])) && $session['userProjects'] )
        {
            //find area
            $area = \app\models\PcExchanges::find()->select('area, center_id, done')->where(['id'=>$eId])->one();
            $center_id = $area->center_id;
            $done = $area->done;
            $area = $area->area;
            if($done == true)
            {
                return $this->redirect(['project/index']);
            }
            //check accessibility
            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $upArea = $userProjects[$project['id']]['area'];
            $upeId = $userProjects[$project['id']]['exchange_id'];

            $access = false;
            $level = 0;
            //admin
            if(empty($upArea) && empty($upeId))
            {
                $access  = true;
                $level = 1;
            }
            //area
            else if(empty($upeId) && ($area == $upArea) )
            {
                $access = true;
                $level = 2;
            }
            //exch
            else if( (($eId == $upeId) || ($center_id == $upeId) ) && ($area == $upArea))
            {
                $access = true;
                $level = 3;
            }


            if($access)
            {
                $user = $session['user'];

                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id']])->orderBy('project_id, priority')->asArray()->all();

                $choices = \app\models\PcViewChoices::find()->select('op_id, id,choice,choice_weight')->where(['project_id'=>$project['id']])->orderBy('op_id')->asArray()->all();
                $array = []; //  opid=>[ chid=>choice]
                foreach($choices as $ch)
                {
                    $array[$ch['op_id']][$ch['id']] = ['choice'=>$ch['choice'], 'weight'=>$ch['choice_weight']];
                }
                $choices = $array;

                // lom
                $lom = []; // [exch=>[id, equip, quantity], ...  ]
                $lom_detail = \app\models\PcViewLomDetail::find()->where(['exchange_id'=>$eId])->asArray()->all();
                foreach ($lom_detail as $ld)
                {
                    $lom[$ld['id']] = ['equipment'=>$ld['equipment'], 'quantity'=>$ld['quantity'] ];
                }

                $records = \app\models\PcViewRecords::find()->where(['exchange_id'=>$eId])->orderBy('area,name, exchange_id, priority')->asArray()->all();
                //[ exch=>[op1=>val1, op2=>val2], ... ]
                $array = [];
                $exchId = -1;
                $bs = false;
                $weight = 0;
                $project_weight = 0;

                foreach ($records as $rec)
                {
                    $exchId2 = $rec['exchange_id'];
                    if($exchId2 != $exchId)
                    {
                        $exchId = $exchId2;

//                        $array[$rec['exchange_id']]['id'] = $exchId;
                        $array[$rec['exchange_id']]['منطقه'] = ['type'=>2, 'value'=>$rec['area'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['نام مرکز/سایت'] = ['type'=>2, 'value'=>$rec['name'], 'opId'=>-1, 'chId'=>-1];
//                        $array[$rec['exchange_id']]['extype'] = $rec['extype'];
                        $array[$rec['exchange_id']]['مرکز اصلی'] = ['type'=>2, 'value'=>$rec['center_name'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['شناسه سایت'] = ['type'=>2, 'value'=>$rec['site_id'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['کد کافو'] = ['type'=>2, 'value'=>$rec['kv_code'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['آدرس'] = ['type'=>2, 'value'=>$rec['address'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['موقعیت'] = ['type'=>2, 'value'=>$rec['position'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['فاز'] = ['type'=>2, 'value'=>$rec['phase'], 'opId'=>-1, 'chId'=>-1];
                        $array[$rec['exchange_id']]['ضریب پیشرفت'] = ['type'=>2, 'value'=>$rec['weight'].'/'.$rec['project_weight'], 'opId'=>-1, 'chId'=>-1];
                        $weight = $rec['weight'];
                        $project_weight = $rec['project_weight'];


                        foreach($operations as $op)
                        {
                            $opText = $op['operation'];
                            if($op['op_weight'] > 0)
                                $opText = $opText.' - وزن ['.$op['op_weight'].'] ';

                            $array[$rec['exchange_id']][$opText] = ['type'=>$op['type_id'], 'value'=>'', 'opId'=>$op['id'], 'chId'=>-1];
                        }

                        $array[$rec['exchange_id']]['آخرین ویراستار'] = ['type'=>2, 'value'=>$rec['modifier_name'].' '.$rec['modifier_lastname'].' - '.$rec['modifier_office'], 'opId'=>-1, 'chId'=>-1];
                        $t = \app\components\Jdf::jdate("l, Y/m/d h:i", $rec['modified_ts']);
                        $array[$rec['exchange_id']]['زمان آخرین ویرایش'] = ['type'=>4, 'value'=>$t, 'opId'=>-1, 'chId'=>-1];
                        $t = \app\components\Jdf::jdate("l, Y/m/d h:i", $rec['register_ts']);
                        $array[$rec['exchange_id']]['زمان ثبت'] = ['type'=>4, 'value'=>$t, 'opId'=>-1, 'chId'=>-1];
                    }

                    $opId = $rec['op_id'];
                    $chId = -1;
                    $value = $rec['op_value'];
                    if(($rec['op_type'] == 1) && (!empty($value)) )
                    {
                        $chId = $value*1;
                        $value = $choices[$opId][$value]['choice'];

                        if($rec['operation'] == "نوع کافو")
                        {
                            if(str_contains($value, "BitStream") || str_contains($value, "بیت استریم") )
                                $bs = true;
                        }
                    }

                    if($rec['op_type'] == 4)
                    {
                        $value = \app\components\Jdf::jdate("l, Y/m/d h:i" , (integer)$value); //strtotime()
                    }
                    $opText = $rec['operation'];
                    if($rec['op_weight'] > 0)
                        $opText = $opText.' - وزن ['.$rec['op_weight'].'] ';
                    $array[$rec['exchange_id']][$opText] = ['type'=>$rec['op_type'], 'value'=>$value, 'opId'=>$opId, 'chId'=>$chId];
                }

                $records = $array;
                $array=[];
                foreach ($operations as $op)
                {
                    $array[$op['id']] = ['operation'=>$op['operation'] , 'type'=>$op['type_id'], 'project_id'=>$op['project_id'], 'priority'=>$op['priority'], 'permission'=>['design'=>$op['design_role'], 'install'=>$op['install_role'], 'operation'=>$op['operation_role'], 'test'=>$op['test_role'], 'it'=>$op['it_role'], 'district'=>$op['district_role'], 'planning'=>$op['planning_role'] ]];
                }
                $operations = $array;


                $exchangeModel = \app\models\PcExchanges::find()->where(['id'=>$eId])->one();
                $exchanges = [];
                $areas = [];
                $ar = $exchangeModel->area;
                if($level == 1)
                {
                    $areas = [2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['type'=>2])->orderBy('area, name')->all();
                    $array = []; // [ 2=>[id=>exch, ... ] , 3=>[] ]
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }

                    $exchanges = $array;
                }
                else if($level == 2)
                {
                    $areas = [$ar => "$ar"];
                    $exchanges = \app\models\PcExchanges::find()->select('id, area, name')->where(['type'=>2, 'area'=>$ar])->orderBy('area, name')->all();
                    $array = []; // [ 2=>[id=>exch, ... ] , 3=>[] ]
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;
                }
                else if($level == 3)
                {
                    $areas = [$ar => "$ar"];
                    $exchanges[$ar][$exchangeModel->id] = $exchangeModel->name;
                }

                $lomDetailModel = new \app\models\PcLomDetail();
                $lomDetailModel->exchange_id = $exchId;
                $projectLom = \app\models\PcLom::find()->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($projectLom as $item)
                {
                    $array[$item['id']] = $item['equipment'];
                }
                $projectLom = $array;

                return $this->render('view', ['records'=>$records,'lom'=>$lom, 'lomDetailModel'=>$lomDetailModel, 'projectLom'=>$projectLom, 'bs'=>$bs, 'weight'=>$weight, 'project_weight'=>$project_weight,  'bsExcept'=>Yii::$app->params['bsExcept'], 'operations'=>$operations,'userProject'=>$userProjects[$project['id']], 'user'=>$user, 'choices'=>$choices, 'exchange_id'=>$eId, 'exchangeModel'=>$exchangeModel, 'exchanges'=>$exchanges, 'areas'=>$areas, 'searchParams'=>$searchParams]);
            }

            Yii::$app->session->setFlash('error', 'شما دسترسی لازم برای این عملیات را ندارید.');

        }

        return $this->redirect(['project/index']);
    }

    public function actionExchange_add_lom()
    {
        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }
        if($searchParams['area'] == -1) $searchParams['area'] = '';

        $session = Yii::$app->session;
        $session->open();

        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $post = Yii::$app->request->post();
            $model = new \app\models\PcLomDetail();
            if($model->load($post) && ($post['PcLomDetail']['exchange_id'] > 0))
            {
                $area = \app\models\PcExchanges::find()->select("area")->where(['id'=>$model->exchange_id])->scalar();
                $projectId = \app\models\PcExchanges::find()->select("project_id")->where(['id'=>$model->exchange_id])->scalar();
                $usedItem = \app\models\PcViewLomDetail::find()->select("SUM(quantity)")->where(['area'=>$area, 'lom_id'=>$model->lom_id])->scalar();
                $dedication = \app\models\PcLom::find()->select("area".$area)->where(['id'=>$model->lom_id])->scalar();
                $left = $dedication - $usedItem;

                if( $left >= $model->quantity )
                {
                    try
                    {
                        $model->save();
                        Yii::$app->session->setFlash("success", "تجهیز با موفقیت به LOM افزوده شد.");
                    }
                    catch (\Exception $e)
                    {
                        Yii::$app->session->setFlash("error", "افزودن تجهیز به LOM با مشکل مواجه شد.");
                    }
                }
                else
                    Yii::$app->session->setFlash("error", "تعداد تجهیز باقیمانده از تخصیص منطقه برابر ".$left." می‌باشد. ");

                return $this->redirect(['project/view_record', 'eId'=>$model->exchange_id,
                    "search[area]"=>$searchParams['area'],
                    "search[phaseNo]"=>$searchParams['phaseNo'] ,
                    "search[name]"=>$searchParams['name'],
                    "search[center_name]"=>$searchParams['center_name'],
                    "search[site_id]"=>$searchParams['site_id'],
                    "search[kv_code]"=>$searchParams['kv_code'],
                    "search[page]"=>$searchParams['page'],
                    "page"=>$searchParams['page']
                ]);
            }
        }

        return $this->redirect(['project/index',
            "search[area]"=>$searchParams['area'],
            "search[phaseNo]"=>$searchParams['phaseNo'] ,
            "search[name]"=>$searchParams['name'],
            "search[center_name]"=>$searchParams['center_name'],
            "search[site_id]"=>$searchParams['site_id'],
            "search[kv_code]"=>$searchParams['kv_code'],
            "search[page]"=>$searchParams['page'],
            "page"=>$searchParams['page']]);
    }

    public function actionExchange_remove_lom()
    {
        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }
        if($searchParams['area'] == -1) $searchParams['area'] = '';

        $session = Yii::$app->session;
        $session->open();

        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $post = Yii::$app->request->post();
            $id = $post['lom_id'];
            $model =  \app\models\PcLomDetail::findOne($id);

            if($model->delete())
            {
                Yii::$app->session->setFlash("success", "تجهیز با موفقیت از LOM حذف شد.");
            }
            else
            {
                Yii::$app->session->setFlash("error", "حذف تجهیز از LOM با مشکل مواجه شد.");
            }

            return $this->redirect(['project/view_record', 'eId'=>$model->exchange_id,
                "search[area]"=>$searchParams['area'],
                "search[phaseNo]"=>$searchParams['phaseNo'] ,
                "search[name]"=>$searchParams['name'],
                "search[center_name]"=>$searchParams['center_name'],
                "search[site_id]"=>$searchParams['site_id'],
                "search[kv_code]"=>$searchParams['kv_code'],
                "search[page]"=>$searchParams['page'],
                "page"=>$searchParams['page']
            ]);
        }

        return $this->redirect(['project/index',
            "search[area]"=>$searchParams['area'],
            "search[phaseNo]"=>$searchParams['phaseNo'] ,
            "search[name]"=>$searchParams['name'],
            "search[center_name]"=>$searchParams['center_name'],
            "search[site_id]"=>$searchParams['site_id'],
            "search[kv_code]"=>$searchParams['kv_code'],
            "search[page]"=>$searchParams['page'],
            "page"=>$searchParams['page']]);
    }

    public function actionUpdate_operation()
    {
        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }
        if($searchParams['area'] == -1) $searchParams['area'] = '';

        $session = Yii::$app->session;
        $session->open();

        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $post = Yii::$app->request->post();
            $eId = $post['exchange_id'];
            $op_id = $post['operation_id'];
            $value = $post['operation'];

            $area = \app\models\PcExchanges::find()->select('area, center_id')->where(['id'=>$eId])->one();
            $center_id = $area->center_id;
            $area = $area->area;

            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $upArea = $userProjects[$project['id']]['area'];
            $upeId = $userProjects[$project['id']]['exchange_id'];
            $access = false;
            //admin
            if(empty($upArea) && empty($upeId))
            {
                $access  = true;
            }
            //area
            else if(empty($upeId) && ($area == $upArea) )
            {
                $access = true;
            }
            //exch
            else if( (($eId == $upeId) || ($center_id == $upeId) ) && ($area == $upArea))
            {
                $access = true;
            }

            if($access)
            {
                $type_id = \app\models\PcOperations::find()->select('type_id')->where(['id'=>$op_id])->scalar();
                $record = \app\models\PcRecords::find()->where(['project_id'=>$project['id'], 'exchange_id'=>$eId, 'op_id'=>$op_id ])->one();
                if(empty($record))
                {
                    //insert
                    $record = new \app\models\PcRecords();
                    $record->project_id = $project['id'];
                    $record->exchange_id = $eId;
                    $record->op_id = $op_id;
                    $record->op_value = $value;
                    if($type_id == 3) $value = (integer)$value;
                    if( ($type_id > 1) || (($type_id == 1) && ($value > 0)) )
                    {
                        if($record->save())
                        {
                            Yii::$app->session->setFlash('success', 'ویرایش اطلاعات با موفقیت انجام شد.');
                            //update modification times & user
                            $exch = \app\models\PcExchanges::findOne($eId);
                            $exch->modified_ts = time();
                            $exch->modifier_id = $session['user']['id'];
                            $exch->weight = \app\components\PdcpHelper::getWeight($exch->project_id , $exch->id);
                            $exch->save();

                            return $this->redirect(['project/view_record', 'eId'=>$eId,
                                "search[area]"=>$searchParams['area'],
                                "search[phaseNo]"=>$searchParams['phaseNo'] ,
                                "search[name]"=>$searchParams['name'],
                                "search[center_name]"=>$searchParams['center_name'],
                                "search[site_id]"=>$searchParams['site_id'],
                                "search[kv_code]"=>$searchParams['kv_code'],
                                "search[page]"=>$searchParams['page'],
                                "page"=>$searchParams['page']
                            ]);
                        }
                        else
                            Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');


                }
                else
                {
                    //update
                    if($type_id == 3) $value = (integer)$value;
                    $record->op_value = $value;
                    if($record->update(false))
                    {
                        Yii::$app->session->setFlash('success', 'ویرایش اطلاعات با موفقیت انجام شد.');
                        //update modification times & user
                        $exch = \app\models\PcExchanges::findOne($eId);
                        $exch->modified_ts = time();
                        $exch->modifier_id = $session['user']['id'];
                        $exch->weight = \app\components\PdcpHelper::getWeight($exch->project_id, $exch->id);
                        $exch->save();

                        return $this->redirect(['project/view_record', 'eId'=>$eId,
                            "search[area]"=>$searchParams['area'],
                            "search[phaseNo]"=>$searchParams['phaseNo'] ,
                            "search[name]"=>$searchParams['name'],
                            "search[center_name]"=>$searchParams['center_name'],
                            "search[site_id]"=>$searchParams['site_id'],
                            "search[kv_code]"=>$searchParams['kv_code'],
                            "search[page]"=>$searchParams['page'],
                            "page"=>$searchParams['page']
                        ]);
                    }
                    else
                        Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                }
            }
            Yii::$app->session->setFlash('error', 'شما دسترسی لازم برای این عملیات را ندارید.');
            return $this->redirect(['project/view_record', 'eId'=>$eId]);
        }

        return $this->redirect(['project/index',
            "search[area]"=>$searchParams['area'],
            "search[phaseNo]"=>$searchParams['phaseNo'] ,
            "search[name]"=>$searchParams['name'],
            "search[center_name]"=>$searchParams['center_name'],
            "search[site_id]"=>$searchParams['site_id'],
            "search[kv_code]"=>$searchParams['kv_code'],
            "search[page]"=>$searchParams['page'],
            "page"=>$searchParams['page']]);
    }

    public function actionUpdate_exchange()
    {
        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }
        if($searchParams['area'] == -1) $searchParams['area'] = '';

        $session = Yii::$app->session;
        $session->open();
        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $id = Yii::$app->request->post()['PcExchanges']['id'];
            $model = \app\models\PcExchanges::findOne($id);
            if($model->load(Yii::$app->request->post()))
            {
                if($model->type == 2)
                {
                    $model->site_id = null;
                    $model->kv_code = null;
                    $model->center_id = null;
                }

                if($model->save())
                {
                    Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'عملیات با خطا مواجه شد.');

            }
            else
                Yii::$app->session->setFlash('error', 'عملیات با خطا مواجه شد.');

            return $this->redirect(['project/view_record', 'eId'=>$id,
                "search[area]"=>$searchParams['area'],
                "search[phaseNo]"=>$searchParams['phaseNo'] ,
                "search[name]"=>$searchParams['name'],
                "search[center_name]"=>$searchParams['center_name'],
                "search[site_id]"=>$searchParams['site_id'],
                "search[kv_code]"=>$searchParams['kv_code'],
                "search[page]"=>$searchParams['page'],
                "page"=>$searchParams['page']
            ]);
        }

        return $this->redirect(['project/index',
            "search[area]"=>$searchParams['area'],
            "search[phaseNo]"=>$searchParams['phaseNo'] ,
            "search[name]"=>$searchParams['name'],
            "search[center_name]"=>$searchParams['center_name'],
            "search[site_id]"=>$searchParams['site_id'],
            "search[kv_code]"=>$searchParams['kv_code'],
            "search[page]"=>$searchParams['page'],
            "page"=>$searchParams['page']]);
    }

    public function actionRemove_record()
    {
        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }
        if($searchParams['area'] == -1) $searchParams['area'] = '';

        $session = Yii::$app->session;
        $session->open();
        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $id = Yii::$app->request->post()['id'];
            $area = \app\models\PcExchanges::find()->select('area, center_id')->where(['id'=>$id])->one();
            $center_id = $area->center_id;
            $area = $area->area;

            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $upArea = $userProjects[$project['id']]['area'];
            $upeId = $userProjects[$project['id']]['exchange_id'];
            $access = false;
            //admin
            if(empty($upArea) && empty($upeId))
            {
                $access  = true;
            }
            //area
            else if(empty($upeId) && ($area == $upArea) )
            {
                $access = true;
            }
            //exch
            else if( (($id == $upeId) || ($center_id == $upeId) ) && ($area == $upArea))
            {
                $access = true;
            }

            if($access)
            {
                \app\models\PcRecords::deleteAll(['exchange_id'=>$id]);
                $exch = \app\models\PcExchanges::findOne($id);
                if($exch->type == 3)
                {
                    $exchName = $exch->area.' '.$exch->name;
                    if($exch->delete())
                    {
                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'عملیات با خطا مواجه شد.');
                }
            }
            else
                Yii::$app->session->setFlash('error', 'شما مجاز به انجام این عملیات نمی باشید.');

        }

        return $this->redirect(['project/index',
            "search[area]"=>$searchParams['area'],
            "search[phaseNo]"=>$searchParams['phaseNo'] ,
            "search[name]"=>$searchParams['name'],
            "search[center_name]"=>$searchParams['center_name'],
            "search[site_id]"=>$searchParams['site_id'],
            "search[kv_code]"=>$searchParams['kv_code'],
            "search[page]"=>$searchParams['page'],
            "page"=>$searchParams['page']]);
    }

    public function actionDone_record()
    {
        $searchParams = ['phaseNo'=>-1, 'area'=>'', 'name'=>'', 'center_name'=>'', 'site_id'=>'', 'kv_code'=>'', 'page'=>0];//, 'address'=>''
        $qp = Yii::$app->request->queryParams;
        if(isset($qp['search']))
        {
            $qp = $qp['search'];
            if(isset($qp['area']))
                $searchParams['area'] = $qp['area'];
            if(isset($qp['phaseNo']))
                $searchParams['phaseNo'] = $qp['phaseNo'];
            if(isset($qp['name']))
                $searchParams['name'] = $qp['name'];
            if(isset($qp['center_name']))
                $searchParams['center_name'] = $qp['center_name'];
            if(isset($qp['site_id']))
                $searchParams['site_id'] = $qp['site_id'];
            if(isset($qp['kv_code']))
                $searchParams['kv_code'] = $qp['kv_code'];
            if(isset($qp['page']))
                $searchParams['page'] = $qp['page'];
        }
        if($searchParams['area'] == -1) $searchParams['area'] = '';

        $session = Yii::$app->session;
        $session->open();
        if((Yii::$app->request->isPost) && (isset($session['user'])) )
        {
            $id = Yii::$app->request->post()['id'];
            $area = \app\models\PcExchanges::find()->select('area, center_id')->where(['id'=>$id])->one();
            $center_id = $area->center_id;
            $area = $area->area;

            $project = $session['project'];
            $userProjects = $session['userProjects'];
            $upArea = $userProjects[$project['id']]['area'];
            $upeId = $userProjects[$project['id']]['exchange_id'];
            $access = false;
            //admin
            if(empty($upArea) && empty($upeId))
            {
                $access  = true;
            }
            //area
            else if(empty($upeId) && ($area == $upArea) )
            {
                $access = true;
            }
            //exch
            else if( (($id == $upeId) || ($center_id == $upeId) ) && ($area == $upArea))
            {
                $access = true;
            }

            if($access)
            {
                $exch = \app\models\PcExchanges::findOne($id);
                $exch->done = true;
                $exch->modified_ts = time();
                $exch->modifier_id = $session['user']['id'];
                if($exch->update())
                {
                    Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'عملیات با خطا مواجه شد.');
            }
            else
                Yii::$app->session->setFlash('error', 'شما مجاز به انجام این عملیات نمی باشید.');

        }

        return $this->redirect(['project/index',
            "search[area]"=>$searchParams['area'],
            "search[phaseNo]"=>$searchParams['phaseNo'] ,
            "search[name]"=>$searchParams['name'],
            "search[center_name]"=>$searchParams['center_name'],
            "search[site_id]"=>$searchParams['site_id'],
            "search[kv_code]"=>$searchParams['kv_code'],
            "search[page]"=>$searchParams['page'],
            "page"=>$searchParams['page']]);
    }

    private function delete_operation($eId, $op_id)
    {
        $session = Yii::$app->session;
        $session->open();

        if(isset($session['project']))
        {
            $project = $session['project'];
            $record = \app\models\PcRecords::find()->where(['project_id'=>$project['id'], 'exchange_id'=>$eId, 'op_id'=>$op_id ])->one();
            if(!empty($record))
            {
                if($record->delete())
                {
                    try{
                        //log
                        $op = \app\models\PcOperations::find()->select('operation')->where(['id'=>$op_id])->scalar();
                        $log = new \app\models\PcLogs();
                        $log->user_id = $session['user']['id'];
                        $log->exchange_id = $eId;
                        $log->action = " حذف ویژگی ". $op;
                        $log->project_id = $session['project']['id'];
                        $log->ts = time();
                        $log->save();
                    }
                    catch (\Exception $e){}

                    Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'عملیات با خطا مواجه شد.');
            }
            else
                Yii::$app->session->setFlash('error', 'عملیات با خطا مواجه شد.');

            return $this->redirect(['project/view_record?eId='.$eId]);
        }
        return $this->redirect(['project/index']);
    }

    public function actionUpdate_weight($id = -1)
    {
        //update weights
        $exchanges = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>$id])->asArray()->all();
        foreach ($exchanges as $exchange)
        {
            $exch = \app\models\PcExchanges::findOne($exchange['id']);
            $exch->weight = \app\components\PdcpHelper::getWeight($exch->project_id , $exch->id);
            $exch->update(false);
        }
        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
        return $this->redirect(['project/index?id='.$id]);
    }
}
