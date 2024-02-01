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

class OwnerController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $session->open();
        if ((isset($session['owner'])))
        {
            if(sizeof($session['owner']) > 0)
                return parent::beforeAction($action);
            else
                return $this->redirect(["main/home"]);
        }
        else
        {
            return $this->redirect(["main/login"]);
        }
    }
    
    public function actionEdit_project($id=-1)
    {
        $session = Yii::$app->session;
        $session->open();
        $projects = $session['owner'];
        if(!in_array($id, $projects))
            $id = -1;

        $projects = \app\models\PcProjects::find()->where(['id'=>$projects])->orderBy(['ts'=>SORT_DESC])->asArray()->all();

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
                    
                return $this->redirect(['owner/edit_project', "id"=>$id]);
                
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
            $pId = $post['pId'] ;

            $session = Yii::$app->session;
            $session->open();
            $projects = $session['owner'];
            if(!in_array($pId, $projects))
                $pId = -1;
            
            
            if( $pId > -1 )
            {
                if($action == 'add')
                    return $this->add_equipment($post);
                
                return $this->redirect(['owner/edit_project?id='.$pId]);
            }
        }
        return $this->redirect(['owner/edit_project']);
    }

    private function add_equipment($post)
    {
        $pId = $post['pId'];

        $session = Yii::$app->session;
        $session->open();
        $projects = $session['owner'];
        if(!in_array($pId, $projects))
            $pId = -1;
        
        $project = \app\models\PcProjects::findOne($pId);
        $model = new \app\models\PcLom();
        $model->project_id = $pId;
        return $this->render("add_equip", ['project'=>$project, 'model'=>$model]);
    }

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

            $session = Yii::$app->session;
            $session->open();
            $projects = $session['owner'];
            if(!in_array($project_id, $projects))
                $project_id = -1;

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
                        return $this->redirect(['owner/edit_project?id='.$project_id]);

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
            return $this->redirect(['owner/edit_project?id='.$project_id]);
        }
        return $this->redirect(['owner/edit_project']);
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
                    return $this->redirect(['owner/edit_project', 'id'=>$model->project_id]);
            }
        }
        return $this->redirect(['owner/edit_project']);
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
                        return $this->redirect(['owner/edit_project', 'id'=>$model->project_id]);
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
                    return $this->redirect(['owner/edit_project', 'id'=>$pid]);
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
            return $this->redirect(['owner/edit_project']);
    }

    // users
    public function actionProject_users($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        $projects = $session['owner'];
        if(!in_array($id, $projects))
            $id = -1;

        $projects = \app\models\PcProjects::find()->where(['id'=>$projects])->orderBy(['ts'=>SORT_DESC])->asArray()->all();

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

                return $this->redirect(['owner/project_users?id='.$project['id']]);
            }

            $areas = [-1=>"تمام مناطق", 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];

            return $this->render('site_edit', ['project'=>$project,'model'=>$model, 'areas'=>$areas]);
        }

        return $this->redirect(['owner/project_users']);
    }

    public function actionNew_user()
    {
        $session = Yii::$app->session;
        $session->open();

        if(isset($session['project']))
        {
            $project = $session['project'];
            $projects_id = $session['owner'];
            if(!in_array($project['id'], $projects_id))
                return $this->redirect(['owner/edit_project']);

            
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

                return $this->redirect(['owner/project_users?id='.$project['id']]);
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

        return $this->redirect(['owner/project_users']);
    }

    public function actionEdit_user_project($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        
        if(isset($session['project']))
        {
            $project = $session['project'];
            $projects_id = $session['owner'];

            
            $model = \app\models\PcUserProjects::findOne($id);
            if(!in_array($model->project_id, $projects_id))
                return $this->redirect(['owner/edit_project']);

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

                return $this->redirect(['owner/project_users?id='.$project['id']]);
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

        return $this->redirect(['owner/edit_user_project']);
    }

    public function actionRemove_user_project($id = -1)
    { 
        $session = Yii::$app->session;
        $session->open();
        
        if(isset($session['project']))
        {
            $project = $session['project'];
            $projects_id = $session['owner'];            
            
            if(Yii::$app->request->isPost)
            {
                $id = Yii::$app->request->post()['PcViewUserProjects']['id'];
                $model = \app\models\PcUserProjects::findOne($id);
                if(!in_array($model->project_id, $projects_id))
                    return $this->redirect(['owner/edit_project']);
                else
                {
                    if($model->delete())
                    {
                        \app\components\PdcpHelper::setUserProjectSession();
                        Yii::$app->session->setFlash('success', 'عملیات با موفقیت انجام شد.');
                    }
                    else
                        Yii::$app->session->setFlash('error', 'عملیات با خطا انجام شد.');
                }
                
                return $this->redirect(['owner/project_users?id='.$project['id']]);
            }
            
            $view = \app\models\PcViewUserProjects::findOne($id);
            if(!in_array($view->project_id, $projects_id))
                 return $this->redirect(['owner/edit_project']);
            return $this->render('remove_user_project', ['model'=>$view]);
        }
        
        return $this->redirect(['owner/edit_user_project']);
    }


}
