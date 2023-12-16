<?php

namespace app\controllers;
use app\models\PcProjects;
use phpDocumentor\Reflection\Types\Scalar;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\db\JsonExpression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class ProjectsController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $session->open();
        if ((isset($session['user'])))
        {
            if($session['user']['admin'] == 1)
                return parent::beforeAction($action);
            else
                return $this->redirect(["main/home"]);
        }
        else
        {
            return $this->redirect(["main/login"]);
        }
    }

    public function actionIndex()
    {
        $model = \app\models\PcProjects::find()->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        $url = Yii::$app->request->baseUrl.'/projects/setting?id=';
        return $this->render('index', ['model'=>$model, 'url'=>$url]);
    }

    public function actionNew_project()
    {
        $session = Yii::$app->session;
        $session->open();

        $model = new \app\models\PcProjects();
        if(Yii::$app->request->isPost)
        {
            if($model->load(Yii::$app->request->post()))
            {
                $model->ts = time();
                if($model->save())
                {
                    // add centers
                    $center_pId = \app\models\PcExchanges::find()->select('project_id')->where(['abbr'=>'ES'])->one();
                    $center_pId = $center_pId->project_id;
                    $centers = \app\models\PcExchanges::find()->where(['type'=>2, 'project_id'=>$center_pId])->all();
                    foreach($centers as $cen)
                    {
                        $m = new \app\models\PcExchanges();
                        $m->project_id = $model->id;
                        $m->area = $cen->area;
                        $m->name = $cen->name;
                        $m->abbr = $cen->abbr;
                        $m->type = $cen->type;
                        $m->address = $cen->address;
                        $m->done = false;
                        $m->weight = 0;
                        $m->save(false);
                    }


                    Yii::$app->session->setFlash('success', 'پروژه جدید با موفقیت اضافه شد.');
                }
                else
//                    return var_dump($model->getErrors());
                    Yii::$app->session->setFlash('error','افزودن پروژه جدید با خطا مواجه شد.');

                return $this->redirect(['projects/edit_project']);
            }
        }

        return $this->render('new_project', ['model'=>$model]);
    }

    public function actionEdit_project($id=-1)
    {
        $projects = \app\models\PcProjects::find()->orderBy(['ts'=>SORT_DESC])->asArray()->all();

        if($id > -1)
        {
            $session = Yii::$app->session;
            $session->open();
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();
            $lom = \app\models\PcLom::find()->where(['project_id'=>$id])->orderBy("id")->all();
            $model = \app\models\PcProjects::findOne($id);

            if(Yii::$app->request->isPost)
            {
                $post = Yii::$app->request->post()['PcProjects'];
                $model->project = $post['project'];
                $model->office = $post['office'];
                $model->contract_subject = $post['contract_subject'];
                $model->contract_company = $post['contract_company'];
                $model->contract_date = $post['contract_date'];
                $model->contract_duration = $post['contract_duration'];
                $model->enabled = $post['enabled'];

                if($model->update())
                {
                    try
                    {
                        //log
                        $log = new \app\models\PcLogs();
                        $log->user_id = $session['user']['id'];
                        $log->exchange_id = null;
                        $log->action = " ویرایش پروژه ". $model->project;
                        $log->project_id = $model->id;
                        $log->ts = time();
                        $log->save();
                    }
                    catch (\Exception $e){}


                    Yii::$app->session->setFlash('success', 'پروژه با موفقیت ویرایش شد.');
                }
                else
                    Yii::$app->session->setFlash('error','ویرایش پٰروژه با خطا مواجه شد.');

                return $this->redirect(['projects/edit_project', "id"=>$id]);

            }

            return $this->render('edit_project', ['model'=>$model,'projects'=>$projects, 'project'=>$project, "lom"=>$lom]);
        }
        else
        {
            // no selected project
            return $this->render('edit_project', ['projects'=>$projects, 'model' => "","project"=>"", 'lom' => ""]);
        }
    }

    public function actionLom_action()
    {
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $action = $post['btnAction'];
            if($post['pId'] > -1)
            {
                if($action == 'add')
                    return $this->add_equipment($post);

                return $this->redirect(['projects/edit_project?id='.$pId]);
            }
        }
        return $this->redirect(['projects/edit_project']);
    }

    private function add_equipment($post)
    {
        $pId = $post['pId'];
        $project = \app\models\PcProjects::findOne($pId);
        $model = new \app\models\PcLom();
        $model->project_id = $pId;
        return $this->render("add_equip", ['project'=>$project, 'model'=>$model]);
    }

    //---------------------
    public function actionLom_template()
    {
        $path = getcwd();
        return Yii::$app->response->sendFile($path."/views/projects/PDCP_LOM_Template.xls");
    }

    public function actionParse_lom()
    {
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $project_id = $post['project-id'];
            $fileName = $_FILES['file-upload']['name'];
            $fileName = strtolower($fileName);
            if(str_ends_with($fileName,".xls"))
            {
                $file = $_FILES['file-upload']['tmp_name'];
                $reader = IOFactory::createReaderForFile($file);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file);
                $spreadsheet->getSheet(0);
                //check xls columns standard
                if($this->checkXlsColumns($spreadsheet))
                {
                    //COLUMNS ARE OK
                    $modelsArray = $this->xlsToModelsArray($spreadsheet,$project_id);

                    //check duplicate entries
                    if(!self::checkDuplicate($modelsArray, $project_id))
                        return $this->redirect(['projects/edit_project?id='.$project_id]);

                    // ready to import
                    foreach ($modelsArray as $model)
                    {
                        if($this->addLom($project_id,  $model))
                            $model->done = true;
                        else
                        {
                            $model->done = false;
                            break;
                        }
                    }

                    $project = \app\models\PcProjects::find()->select('project')->where(['id'=>$project_id])->scalar();
                    return $this->render("lom_import_res", ['models'=>$modelsArray, 'project'=>$project, 'project_id'=>$project_id]);
                }
                else
                    return $this->redirect(['import/index']);
            }
            Yii::$app->session->setFlash("error", "فایل ورودی بایستی با پسوند xls بارگذاری گردد.");
            return $this->redirect(['projects/edit_project?id='.$project_id]);
        }
        return $this->redirect(['projects/edit_project']);
    }

    private function checkXlsColumns($spreadsheet)
    {
        /* @var $spreadsheet  Spreadsheet */
        //A2 -> equip
        $text = $spreadsheet->getActiveSheet()->getCell("A1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "تجهیز")
        {
            Yii::$app->session->setFlash("error", "ستون اول فایل اکسل ورودی بایستی تجهیز باشد.");
            return false;
        }

        //B2 -> desc
        $text = $spreadsheet->getActiveSheet()->getCell("B1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "توضیحات")
        {
            Yii::$app->session->setFlash("error", "ستون دوم فایل اکسل ورودی بایستی توضیحات باشد.");
            return false;
        }

        //C2 -> quantity
        $text = $spreadsheet->getActiveSheet()->getCell("C1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "تعداد کل")
        {
            Yii::$app->session->setFlash("error", "ستون سوم فایل اکسل ورودی بایستی تعداد کل باشد.");
            return false;
        }

        //D2 -> area2
        $text = $spreadsheet->getActiveSheet()->getCell("D1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۲")
        {
            Yii::$app->session->setFlash("error", "ستون چهارم فایل اکسل ورودی بایستی منطقه ۲ باشد.");
            return false;
        }

        //E2 -> area3
        $text = $spreadsheet->getActiveSheet()->getCell("E1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۳")
        {
            Yii::$app->session->setFlash("error", "ستون پنجم فایل اکسل ورودی بایستی منطقه ۳ باشد.");
            return false;
        }

        //F2 -> area4
        $text = $spreadsheet->getActiveSheet()->getCell("F1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۴")
        {
            Yii::$app->session->setFlash("error", "ستون ششم فایل اکسل ورودی بایستی منطقه ۴ باشد.");
            return false;
        }

        //G2 -> area5
        $text = $spreadsheet->getActiveSheet()->getCell("G1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۵")
        {
            Yii::$app->session->setFlash("error", "ستون هفتم فایل اکسل ورودی بایستی منطقه ۵ باشد.");
            return false;
        }

        //H2 -> area6
        $text = $spreadsheet->getActiveSheet()->getCell("H1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۶")
        {
            Yii::$app->session->setFlash("error", "ستون هشتم فایل اکسل ورودی بایستی منطقه ۶ باشد.");
            return false;
        }

        //H2 -> area7
        $text = $spreadsheet->getActiveSheet()->getCell("I1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۷")
        {
            Yii::$app->session->setFlash("error", "ستون نهم فایل اکسل ورودی بایستی منطقه ۷ باشد.");
            return false;
        }

        //H2 -> area8
        $text = $spreadsheet->getActiveSheet()->getCell("J1")->getValue();
        $text = strtolower($text);
        $text = trim($text);
        if($text != "منطقه ۸")
        {
            Yii::$app->session->setFlash("error", "ستون دهم فایل اکسل ورودی بایستی منطقه ۸ باشد.");
            return false;
        }

        return true;
    }

    private function xlsToModelsArray($spreadsheet,$project_id)
    {
        /* @var $spreadsheet  Spreadsheet */
        $array = []; //
        $maxRow = $spreadsheet->getActiveSheet()->getHighestRow();
        for($row = 2; $row <= $maxRow; $row++)
        {
            $model = new \app\models\XlsLomModel();
            $model->projectId = $project_id;
            $model->equipment = self::getSpreadsheetValue($spreadsheet,$row,"equipment");
            $model->description = (string)self::getSpreadsheetValue($spreadsheet,$row,"description");
            $model->quantity = self::getSpreadsheetValue($spreadsheet,$row,"quantity");

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area2");
            if(empty($val)) $val = 0;
            $model->area2 = $val;

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area3");
            if(empty($val)) $val = 0;
            $model->area3 = $val;

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area4");
            if(empty($val)) $val = 0;
            $model->area4 = $val;

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area5");
            if(empty($val)) $val = 0;
            $model->area5 = $val;

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area6");
            if(empty($val)) $val = 0;
            $model->area6 = $val;

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area7");
            if(empty($val)) $val = 0;
            $model->area7 = $val;

            $val = self::getSpreadsheetValue($spreadsheet,$row,"area8");
            if(empty($val)) $val = 0;
            $model->area8 = $val;

            $model->done = false;
            array_push($array, $model);
        }

        return $array;
    }

    private function getSpreadsheetValue($spreadsheet, $row, $columnTitle)
    {
        /* @var $spreadsheet  Spreadsheet */
        $col = "";
        if($columnTitle == "equipment")
            $col = "A";
        else if($columnTitle == "description")
            $col = "B";
        else if($columnTitle == "quantity")
            $col = "C";
        else if($columnTitle == "area2")
            $col = "D";
        else if($columnTitle == "area3")
            $col = "E";
        else if($columnTitle == "area4")
            $col = "F";
        else if($columnTitle == "area5")
            $col = "G";
        else if($columnTitle == "area6")
            $col = "H";
        else if($columnTitle == "area7")
            $col = "I";
        else if($columnTitle == "area8")
            $col = "J";
        else
            return "";

        $cell = $col.$row;
        $value = $spreadsheet->getActiveSheet()->getCell($cell)->getValue();
        return $value;
    }

    private function checkDuplicate($modelsArray, $project_id)
    {
        $loms = \app\models\PcLom::find()->where(['project_id'=>$project_id])->asArray()->all();
        $array = [];
        foreach ($loms as $lom)
        {
            $temp = $lom['equipment'];
            $temp = trim($temp);
            array_push($array, $temp);
        }

        foreach ($modelsArray as $model)
        {
            /* @var $model \app\models\XlsLomModel*/
            $equipment = $model->equipment;
            if(in_array($equipment, $array))
            {
                Yii::$app->session->setFlash('error', "تجهیز تکراری برای یک پروژه میسر نمی‌باشد."."[ ".$equipment." ]");
                return false;
            }
        }

        return true;
    }

    private function addLom($project_id,  $model)
    {
        /* @var $model  \app\models\XlsLomModel */
        $mdl = new \app\models\PcLom();
        $mdl->project_id = $model->projectId;
        $mdl->equipment = trim($model->equipment);
        $mdl->description = $model->description;
        $mdl->quantity = $model->quantity;
        $mdl->area2 = $model->area2;
        $mdl->area3 = $model->area3;
        $mdl->area4 = $model->area4;
        $mdl->area5 = $model->area5;
        $mdl->area6 = $model->area6;
        $mdl->area7 = $model->area7;
        $mdl->area8 = $model->area8;
        if($mdl->save())
           return true;
        else
            return false;
    }
//---------------------

    public function actionAdd_lom()
    {
        if(Yii::$app->request->isPost)
        {
            $model = new \app\models\PcLom();
            if($model->load(Yii::$app->request->post()))
            {
                $lom = $model->equipment;
                $lom = trim($lom);
                $model->equipment = $lom;
                if(empty($model->area2)) $model->area2 = 0;
                if(empty($model->area3)) $model->area3 = 0;
                if(empty($model->area4)) $model->area4 = 0;
                if(empty($model->area5)) $model->area5 = 0;
                if(empty($model->area6)) $model->area6 = 0;
                if(empty($model->area7)) $model->area7 = 0;
                if(empty($model->area8)) $model->area8 = 0;

                if($model->save())
                    return $this->redirect(['projects/edit_project', 'id'=>$model->project_id]);
            }
        }
        return $this->redirect(['projects/edit_project']);
    }

    public function actionEdit_lom($id = -1)
    {
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $action = $post['action'];
            if($action == "update")
            {
                $id = -1;
                if(isset($post['PcLom']['id'])) $id = $post['PcLom']['id'];
                $model = \app\models\PcLom::findOne($id);
                if($model->load($post))
                {
                    $lom = $model->equipment;
                    $lom = trim($lom);
                    $model->equipment = $lom;
                    if(empty($model->area2)) $model->area2 = 0;
                    if(empty($model->area3)) $model->area3 = 0;
                    if(empty($model->area4)) $model->area4 = 0;
                    if(empty($model->area5)) $model->area5 = 0;
                    if(empty($model->area6)) $model->area6 = 0;
                    if(empty($model->area7)) $model->area7 = 0;
                    if(empty($model->area8)) $model->area8 = 0;
                    
                    if($model->update())
                        return $this->redirect(['projects/edit_project', 'id'=>$model->project_id]);
                    else
//                    return var_dump($model->getErrors());
                        Yii::$app->session->setFlash("error", "ویرایش LOM با خطا مواجه گردید.");
                }
            }
            else if ($action == "delete")
            {
                $id = -1;
                if(isset($post['PcLom']['id'])) $id = $post['PcLom']['id'];
                $model = \app\models\PcLom::findOne($id);
                $pid = $model->project_id;
                if($model->delete())
                    return $this->redirect(['projects/edit_project', 'id'=>$pid]);
                else
                    Yii::$app->session->setFlash("error", "حذف تجهیز با خطا مواجه شد.");
            }

        }

        if($id > -1)
        {
            $model = \app\models\PcLom::findOne($id);
            $project = \app\models\PcProjects::findOne($model->project_id);
            return $this->render('edit_lom',[ "model"=>$model, "project"=>$project]);
        }
        else
            return $this->redirect(['projects/edit_project']);
    }

    public function actionRemove_project($id=-1)
    {
        $projects = \app\models\PcProjects::find()->orderBy(['ts'=>SORT_DESC])->asArray()->all();

        if($id > -1)
        {
            $session = Yii::$app->session;
            $session->open();
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();

            $model = \app\models\PcProjects::findOne($id);
            if(Yii::$app->request->isPost)
            {
                $id = Yii::$app->request->post()['PcProjects']['id'];
                $model = \app\models\PcProjects::findOne($id);
                $projectName = $model->project;

                try{
                    $model->delete();
                    \app\components\PdcpHelper::setUserProjectSession();
                    Yii::$app->session->setFlash('success', 'پروژه با موفقیت حذف شد.');
                }
                catch (\Exception $e)
                {
                    Yii::$app->session->setFlash('error',' حذف پٰروژه با خطا مواجه شد. ');
                }

                return $this->redirect(['projects/edit_project']);

            }

            return $this->render('remove_project', ['model'=>$model, 'project'=>$project, 'projects'=>$projects]);

        }
        else
        {
            return $this->render('remove_project', ['model'=>"", 'project'=>"", 'projects'=>$projects]);
        }

    }

    public function actionSetting($id = -1)
    {
        $projects = \app\models\PcProjects::find()->orderBy(['ts'=>SORT_DESC])->asArray()->all();
        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            if(isset($session['project'])) $session->remove('project');
            $session['project'] = $project;

            //operations
            $qry = \app\models\PcOperations::find()->where(['project_id'=>$id])->orderBy('priority');
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
                    if(($weight > 0) && (($totalChoiceWeight >= $weight)|| ($op['type_id'] > 1) ) )
                        $array[$id] = 'background-color: lightgreen';
                    else if(($weight > 0) && ($totalChoiceWeight < $weight) )
                        $array[$id] = 'background-color: orange';
                    else
                        $array[$id] = '';
                }
                else
                {
                    if(($weight > 0) && (($totalChoiceWeight >= $weight)|| ($op['type_id'] > 1) ) )
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

            return $this->render('setting', ['project'=>$project,'projects'=>$projects, 'project_weight'=>$project_weight, 'operationsDP'=>$operationsDP, 'colors'=>$colors, 'opType'=>$opType]);

        }
        else
        {
            return $this->render('setting', ['projects'=>$projects,'project'=>"", 'project_weight'=>"", 'operationsDP'=>"", 'colors'=>"", 'opType'=>""]);
        }

    }

    public function actionProject_users($id = -1)
    {
        $projects = \app\models\PcProjects::find()->orderBy(['ts'=>SORT_DESC])->asArray()->all();

        if($id > -1)
        {
            $project = \app\models\PcProjects::find()->where(['id'=>$id])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            if(isset($session['project'])) $session->remove('project');
            $session['project'] = $project;

            //users
            $usersSM = new \app\models\PcViewUserProjectsSearch();
            $params = Yii::$app->request->queryParams;

            if (isset($params['PcViewUserProjectsSearch']))
            {
                $params['PcViewUserProjectsSearch']['project_id'] = $id;
                $usersDP = $usersSM->search($params);
            }
            else
            {
                $qry = \app\models\PcViewUserProjects::find()->where(['project_id'=>$id])->orderBy("area, office,lastname, name");
                $usersDP = new \yii\data\ActiveDataProvider(['query' => $qry]);

            }
            $usersDP->pagination->pageSize = 10;
            $usersDP->setSort(['defaultOrder' => ['area'=>SORT_ASC]]);

            return $this->render('project_users', ['project'=>$project, 'projects'=>$projects, 'usersSM'=>$usersSM, 'usersDP'=>$usersDP]);

        }
        else
        {
            return $this->render('project_users', ['project'=>"",'projects'=>$projects, 'usersSM'=>"", 'usersDP'=>""]);
        }
    }

    public function actionNew_user()
    {
        $session = Yii::$app->session;
        $session->open();

        if(isset($session['project']))
        {
            $project = $session['project'];
            $model = new \app\models\PcUserProjects();
            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    $model->project_id = $project['id'];
                    if($model->area == -1)
                    {
                        $model->area = null;
                        $model->exchange_id = null;
                    }

                    if($model->exchange_id == -1) $model->exchange_id = null;
                    $model->enabled = 1;

                    if($model->save())
                    {
                        \app\components\PdcpHelper::setUserProjectSession();
                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'ذخیره اطلاعات با خطا مواجه شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'دریافت اطلاعات با خطا مواجه شد.');

                return $this->redirect(['projects/project_users?id='.$project['id']]);
            }

            $users = \app\models\PcUsers::find()->select('id, name, lastname, office')->orderBy('office, lastname, name')->asArray()->all();
            $array=[];
            //['cat1'=>[id1=>"samad"], 'cat2'=>[id2=>"you"]]
            foreach($users as $u)
            {
                $array[$u['office']][$u['id']] = $u['name'].' '.$u['lastname'];
            }
            $users = $array;

            $projects = \app\models\PcProjects::find()->orderBy('ts, project')->asArray()->all();
            $array = [];
            foreach($projects as $p)
            {
                $array[$p['id']] = $p['project'].' ['.$p['office'].']';
            }
            $projects = $array;

            $areas = [-1=>"تمام مناطق", 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
            $exchanges = \app\models\PcExchanges::find()->select('id,area, name')->where(['project_id'=>$project['id'], 'type'=>2])->orderBy('area, name')->asArray()->all();
            $array=[];
            $array = [2=>['-1'=>'تمام مراکز'], 3=>['-1'=>'تمام مراکز'], 4=>['-1'=>'تمام مراکز'], 5=>['-1'=>'تمام مراکز'], 6=>['-1'=>'تمام مراکز'], 7=>['-1'=>'تمام مراکز'], 8=>['-1'=>'تمام مراکز']];
            foreach ($exchanges as $e)
            {
                $array[$e['area']][$e['id']] = $e['name'];
            }
            $exchanges = $array;

            return $this->render('new_user', ['project'=>$project,'model'=>$model, 'users'=>$users, 'projects'=>$projects, 'areas'=>$areas, 'exchanges'=>$exchanges]);
        }

        return $this->redirect(['projects/index']);
    }

    public function actionEdit_user_project($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();

        if(isset($session['project']))
        {
            $project = $session['project'];
            $model = \app\models\PcUserProjects::findOne($id);
            $view = \app\models\PcViewUserProjects::findOne($id);
            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    if($model->area == -1)
                    {
                        $model->area = null;
                        $model->exchange_id = null;
                    }

                    if($model->exchange_id == -1) $model->exchange_id = null;

                    if($model->update())
                    {
                        \app\components\PdcpHelper::setUserProjectSession();
                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'ذخیره اطلاعات با خطا مواجه شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'دریافت اطلاعات با خطا مواجه شد.');

                return $this->redirect(['projects/project_users?id='.$project['id']]);
            }

            $user = $view->name . ' '. $view->lastname.' ['.$view->office.']';
            if($model->area == null) $model->area = -1;
            if($model->exchange_id == null) $model->exchange_id = -1;

            $areas = [-1=>"تمام مناطق", 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
            $exchanges = \app\models\PcExchanges::find()->select('id,area, name')->where(['project_id'=>$project['id'], 'type'=>2])->orderBy('area, name')->asArray()->all();
            $array=[];
            $array = [2=>['-1'=>'تمام مراکز'], 3=>['-1'=>'تمام مراکز'], 4=>['-1'=>'تمام مراکز'], 5=>['-1'=>'تمام مراکز'], 6=>['-1'=>'تمام مراکز'], 7=>['-1'=>'تمام مراکز'], 8=>['-1'=>'تمام مراکز']];
            foreach ($exchanges as $e)
            {
                $array[$e['area']][$e['id']] = $e['name'];
            }
            $exchanges = $array;

            $projectName = $view->project;
            return $this->render('edit_user_project', ['project'=>$project,'projectName'=>$projectName, 'model'=>$model, 'user'=>$user, 'areas'=>$areas, 'exchanges'=>$exchanges]);
        }

        return $this->redirect(['projects/index']);
    }

    public function actionRemove_user_project($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();

        if(isset($session['project']))
        {
            $project = $session['project'];
            if(Yii::$app->request->isPost)
            {
                $id = Yii::$app->request->post()['PcViewUserProjects']['id'];
                $model = \app\models\PcUserProjects::findOne($id);

                if($model->delete())
                {
                    \app\components\PdcpHelper::setUserProjectSession();
                    Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'عملیات با خطا انجام شد.');

                return $this->redirect(['projects/project_users?id='.$project['id']]);
            }

            $view = \app\models\PcViewUserProjects::findOne($id);
            return $this->render('remove_user_project', ['model'=>$view]);
        }

        return $this->redirect(['projects/index']);
    }

    public function actionNew_op()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            $opType = \app\models\PcOpType::find()->asArray()->all();
            $array= [];
            foreach ($opType as $t)
            {
                $array[$t['id']] = $t['description'];
            }
            $opType = $array;

            $model = new \app\models\PcOperations();
            $model->project_id = $project['id'];
            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    if($model->save())
                    {
                        // update project weight
                        if($model->op_weight > 0)
                        {
                            $project = \app\models\PcProjects::findOne($model->project_id);
                            $weight = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$model->project_id])->scalar();
                            $project->project_weight = (integer)$weight;
                            $project->update();
                        }

                        //add new operation type 1 to all exchanges
                        if($model->type_id == 1)
                        {
                            $allExchanges = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$model->project_id])->asArray()->all();
                            foreach ($allExchanges as $ex)
                            {
                                $rec = new \app\models\PcRecords();
                                $rec->project_id = $model->project_id;
                                $rec->exchange_id = $ex['exchange_id'];
                                $rec->op_id = $model->id;
                                $rec->op_value = null;
                                $rec->save();
                            }
                            //weights are effected for type 1 operation -- type 1 >> null value
                        }

                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'ذخیره اطلاعات با خطا مواجه شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'دریافت اطلاعات با خطا مواجه شد.');

                return $this->redirect(['projects/setting?id='.$project['id']]);
            }

            return $this->render('new_op', ['project'=>$project,'model'=>$model, 'opType'=>$opType]);
        }

        return $this->redirect(['projects/index']);
    }

    public function actionEdit_op_choices($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            if($id > 0)
            {
                $operation = \app\models\PcOperations::find()->where(['id'=>$id, 'project_id'=>$project['id']])->asArray()->one();
                if(!empty($operation) && ($operation['type_id'] == 1))
                {
                    $choices = \app\models\PcChoices::find()->where(['op_id'=>$id])->orderBy('id');
                    $choices = new \yii\data\ActiveDataProvider(['query'=>$choices]);

                    return $this->render('edit_choices', ['project'=>$project, 'operation'=>$operation, 'choices'=>$choices]);
                }
            }
            return $this->redirect(['projects/setting?id='.$project['id']]);
        }
        return $this->redirect(['projects/index']);
    }

    public function actionNew_choice($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            if ($id > 0)
            {
                //operaion id
                $model = new \app\models\PcChoices();
                if(Yii::$app->request->isPost)
                {
                    if($model->load(Yii::$app->request->post()))
                    {
                        // check if set to default
                        $default = $model->default;
                        if($default == true)
                        {
                            //clear other default
                            $op_id = $model->op_id;
                            $chq = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$op_id]);
                            \app\models\PcChoices::updateAll(['default'=>false], ['in','id', $chq]);
                        }

                        if($model->save())
                        {

                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                            else
                                Yii::$app->session->setFlash('error', 'ذخیره اطلاعات با خطا مواجه شد.');

                        return $this->redirect(['projects/edit_op_choices?id='.$model->op_id]);

                    }
                    else
                        Yii::$app->session->setFlash('error', 'ورود اطلاعات با خطا مواجه شد.');
                }
                $operation = \app\models\PcOperations::find()->where(['id'=>$id])->asArray()->one();
                $model->op_id = $id;
                return $this->render('new_choice', ['project'=>$project, 'operation'=>$operation, 'model'=>$model]);
            }
            return $this->redirect(['projects/setting?id='.$project['id']]);
        }
        return $this->redirect(['projects/index']);
    }

    public function actionSite_edit()
    {
        $session = Yii::$app->session;
        $session->open();

        if(isset($session['project']))
        {
            $project = $session['project'];
            $model = new \app\models\PcUserProjects();
            $model->project_id = $project['id'];
            
            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    $area = $model->area;
                    $permission = $model->site_editable;
                    if($area == -1)
                    {
                        if(\app\models\PcUserProjects::updateAll(['site_editable'=>$permission]))
                        {
                            \app\components\PdcpHelper::setUserProjectSession();
                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('success', 'عملیات با خطا مواجه شد.');
                    }
                    else
                    {
                        if(\app\models\PcUserProjects::updateAll(['site_editable'=>$permission],['area'=>$area]))
                        {
                            \app\components\PdcpHelper::setUserProjectSession();
                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('success', 'عملیات با خطا مواجه شد.');
                    }
                }
                else
                    Yii::$app->session->setFlash('error', 'دریافت اطلاعات با خطا مواجه شد.');

                return $this->redirect(['projects/project_users?id='.$project['id']]);
            }

            $areas = [-1=>"تمام مناطق", 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];

            return $this->render('site_edit', ['project'=>$project,'model'=>$model, 'areas'=>$areas]);
        }

        return $this->redirect(['projects/index']);
    }

    public function actionEdit_choice($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            if($id > 0)
            {
                $model = \app\models\PcChoices::find()->where(['id'=>$id])->one();
                $op_id = $model->op_id;
                $operation = \app\models\PcOperations::find()->where(['id'=>$op_id, 'project_id'=>$project['id']])->asArray()->one();

                if(!empty($model) && ($operation['type_id'] == 1))
                {
                    return $this->render('edit_choice', ['project'=>$project, 'operation'=>$operation, 'model'=>$model]);
                }
            }
            return $this->redirect(['projects/setting?id='.$project['id']]);
        }
        return $this->redirect(['projects/index']);
    }

    public function actionEditchoice()
    {
        if(Yii::$app->request->isPost)
        {
            $session = Yii::$app->session;
            $session->open();

            $id = Yii::$app->request->post()['PcChoices']['id'];
            $model = \app\models\PcChoices::findOne($id);
            if(!empty($model))
            {
                $old_weight = $model->choice_weight;

                if($model->load(Yii::$app->request->post()))
                {
                    if($model->update())
                    {
                        // check if set to default
                        $default = $model->default;
                        if($default == true)
                        {
                            //clear other default
                            $op_id = $model->op_id;
                            $chq = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$op_id])->andWhere(['not',['id'=>$id]]);
                            \app\models\PcChoices::updateAll(['default'=>false], ['in','id', $chq]);

                            //update all exchanges that have null value for this operation
                            $records = \app\models\PcViewRecords::find()->select('id')->where(['op_id'=>$model->op_id, 'op_value'=>null]);
                            \app\models\PcRecords::updateAll(['op_value'=>$model->id], ['in','id', $records]);
                        }

                        //update weights
                        $weight = $model->choice_weight;
                        if( ($weight > 0) || ($weight != $old_weight) )
                        {
                            $exchanges = \app\models\PcViewRecords::find()->select('exchange_id')->where(['op_id'=>$model->op_id, 'op_value'=>$model->id])->asArray()->all();
                            foreach ($exchanges as $exchange)
                            {
                                $exch = \app\models\PcExchanges::findOne($exchange['exchange_id']);
                                $exch->weight = \app\components\PdcpHelper::getWeight($exch->project_id , $exch->id);
                                $exch->update(false);
                            }
                        }


                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'بروز رسانی اطلاعات با خطا مواجه شد.');
                }
                return $this->redirect(['projects/edit_op_choices?id='.$model->op_id]);
            }
        }

        return $this->redirect(['projects/index']);
    }

    public function actionRemove_choice($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            if($id > 0)
            {
                $model = \app\models\PcChoices::findOne($id);
                $opId = -1;
                if(!empty($model)) $opId = $model->op_id;
                //check records
                $recs = \app\models\PcViewRecords::find()->select('count(id)')->where(['project_id'=>$project['id'],'op_id'=>$opId,'op_type'=>1, 'op_value'=>$id ])->scalar();
                if($recs == 0)
                {
                    $op = \app\models\PcOperations::find()->select('operation')->where(['id'=>$model->op_id])->scalar();
                    $ch = $model->choice;
                    if($model->delete())
                    {
                        try{
                        //log
                        $log = new \app\models\PcLogs();
                        $log->user_id = $session['user']['id'];
                        $log->exchange_id = null;
                        $log->action = " حذف آیتم انتخابی برای ویژگی ".$op." - ".$ch ;
                        $log->project_id = $project['id'];
                        $log->ts = time();
                        $log->save();
                        }
                        catch (\Exception $e){}

                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                }
                else
                    Yii::$app->session->setFlash('error', 'این ویژگی در مراکز و سایت ها دارای مقدار می باشد. برای حذف این ویژگی لازم است مقادیر تمامی رکوردها حذف گردد.');
            }
            return $this->redirect(['projects/edit_op_choices?id='.$opId]);
        }
        return $this->redirect(['projects/index']);
    }

    public function actionEdit_project_op($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            if ($id > 0)
            {
                $model = \app\models\PcOperations::findOne($id);
                $opType = \app\models\PcOpType::find()->asArray()->all();
                $array= [];
                foreach ($opType as $t)
                {
                    $array[$t['id']] = $t['description'];
                }
                $opType = $array;

                return $this->render('edit_op', ['project'=>$project,'model'=>$model, 'operation'=>$project, 'opType'=>$opType]);

            }
            return $this->redirect(['projects/setting?id='.$project['id']]);
        }

        return $this->redirect(['projects/index']);
    }

    public function actionEditop()
    {
        if(Yii::$app->request->isPost)
        {
            $session = Yii::$app->session;
            $session->open();

            $id = Yii::$app->request->post()['PcOperations']['id'];
            $model = \app\models\PcOperations::findOne($id);
            $weight0 = $model->op_weight;
            if(!empty($model))
            {
                $old_type_id = $model->type_id;
                if($model->load(Yii::$app->request->post()))
                {
                    if($model->update())
                    {
                        $weight = $model->op_weight;
                        if(($old_type_id == 1) && ($model->type_id > 1) )
                        {
                            // remove from choices
                            $choices = \app\models\PcChoices::deleteAll(['op_id'=>$model->id]);
                        }

                        // update choice weight
                        if($model->type_id == 1)
                        {
                            $chq = \app\models\PcChoices::find()->select('id')->where(['op_id'=>$model->id])->andWhere(['>','choice_weight', $weight]);
                            \app\models\PcChoices::updateAll(['choice_weight'=>$weight], ['in','id', $chq]);
                        }

                        //update project weights
                        $project = \app\models\PcProjects::findOne($model->project_id);
                        $weight = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$model->project_id])->scalar();
                        $project->project_weight = $weight;
                        $project->update();

                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'بروز رسانی اطلاعات با خطا مواجه شد.');
                }
                return $this->redirect(['projects/setting?id='.$model->project_id]);
            }
        }

        return $this->redirect(['projects/index']);
    }

    public function actionRemove_project_op($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            if($id > 0)
            {
                $model = \app\models\PcOperations::findOne($id);
                $weight = $model->op_weight;
                //check type
                if($model->type_id == 1)
                {
                    //remove if all records with this operation has null value
                    $cnt = \app\models\PcViewRecords::find()->select('count(id)')->where(['project_id'=>$project['id'],'op_id'=>$id])->andWhere(['not', ['op_value'=>null]])->scalar();
                    if($cnt == 0)
                    {
                        //can remove all --> all is null
                        \app\models\PcRecords::deleteAll(['project_id'=>$project['id'], 'op_id'=>$id]);
                        \app\models\PcChoices::deleteAll(['op_id'=>$id]);
                        $pid = $model->project_id;
                        if($model->delete())
                        {
                            if($weight > 0)
                            {
                                $project = \app\models\PcProjects::findOne($pid);
                                $weight = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$pid])->scalar();
                                $project->project_weight = $weight;
                                $project->update(false);
                            }
                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                    }
                    else
                    {
                        //cannot remove
                        Yii::$app->session->setFlash('error', 'این ویژگی در مراکز و سایت ها دارای مقدار می باشد. برای حذف این ویژگی لازم است مقادیر تمامی رکوردها حذف گردد.');
                    }
                }
                else if($model->type_id == 2)
                {
                    //text
                    //remove if all records with this operation has empty value
                    $cnt = \app\models\PcViewRecords::find()->select('count(id)')->where(['project_id'=>$project['id'],'op_id'=>$id])->andWhere(['not',['op_value'=>""]])->andWhere(['not',['op_value'=>null]])->scalar();
                    if($cnt == 0)
                    {
                        //can remove all
                        \app\models\PcRecords::deleteAll(['project_id'=>$project['id'], 'op_id'=>$id]);
                        $pid = $model->project_id;
                        if($model->delete())
                        {
                            if($weight > 0)
                            {
                                $project = \app\models\PcProjects::findOne($pid);
                                $weight = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$pid])->scalar();
                                $project->project_weight = $weight;
                                $project->update(false);
                            }
                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                    }
                    else
                    {
                        //cannot remove
                        Yii::$app->session->setFlash('error', 'این ویژگی در مراکز و سایت ها دارای مقدار می باشد. برای حذف این ویژگی لازم است مقادیر تمامی رکوردها حذف گردد.');
                    }
                }
                else if($model->type_id == 3)
                {
                    //remove if all records with this operation has 0 value
                    $cnt = \app\models\PcViewRecords::find()->select('count(id)')->where(['project_id'=>$project['id'],'op_id'=>$id])->andWhere(['>','op_value', 0])->andWhere(['not',['op_value'=>null]])->scalar();
                    if($cnt == 0)
                    {
                        //can remove all
                        \app\models\PcRecords::deleteAll(['project_id'=>$project['id'], 'op_id'=>$id]);
                        $pid = $model->project_id;
                        if($model->delete())
                        {
                            if($weight > 0)
                            {
                                $project = \app\models\PcProjects::findOne($pid);
                                $weight = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$pid])->scalar();
                                $project->project_weight = $weight;
                                $project->update(false);
                            }
                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                    }
                    else
                    {
                        //cannot remove
                        Yii::$app->session->setFlash('error', 'این ویژگی در مراکز و سایت ها دارای مقدار می باشد. برای حذف این ویژگی لازم است مقادیر تمامی رکوردها حذف گردد.');
                    }
                }
                else if($model->type_id == 4)
                {
                    //remove if all records with this operation has 0->ts value
                    $cnt = \app\models\PcViewRecords::find()->select('count(id)')->where(['project_id'=>$project['id'],'op_id'=>$id])->andWhere(['>','op_value', 0])->andWhere(['not','op_value'=>null])->scalar();
                    if($cnt == 0)
                    {
                        //can remove all
                        \app\models\PcRecords::deleteAll(['project_id'=>$project['id'], 'op_id'=>$id]);
                        $pid = $model->project_id;
                        if($model->delete())
                        {
                            if($weight > 0)
                            {
                                $project = \app\models\PcProjects::findOne($pid);
                                $weight = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$pid])->scalar();
                                $project->project_weight = $weight;
                                $project->update(false);
                            }
                            Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                        }
                        else
                            Yii::$app->session->setFlash('error', 'انجام عملیات با خطا مواجه شد.');
                    }
                    else
                    {
                        //cannot remove
                        Yii::$app->session->setFlash('error', 'این ویژگی در مراکز و سایت ها دارای مقدار می باشد. برای حذف این ویژگی لازم است مقادیر تمامی رکوردها حذف گردد.');
                    }
                }
            }
            return $this->redirect(['projects/setting?id='.$project['id']]);
        }
        return $this->redirect(['projects/index']);
    }

    //users

    public function actionUsers()
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if($user['admin'] == 1)
        {
            $this->layout = "main";
            $searchModel = new \app\models\PcUsersSearch();
            $params = Yii::$app->request->queryParams;
            if ($params)
            {
                $dataProvider = $searchModel->search($params);
            }
            else
            {
                $qry = \app\models\PcUsers::find()->asArray()->orderBy("office,lastname, name");
                $dataProvider = new \yii\data\ActiveDataProvider(['query' => $qry]);
            }
            $dataProvider->pagination->pageSize = 25;

            return $this->render("users", ['dataProvider'=>$dataProvider, 'searchModel'=>$searchModel]);
        }
        else
            return $this->redirect(['main/logout']);
    }

    public function actionUser_new()
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if($user['admin'] == 1)
        {
            $model = new \app\models\PcUsers();

            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    $model->password = md5($model->password);
                    $model->passwordConfirm = $model->password;
                    if($model->save())
                    {
                        Yii::$app->session->setFlash('success', 'کاربر جدید با موفقیت ایجاد شد.');
                        return $this->redirect(['projects/users']);
                    }
                    else
                        Yii::$app->session->setFlash('error', 'ایجاد کاربر جدید با خطا مواجه شد.');
                }
            }

            return $this->render('user_new',['model'=>$model]);
        }

        return $this->redirect(['main/logout']);
    }

    public function actionUser_edit($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if( ($user['admin'] == 1) && ($id > 0) )
        {
            $model = \app\models\PcUsers::find()->where(['id'=>$id])->one();
            $model->passwordConfirm = $model->password;

            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    $id = $model->id;
                    $oldPass = \app\models\PcUsers::find()->select('password')->where(['id'=>$id])->scalar();
                    if($oldPass != $model->password)
                    {
                        $model->password = md5($model->password);
                        $model->reset_password = true;
                    }
                    $model->passwordConfirm = $model->password;
                    if($model->update(false))
                    {
                        Yii::$app->session->setFlash('success', 'ویرایش کاربر با موفقیت انجام شد.');
                        \app\components\PdcpHelper::setUserSession();
                        return $this->redirect(['projects/users']);
                    }
                    else
                        Yii::$app->session->setFlash('error', 'ویرایش کاربر با خطا مواجه شد.');
                }
            }

            return $this->render('user_edit',['model'=>$model]);
        }

        return $this->redirect(['main/logout']);
    }

    public function actionUser_remove($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if($user['admin'] == 1)
        {
            if(Yii::$app->request->isPost)
            {
                $id = Yii::$app->request->post()['PcUsers']['id'];
                $model = \app\models\PcUsers::findOne($id);
                if($model->delete())
                {
                    Yii::$app->session->setFlash('success', 'کاربر با موفقیت حذف گردید.');
                }
                else
                {
                    Yii::$app->session->setFlash('error', 'حذف کاربر با خطا مواجه شد.');
                }

                return $this->redirect(['projects/users']);
            }

            $model = \app\models\PcUsers::find()->where(['id'=>$id])->one();
            return $this->render('user_remove',['model'=>$model]);
        }

        return $this->redirect(['main/logout']);
    }

}
