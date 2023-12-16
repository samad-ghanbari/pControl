<?php

namespace app\controllers;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Scalar;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use yii\web\UploadedFile;

class ImportController extends \yii\web\Controller
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

    public function actionIndex()
    {
        $projects = "";
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $user = $session['user'];
            $projects = \app\models\PcViewUserProjects::find()->select('project_id,project')->where(['user_id'=>$user['id'],'project_enabled'=>true])->asArray()->all();
            return $this->render("index", ['projects'=>$projects]);
        }

        return $this->redirect(['main/login']);
    }

    public function actionExport_centers()
    {
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>20, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
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


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        //topic
        $sheet->mergeCells('A1:D1');
        $sheet->getRowDimension('1')->setRowHeight(50);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "P D C P");
        //header
        $row = 2;
        $sheet->getRowDimension($row)->setRowHeight(40);

        $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A'.$row, 'منطقه');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('B'.$row, 'مرکز');
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getStyle('C'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('C'.$row, 'اختصار');
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getStyle('D'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('D'.$row, 'آدرس');
        $sheet->getColumnDimension('D')->setWidth(50);

        $row++;
        //fetch data
        $models = \app\models\PcExchanges::find()->select(['area','name', 'abbr', 'address'])->where(['type'=>2])->distinct()->orderBy('area', 'name')->all();
        foreach ($models as $model)
        {
            $sheet->getRowDimension($row)->setRowHeight(50);
            $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('A'.$row, $model->area);
            $sheet->setCellValue('B'.$row, $model->name);
            $sheet->setCellValue('C'.$row, $model->abbr);
            $sheet->setCellValue('D'.$row, $model->address);
            $row++;
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Centers_List'.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    private function checkXlsColumns($spreadsheet)
    {
        /* @var $spreadsheet  Spreadsheet */
        //A2 -> area
        $text = $spreadsheet->getActiveSheet()->getCell("A2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "area")
        {
            Yii::$app->session->setFlash("error", "ستون اول فایل اکسل ورودی area نمی باشد.");
            return false;
        }

        //B2 -> center
        $text = $spreadsheet->getActiveSheet()->getCell("B2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "center")
        {
            Yii::$app->session->setFlash("error", "ستون دوم فایل اکسل ورودی center نمی باشد.");
            return false;
        }

        //C2 -> site id
        $text = $spreadsheet->getActiveSheet()->getCell("C2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "site id")
        {
            Yii::$app->session->setFlash("error", "ستون سوم فایل اکسل ورودی site id نمی باشد.");
            return false;
        }

        //D2 -> kv code
        $text = $spreadsheet->getActiveSheet()->getCell("D2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "kv code")
        {
            Yii::$app->session->setFlash("error", "ستون چهارم فایل اکسل ورودی kv code نمی باشد.");
            return false;
        }

        //E2 -> address
        $text = $spreadsheet->getActiveSheet()->getCell("E2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "address")
        {
            Yii::$app->session->setFlash("error", "ستون پنجم فایل اکسل ورودی address نمی باشد.");
            return false;
        }

        //F2 -> position
        $text = $spreadsheet->getActiveSheet()->getCell("F2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "position")
        {
            Yii::$app->session->setFlash("error", "ستون ششم فایل اکسل ورودی position نمی باشد.");
            return false;
        }

        //G2 -> phase
        $text = $spreadsheet->getActiveSheet()->getCell("G2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "phase")
        {
            Yii::$app->session->setFlash("error", "ستون هفتم فایل اکسل ورودی phase نمی باشد.");
            return false;
        }

        //H2 -> phase
        $text = $spreadsheet->getActiveSheet()->getCell("H2")->getValue();
        $text = strtolower($text);
        $text = trim($text, " ");
        if($text != "lom")
        {
            Yii::$app->session->setFlash("error", "ستون هشتم فایل اکسل ورودی  LOM نمی باشد.");
            return false;
        }

        return true;
    }

    private function getSpreadsheetValue($spreadsheet, $row, $columnTitle)
    {
        /* @var $spreadsheet  Spreadsheet */
        $col = "";
        if($columnTitle == "area")
            $col = "A";
        else if($columnTitle == "center")
            $col = "B";
        else if($columnTitle == "site_id")
            $col = "C";
        else if($columnTitle == "kv_code")
            $col = "D";
        else if($columnTitle == "address")
            $col = "E";
        else if($columnTitle == "position")
            $col = "F";
        else if($columnTitle == "phase")
            $col = "G";
        else if($columnTitle == "lom")
            $col = "H";
        else
            return "";

        $cell = $col.$row;
        $value = $spreadsheet->getActiveSheet()->getCell($cell)->getValue();
        return $value;
    }

    private function xlsToModelsArray($spreadsheet, $project_id)
    {
        /* @var $spreadsheet  Spreadsheet */
        $array = [];
        $maxRow = $spreadsheet->getActiveSheet()->getHighestRow();
        for($row = 3; $row <= $maxRow; $row++)
        {
            $model = new \app\models\XlsModel();
            $model->area = self::getSpreadsheetValue($spreadsheet,$row,"area");
            $model->type = 3;
            $model->center = (string)self::getSpreadsheetValue($spreadsheet,$row,"center");
            $model->center_id = self::getCenterId($project_id, $model->center);
            $model->site_id = (string)self::getSpreadsheetValue($spreadsheet,$row,"site_id");
            $model->kv_code = (string)self::getSpreadsheetValue($spreadsheet,$row,"kv_code");
            $center_abbr = \app\models\PcExchanges::find()->select("abbr")->where(['id'=>$model->center_id])->scalar();
            $kc = $model->kv_code; $si = $model->site_id;
            $n = $kc;
            if(strlen($kc) > strlen($si))
                $n = $si;
            $model->name = $center_abbr.'-'.$n;
            $model->abbr =$n;
            $model->address = (string)self::getSpreadsheetValue($spreadsheet,$row,"address");
            $model->position = (string)self::getSpreadsheetValue($spreadsheet,$row,"position");
            $model->phase = self::getSpreadsheetValue($spreadsheet,$row,"phase");
            $lom = self::getSpreadsheetValue($spreadsheet,$row,"lom");
            $lom = trim($lom," ");
            $model->lom = $lom;
            $model->done = false;
            array_push($array, $model);
        }

        return $array;
    }

    private function getCenterId($project_id, $center)
    {
        $center = str_replace("شهید", "", $center);
        $center = str_replace("مرکز", "",$center);
        $center = str_replace("مرحوم", "",$center);
        $center = trim($center);

        $cId = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>$project_id, 'name'=>$center])->scalar();
        $cId = $cId * 1;
        return $cId;
    }

    private function checkPermission($modelsArray, $permission)
    {
        $area = $permission['area'];
        $exchange_id = $permission['exchange_id'];
        $rw = $permission['rw'];
        if($rw == 0)
        {
            Yii::$app->session->setFlash("error", "شما مجوز ورود اطلاعات ندارید.");
            return false;
        }

        if($area > 1)
        {
            foreach($modelsArray as $model)
            {
                /* @var $model \app\models\XlsModel*/
                $value = $model->area;
                if($area != $value)
                {
                    Yii::$app->session->setFlash("error", "شما مجوز ورود اطلاعات منطقه دیگر را ندارید.");
                    return false;
                }
            }

            if($exchange_id > 0)
            {
                foreach($modelsArray as $model)
                {
                    /* @var $model \app\models\XlsModel*/
                    $value = $model->center_id;
                    if($exchange_id != $value)
                    {
                        Yii::$app->session->setFlash("error", "شما مجوز ورود اطلاعات مرکز دیگر را ندارید.");
                        return false;
                    }
                }

                return true;
            }
            else return true;
        }
        else return true;
    }

    private function checkCenterIdExistence($modelsArray)
    {
        foreach ($modelsArray as $model)
        {
            /* @var $model \app\models\XlsModel*/
            $center_id = $model->center_id;
            if($center_id > 0)
                continue;
            else
            {
                $name = $model->name;
                $center = $model->center;
                Yii::$app->session->setFlash("error", "برای $name نام مرکز اصلی $center به درستی وارد نگردیده است. ");
                return false;
            }
        }
        return true;
    }

    private function checkDuplicate($modelsArray, $project_id)
    {
        $ext = \app\models\PcExchanges::find()->select('area,center_id,name,site_id,kv_code')->where(['project_id'=>$project_id])->orderBy('area,center_id')->asArray()->all();
        $array = [];
        foreach ($ext as $rec)
        {
            $site_id = [];
            $kv_code = [];
            $name = [];
            if(isset($array[$rec['area']][$rec['center_id']]['site_id']))
                $site_id = $array[$rec['area']][$rec['center_id']]['site_id'];

            if(isset($array[$rec['area']][$rec['center_id']]['kv_code']))
                $kv_code = $array[$rec['area']][$rec['center_id']]['kv-code'];

            if(isset($array[$rec['area']][$rec['center_id']]['name']))
                $name = $array[$rec['area']][$rec['center_id']]['name'];

            array_push($site_id, $rec['site_id']);
            array_push($kv_code, $rec['kv_code']);
            array_push($name, $rec['name']);

            $array[$rec['area']][$rec['center_id']]['site_id'] = $site_id;
            $array[$rec['area']][$rec['center_id']]['kv-code'] = $kv_code;
            $array[$rec['area']][$rec['center_id']]['name'] = $name;
        }

        foreach ($modelsArray as $model)
        {
            /* @var $model \app\models\XlsModel*/
            $area = $model->area;
            $center_id = $model->center_id;
            $site_id = $model->site_id;
            $kv_code = $model->kv_code;
            $name = $model->name;

            $siteArray = [];
            $kvArray = [];
            $nameArray = [];
            if(isset($array[$area][$center_id]['site_id']))
                $siteArray = $array[$area][$center_id]['site_id'];
            if(isset($array[$area][$center_id]['kv_code']))
                $kvArray = $array[$area][$center_id]['kv_code'];
            if(isset($array[$area][$center_id]['name']))
                $nameArray = $array[$area][$center_id]['name'];

            if(in_array($site_id, $siteArray))
            {
                Yii::$app->session->setFlash('error', " رکورد با شناسه سایت $site_id برای مرکز مربوطه تکراری می باشد.  ");
                return false;
            }
            if(in_array($kv_code, $kvArray))
            {
                Yii::$app->session->setFlash('error', " رکورد با کد کافو $kv_code برای مرکز مربوطه تکراری می باشد.  ");
                return false;
            }
            if(in_array($name, $nameArray))
            {
                Yii::$app->session->setFlash('error', " رکورد با نام سایت $name برای مرکز مربوطه تکراری می باشد.  ");
                return false;
            }
        }

        return true;
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

    private function addExchange($user, $project_id, $mdl)
    {
        /* @var $model \app\models\XlsModel */
        //site
        $model = new \app\models\PcExchanges();
        $model->project_id = $project_id;
        $model->area = $mdl->area;
        $model->name = (string)$mdl->name;
        $model->abbr = (string)$mdl->abbr;
        $model->type = $mdl->type;
        $model->center_id = $mdl->center_id;
        $model->site_id = (string)$mdl->site_id;
        $model->kv_code = (string)$mdl->kv_code;
        $model->address = (string)$mdl->address;
        $model->position = (string)$mdl->position;
        $model->phase = $mdl->phase;

        $model->modifier_id = $user['id'];
        $model->modified_ts = time();
        $model->register_ts = time();
        //save
        if($model->save())
        {
            //save operations
            $this->saveOperations($model->id, $project_id);
            //update weight
            $weight = \app\components\PdcpHelper::getWeight($model->project_id , $model->id);
            $model->weight = $weight;
            $model->update();
            // insert lom items
            $loms = $mdl->lom;
            // [id1=>cnt1, id2=>cnt2 ...]
            foreach ($loms as $id=>$quantity)
            {
                $lomDetail = new \app\models\PcLomDetail();
                $lomDetail->exchange_id = $model->id;
                $lomDetail->lom_id = $id;
                $lomDetail->quantity = $quantity;
                $lomDetail->save(false);
            }


            return true;
        }
        else
            return false;
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

    public function xlsAreaCount($modelsArray)
    {
        $array = [];

        foreach ($modelsArray as $rec)
        {
            $area = $rec['area'];
            if(isset($array["$area"]))
                $array["$area"] = $array["$area"] + 1;
            else
                $array["$area"] = 1;
        }

        return $array;
    }

    public function projectAreaRecord($projectId)
    {
        $array = ['area2' => 0, 'area3' => 0,  'area4' => 0, 'area5' => 0, 'area6' => 0, 'area7' => 0, 'area8' => 0];

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>2])->scalar();
        $array['area2'] = $available*1;

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>3])->scalar();
        $array['area3'] = $available*1;

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>4])->scalar();
        $array['area4'] = $available*1;

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>5])->scalar();
        $array['area5'] = $available*1;

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>6])->scalar();
        $array['area6'] = $available*1;

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>7])->scalar();
        $array['area7'] = $available*1;

        $available = \app\models\PcExchanges::find()->select("COUNT(*)")->where(['project_id'=>$projectId, 'area'=>8])->scalar();
        $array['area8'] = $available*1;


        return $array;
    }

    private function getlomId($lom, $project_id)
    {
        $id = \app\models\PcLom::find()->where(['equipment'=>$lom , 'project_id'=>$project_id])->scalar();
        if($id > 0) return $id;
        else return -1;
    }

    public function actionExport_lom()
    {
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $project_id = $post['projectCB'];
            $models = []; // [  id=>[equip, desc] , ...  ] ]
            $loms = \app\models\PcLom::find()->where(['project_id'=>$project_id])->asArray()->all();
            foreach ($loms as $lom)
            {
                $models[$lom['id']][0] = $lom['equipment'];
                $models[$lom['id']][1] = $lom['description'];
            }

            $project = \app\models\PcProjects::findOne($project_id);
            $project = $project->project;
            $this->export_lom_xls($models, $project);
        }
        return $this->redirect(['import/index']);
    }

    private function export_lom_xls0($models, $project)
    { // [  id=>[equipment, description, area2=>[dedication, used] , ...  ] ]
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>20, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
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


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        //topic
        $sheet->mergeCells('A1:D1');
        $sheet->getRowDimension('1')->setRowHeight(50);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "P D C P");

        $sheet->mergeCells('A2:D2');
        $sheet->getRowDimension('2')->setRowHeight(50);
        $sheet->getStyle('A2')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A2', $project);

        //header
        $row = 3;
        $sheet->getRowDimension($row)->setRowHeight(40);

        $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A'.$row, 'تجهیز');
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('B'.$row, 'توضیحات');
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getStyle('C'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('C'.$row, 'منطقه ۲'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getStyle('D'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('D'.$row, 'منطقه ۳'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('E'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('E'.$row, 'منطقه ۴'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getStyle('F'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('F'.$row, 'منطقه ۵'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('F')->setWidth(40);
        $sheet->getStyle('G'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('G'.$row, 'منطقه ۶'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getStyle('H'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('H'.$row, 'منطقه ۷'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('H')->setWidth(40);
        $sheet->getStyle('I'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('I'.$row, 'منطقه ۸'."\n".'کل / مصرفی');
        $sheet->getColumnDimension('I')->setWidth(40);


        $row++;
        foreach ($models as $model)
        {// [  id=>[equipment, description, area2=>[dedication, used] , ...  ] ]
            $sheet->getRowDimension($row)->setRowHeight(50);
            $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('A'.$row, $model['equipment']);
            $sheet->setCellValue('B'.$row, $model['description']);
            $sheet->setCellValue('C'.$row, $model['area2'][1].' / '.$model['area2'][0]);
            $sheet->setCellValue('D'.$row, $model['area3'][1].' / '.$model['area3'][0]);
            $sheet->setCellValue('E'.$row, $model['area4'][1].' / '.$model['area4'][0]);
            $sheet->setCellValue('F'.$row, $model['area5'][1].' / '.$model['area5'][0]);
            $sheet->setCellValue('G'.$row, $model['area6'][1].' / '.$model['area6'][0]);
            $sheet->setCellValue('H'.$row, $model['area7'][1].' / '.$model['area7'][0]);
            $sheet->setCellValue('I'.$row, $model['area8'][1].' / '.$model['area8'][0]);
            $row++;
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Project-LOM'.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    private function export_lom_xls($models, $project)
    { // [  id=>[equipment, description, area2=>[dedication, used] , ...  ] ]
        $TopicStyle =
            [
                'font' => ['bold' => true,'size'=>20, 'color' => ['rgb' => 'ffffff'],'name'=>"Tahoma"],
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


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        //topic
        $sheet->mergeCells('A1:B1');
        $sheet->getRowDimension('1')->setRowHeight(50);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "P D C P");

        $sheet->mergeCells('A2:B2');
        $sheet->getRowDimension('2')->setRowHeight(50);
        $sheet->getStyle('A2')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A2', $project);

        //header
        $row = 3;
        $sheet->getRowDimension($row)->setRowHeight(50);

        $sheet->getStyle('A'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A'.$row, 'تجهیز');
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getStyle('B'.$row)->applyFromArray($HeaderStyle);
        $sheet->setCellValue('B'.$row, 'توضیحات');
        $sheet->getColumnDimension('B')->setWidth(50);
        $row++;
        foreach ($models as $id=>$item)
        {// [  id=>[equipment, description] , ...  ] ]
            $sheet->getRowDimension($row)->setRowHeight(50);
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('A'.$row, $item[0]);
            $sheet->setCellValue('B'.$row, $item[1]);
            $row++;
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Project-LOM'.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    private function parseLom($modelsArray, $project_id) // lom : 1*5616, 2*ADPE    MA5616-A
    {
        $ok = true;
        $error = "";
        foreach ($modelsArray as $model)
        {
            $lom = $model->lom;
            if(empty($lom)){$ok = false; break;}
            $ARRAY = [];
            if(str_contains($lom,'*'))
            {
                //1*5616,2*ADPE
                $lomArray = explode(',', $lom);
                foreach($lomArray as $item)
                {
                    [$amount, $equip] = explode('*', $item);
                    $amount = $amount * 1;
                    if($amount <= 0)
                        continue;
                    $id = self::getLomId($equip, $project_id);
                    if($id == -1){$ok = false; $error = "Area: ".$model->area." name: ".$model->name; break;}
                    $ARRAY[$id] = $amount;
                }
            }
            else
            {
                //MA5616-A
                $amount = 1;
                $id = self::getlomId($lom, $project_id);
                if($id == -1) {$ok = false; $error = "Area: ".$model->area." name: ".$model->name; break;}
                $ARRAY[$id] = $amount;
            }
            $model->lom = $ARRAY;

            if($ok == false) break;
        }
        return [$ok, $modelsArray, $error];
    }

    private function  dedicationAcceptible($modelsArray, $project_id)
    { // [true/false,  error]
        // [ LOMID=>[AREA2=>123, AREA3=>345]   ]
        $dedicatedArray = [];
        $usedArray = [];
        $requiredArray = [];
        // dedicated
        $loms = \app\models\PcLom::find()->where(['project_id'=>$project_id])->asArray()->all();
        foreach ($loms as $lom)
        {
            $dedicatedArray[$lom['id']][2] = $lom['area2'];
            $dedicatedArray[$lom['id']][3] = $lom['area3'];
            $dedicatedArray[$lom['id']][4] = $lom['area4'];
            $dedicatedArray[$lom['id']][5] = $lom['area5'];
            $dedicatedArray[$lom['id']][6] = $lom['area6'];
            $dedicatedArray[$lom['id']][7] = $lom['area7'];
            $dedicatedArray[$lom['id']][8] = $lom['area8'];
        }
        // used
        foreach ($loms as $lom)
        {
            $usedArray[$lom['id']][2] = 0;
            $usedArray[$lom['id']][3] = 0;
            $usedArray[$lom['id']][4] = 0;
            $usedArray[$lom['id']][5] = 0;
            $usedArray[$lom['id']][6] = 0;
            $usedArray[$lom['id']][7] = 0;
            $usedArray[$lom['id']][8] = 0;
        }
        $loms = \app\models\PcViewLomDetail::find()->select("lom_id, area, SUM(quantity) as sum")->where(['project_id'=>$project_id])->groupBy("area, lom_id")->asArray()->all();
        foreach($loms as $lom)
        {
            $usedArray[$lom['lom_id']][$lom['area']] = $lom['sum'];
        }

        // required
        foreach ($modelsArray as $model)
        {
            $area = $model->area;
            $lom = $model->lom; // [lomid1=>amount1 , lomid2=>amount2, ]
            foreach ($lom as $id=>$cnt)
            {
                if(isset($requiredArray[$id][$area]))
                    $requiredArray[$id][$area] = $requiredArray[$id][$area] + $cnt;
                else
                    $requiredArray[$id][$area] = $cnt;
            }
        }

        $ok = true;
        $error = "";
        foreach ($requiredArray as $id=>$array)
        {
            foreach ($array as $AREA=>$COUNT)
            {
                $left = $dedicatedArray[$id][$AREA] - $usedArray[$id][$AREA];
                if($COUNT > $left)
                {
                    $ok = false;
                    $error = "برآورد تجهیزات از تعداد تخصیصی منطقه ".$AREA." فراتر می‌باشد. ";
                    break;
                }
            }
            if($ok == false) break;
        }

        return [$ok, $error];
    }

    public function actionParser()
    {
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $project_id = $post['projectCB'];
            $fileName = $_FILES['fileUpload']['name'];
            $fileName = strtolower($fileName);
            if(str_ends_with($fileName,".xls"))
            {
                $file = $_FILES['fileUpload']['tmp_name'];
                $reader = IOFactory::createReaderForFile($file);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file);
                $spreadsheet->getSheet(0);
                //check xls columns standard
                if(self::checkXlsColumns($spreadsheet))
                {
                    //COLUMNS ARE OK
                    $modelsArray = self::xlsToModelsArray($spreadsheet,$project_id);
                    $temp = self::parseLom($modelsArray, $project_id);

                    $modelsArray = $temp[1];
                    if($temp[0] ==  false)
                    {
                        Yii::$app->session->setFlash("error", "اطلاعات ستون LOM به درستی وارد نگردیده است."."\n".$temp[2]);
                        return $this->redirect(['import/index']);
                    }

                    //check dedication and amount
                    [$ok, $error] = self::dedicationAcceptible($modelsArray, $project_id);
                    if($ok == false)
                    {
                        Yii::$app->session->setFlash("error", $error);
                        return $this->redirect(['import/index']);
                    }

                    // check all center ids are available
                    if(!self::checkCenterIdExistence($modelsArray))
                        return $this->redirect(['import/index']);

                    //check area center permissions
                    $session = Yii::$app->session;
                    $session->open();
                    if(!isset($session['userProjects'])) return $this->redirect(['main/login']);
                    if(!isset($session['user'])) return $this->redirect(['main/login']);
                    $user = $session['user'];
                    $userProjects = $session['userProjects']; //area - exchange_id  - rw
                    $permission = $userProjects[$project_id];
                    if(!self::checkPermission($modelsArray, $permission))
                        return $this->redirect(['import/index']);

                    //check duplicate entries
                    if(!self::checkDuplicate($modelsArray, $project_id))
                        return $this->redirect(['import/index']);

                    // ready to import
                    // check operations default
                    $defaults = $this->checkOperationsDefault($project_id);
                    if(!$defaults['pass'])
                    {
                        Yii::$app->session->setFlash('error',' مقدار پیشفرض برای '.$defaults['value'].' موجود نیست. ');
                        return $this->redirect(['import/index']);
                    }

                    foreach ($modelsArray as $model)
                    {
                        if($this->addExchange($user, $project_id,  $model))
                            $model->done = true;
                        else
                        {
                            $model->done = false;
                            break;
                        }
                    }

                    $project = \app\models\PcProjects::find()->select('project')->where(['id'=>$project_id])->scalar();
                    return $this->render("import_res", ['models'=>$modelsArray, 'project'=>$project, 'project_id'=>$project_id]);
                }
                else
                    return $this->redirect(['import/index']);
            }
            Yii::$app->session->setFlash("error", "فایل ورودی بایستی با پسوند xls بارگذاری گردد.");
            return $this->redirect(['import/index']);
        }
        return $this->redirect(['import/index']);
    }



}
