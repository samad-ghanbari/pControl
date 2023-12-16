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

class ReportController extends \yii\web\Controller
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

    // total report
    public function actionTotal($id = -1)
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
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
            $project_id = $id;
            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            if(isset($userProjects[$project['id']]))
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

                if(empty($area)) $area = -1;
                if(empty($exchange_id)) $exchange_id = -1;


                return $this->render('total', [ 'exchanges'=>$exchanges, 'project'=>$project, 'projects'=>$projects, 'areaSelection'=>$areaSelection]);
            }
        }
        return $this->render('total', [ 'exchanges'=>[], 'project'=>[], 'projects'=>$projects, 'areaSelection'=>[]]);
    }

    public function actionExport_total($id = -1)
    {
        set_time_limit(1000);// set max exec time
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


                if(empty($area)) $area = -1;
                if(empty($exchange_id)) $exchange_id = -1;

                $searchParams = ['area'=>$area, 'exchange_id'=>$exchange_id, 'phaseNo'=>-1];
                $params = Yii::$app->request->post();
                $repType = 1;// horizontally report
                if(isset($params['search']))
                {
                    $searchParams['area'] = $params['search']['area'];
                    if(isset($params['search']['exchange_id']))
                        $searchParams['exchange_id'] = $params['search']['exchange_id'];
                    $searchParams['phaseNo'] = $params['search']['phaseNo'];

                    $repType = $params['search']['repType'];
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
                //select exchanges first
                $exchanges = \app\models\PcViewRecords::find()->select('exchange_id')->where($cond)->andWhere($exCond);
                //select records
                $records = \app\models\PcViewRecords::find()->where(['exchange_id'=>$exchanges])->orderBy('area, center_name, name', 'priority')->asArray()->all();
                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id']])->orderBy('priority')->asArray()->all();
                $choices = \app\models\PcViewChoices::find()->select('id,choice')->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($choices as $ch)
                {
                    $array[$ch['id']] = $ch['choice'];
                }
                $choices = $array;

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

                if($searchParams['exchange_id'] > 0) $queryParams['center_name'] = \app\models\PcExchanges::find()->select('name')->where(['id'=>$searchParams['exchange_id']])->scalar();

                if($repType == 1)
                    return $this->exportHorizontally($records,$operations, $choices, $project, $queryParams);
                else
                    return $this->exportVertically($records,$operations, $choices, $project, $queryParams);


            }

        }

        return $this->redirect(['report/total']);
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

    //  statistics
    public function actionStat($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(!isset($session['userProjects'])) return $this->redirect(['main/home']);
        $userProjects = $session['userProjects'];
        $upId = [];
        foreach ($userProjects as $i=>$up) array_push($upId, $i);
        $projects = \app\models\PcProjects::find()->where(['id'=>$upId, 'enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();        $project_id = $id;
        if($id > -1) {
            $project = \app\models\PcProjects::find()->where(['id' => $project_id, 'enabled' => true])->asArray()->one();
            $session = Yii::$app->session;
            $session->open();
            $userProjects = $session['userProjects'];
            if (isset($userProjects[$project['id']])) {
                $areaSelection = [-1=>'کل مناطق', 2=>'2', 3=>"3", 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8'];
                $ae = $userProjects[$project['id']];
                $area = $ae['area'];
                $exchange_id = $ae['exchange_id'];

                $accessLevel=['level'=>-1, 'name'=>''];
                $access = false;
                if(empty($ae['area']) && empty($ae['exchange_id']) )
                {
                    $accessLevel['level'] = 1;
                    $access = true;
                }
                else if(($ae['area'] > 0) && empty($ae['exchange_id']))
                {
                    $accessLevel['level'] = 2;
                    $access = true;
                }
                else if(($ae['area'] > 0) && ($ae['exchange_id'] > 0))
                {
                    $accessLevel['level'] = 3;
                    $access = true;
                }

                if( $access && Yii::$app->request->isPost)
                {
                    $post = Yii::$app->request->post();
                    if(!isset($post['search']['exchange_id'])) $post['search']['exchange_id'] = '-1';
                    if(isset($post['search']))
                        return $this->exportStat($project, $post['search'] );
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

                    $array = [-1=>[-1=>'کل مراکز'], $ae['area']=>[-1=>'کل مراکز']];
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

                    $array = [];//-1=>[-1=>'کل مراکز']
                    foreach ($exchanges as $exch)
                    {
                        $array[$exch['area']][$exch['id']] = $exch['name'];
                    }
                    $exchanges = $array;
                    $aex = [$ae['area'], $ae['exchange_id']];
                }

                //##################
                $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id'], 'type_id'=>1])->orderBy('priority')->asArray()->all();
                $choices = \app\models\PcViewChoices::find()->select('id,op_id,choice')->where(['project_id'=>$project['id']])->asArray()->all();
                $array = [];
                foreach($choices as $ch)
                {
                    $array[$ch['op_id']][-1] = "";
                    $array[$ch['op_id']][$ch['id']] = $ch['choice'];
                }
                $choices = $array;

                return $this->render('stat', ['exchanges'=>$exchanges, 'aex'=>$aex, 'project'=>$project, 'projects'=>$projects, 'areaSelection'=>$areaSelection, 'operations'=>$operations, 'choices'=>$choices]);

            }
        }

        return $this->render('stat', ['exchanges'=>[], 'aex'=>[], 'project'=>[], 'projects'=>$projects, 'areaSelection'=>[], 'operations'=>[], 'choices'=>[]]);

    }
    public function exportStat($project, $post)
    {
        set_time_limit(1000);// set max exec time
        $operations = \app\models\PcOperations::find()->where(['project_id'=>$project['id']])->orderBy('priority')->asArray()->all();
        $choices = \app\models\PcViewChoices::find()->select('id,choice')->where(['project_id'=>$project['id']])->asArray()->all();
        $array = [];
        foreach($choices as $ch)
        {
            $array[$ch['id']] = $ch['choice'];
        }
        $choices = $array;

        $searchParams = ['area'=>-1, 'exchange_id'=>-1];
        $fromMod ="";
        $toMod = "";
        $modString = "";
        if(isset($post['area']))
            $searchParams['area'] = (integer)$post['area'];
        if(isset($post['exchange_id']))
            $searchParams['exchange_id'] = (integer)$post['exchange_id'];
        $searchParams['phaseNo'] = (integer)$post['phaseNo'];
        $fromMod = $post['from-mod'];
        $toMod = $post['to-mod'];
        $fromFlag = (integer)$post['from-flag'];
        $toFlag = (integer)$post['to-flag'];

        if($fromFlag == 0)
            $fromMod = "";
        if($toFlag == 0)
            $toMod = "";

        if( (!empty($fromMod)) || (!empty($toMod)) )
                $modString = $fromMod." ~ ".$toMod;
        $searchParams['fromMod'] = $fromMod;
        $searchParams['toMod'] = $toMod;

        $ok = false;
        foreach ($operations as $op)
        {
            if(isset($post[$op['id']]))
            {
                if($post[$op['id']] > -1)
                {
                    $ok = true;
                    $searchParams[$op['id']] = $post[$op['id']];
                }
            }
        }
        if(!$ok)
        {
            Yii::$app->session->setFlash("error", "حداقل یک ویژگی باید انتخاب نمایید.");
            return $this->redirect(['report/stat']);
        }

        //############
        $recordsInfo = [];
        $area = (integer)$searchParams['area'];
        $exchange_id = (integer)$searchParams['exchange_id'];
        $phaseNo = (integer)$searchParams['phaseNo'];
        $phase = "";
        if($phaseNo > -1)
            $phase = $phaseNo;
        if( ($area > -1) && ($exchange_id > -1) )
        {
            $exchange = \app\models\PcExchanges::find()->select('name')->where(['id'=>$exchange_id])->scalar();
            $recordsInfo[$area] = ['area'=>$area, 'exchange_id'=>$exchange_id, 'exchange'=>$exchange, 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
        }
        else if($area > -1)
        {
            $recordsInfo[$area] = ['area'=>$area, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
        }
        else
        {
            $recordsInfo[2] = ['area'=>2, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]]; // operations=>[opid=>[operation, choice, count]]
            $recordsInfo[3] = ['area'=>3, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
            $recordsInfo[4] = ['area'=>4, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
            $recordsInfo[5] = ['area'=>5, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
            $recordsInfo[6] = ['area'=>6, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
            $recordsInfo[7] = ['area'=>7, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
            $recordsInfo[8] = ['area'=>8, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phase, 'time'=>$modString, 'operations'=>[]];
        }

        $cond = [];
        if($phaseNo > -1)
            $cond['phase'] = $phaseNo;
        $cond['project_id'] = $project['id'];
        if((integer)$searchParams['area'] > -1)
            $cond['area'] = (integer)$searchParams['area'];
        $exCond = [];
        if((integer)$searchParams['exchange_id'] > -1)
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
                $OP_VALUE = (integer)$searchParams[$op['id']];
                if( $OP_VALUE > -1)
                {
                    foreach ($recordsInfo as $AREA=>$INFO)
                    {
                        $EXCH = $INFO['exchange_id'];
                        $OP_ID = $op['id'];
                        $opCond = ['op_id'=>$OP_ID, 'op_value'=>$OP_VALUE];
                        $cond = [];
                        if($EXCH > -1)
                            $cond = ['or', ['exchange_id'=>(integer)$EXCH], ['center_id'=>(integer)$EXCH]];

                        $cnt  = \app\models\PcViewRecords::find()->select('COUNT(DISTINCT exchange_id)')->where(['exchange_id'=>$exchanges])->andWhere(['area'=>$AREA])->andWhere($cond)->andWhere($opCond)->scalar();
                        $recordsInfo[$AREA]['operations'][$OP_ID] = ['operation'=>$op['operation'], 'choice'=>$choices[$OP_VALUE], 'count'=>$cnt];
                    }
                }
            }
        }
        return $this->exportStatTable($project, $recordsInfo);
    }
    public function exportStatTable($project, $recordsInfo)
    {
        //[ 2=>['area'=>2, 'exchange_id'=>-1, 'exchange'=>'کل مراکز', 'phase'=>$phaseNo, 'time'=>$modString, 'operations'=>[opid=>[operation, choice, count]]]
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

        // operation count
        $op_size = 0;
        $operations = [];
        foreach ($recordsInfo as $area=>$rec)
        {
            $operations = $rec['operations']; // [id1=>[op, choice, count, id2=>[] ]]
            $op_size = sizeof($operations);
            break;
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Tehran Telecommunication. Developed by Samad Ghanbari")
            ->setTitle("Project Control")
            ->setDescription("P D C P");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $columnCount = 4 + $op_size;
        $maxColumn = Cell\Coordinate::stringFromColumnIndex($columnCount);
        $maxRows = sizeof($recordsInfo)+5; // pdcp , project, date , header+choice

         //topic
        $sheet->mergeCells('A1:E1');
        $sheet->getRowDimension('1')->setRowHeight(50);
        $sheet->getStyle('A1')->applyFromArray($TopicStyle);
        $sheet->setCellValue('A1', "P D C P");
        //date
        $sheet->mergeCells('A2:E2');
        $sheet->getRowDimension('2')->setRowHeight(40);
        $sheet->getStyle('A2')->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A2', $project['project']);
        //date
        $sheet->mergeCells('A3:E3');
        $sheet->getRowDimension('3')->setRowHeight(40);
        $sheet->getStyle('A3')->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A3', \app\components\Jdf::jdate('Y/m/d', time()));

        //assign operation column
        $opCol =[]; // op_id=>col
        $i = 5;
        foreach ($operations as $id=>$op)
        {
            $opCol[$id] = Cell\Coordinate::stringFromColumnIndex($i);
            $i++;
        }

        $row = 4;

        //header
        $sheet->getRowDimension($row)->setRowHeight(40);
        $sheet->getRowDimension($row+1)->setRowHeight(40);

        //$sheet->getStyle('A'.$row.':'.$maxColumn.($row+1))->applyFromArray($HeaderStyle);
        $sheet->getStyle('A'.$row.':A'.($row+1))->applyFromArray($HeaderStyle);
        $sheet->setCellValue('A'.$row, 'منطقه');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getStyle('B'.$row.':B'.($row+1))->applyFromArray($HeaderStyle);
        $sheet->setCellValue('B'.$row, 'مرکز');
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getStyle('C'.$row.':C'.($row+1))->applyFromArray($HeaderStyle);
        $sheet->setCellValue('C'.$row, 'فاز');
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getStyle('D'.$row.':D'.($row+1))->applyFromArray($HeaderStyle);
        $sheet->setCellValue('D'.$row, 'زمان ویرایش');
        $sheet->getColumnDimension('D')->setWidth(50);

        foreach ($operations as $id=>$op)
        {
            $sheet->getStyle($opCol[$id].$row.':'.$opCol[$id].($row+1))->applyFromArray($HeaderStyle);
            $sheet->setCellValue($opCol[$id].$row, $op['operation']);
            $sheet->getColumnDimension($opCol[$id])->setWidth(25);
        }
        $row++;
        foreach ($operations as $id=>$op)
        {
            $sheet->setCellValue($opCol[$id].$row, $op['choice']);
            $sheet->getColumnDimension($opCol[$id])->setWidth(25);
        }
        $row++;

        foreach ($recordsInfo as $area=>$rec)
        {
            $sheet->getRowDimension($row)->setRowHeight(30);
            //$sheet->getStyle('A'.$row.':'.$maxColumn.$row)->applyFromArray($ContentStyle);

            $sheet->getStyle('A'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('A'.$row, $rec['area']);
            $sheet->getStyle('B'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('B'.$row, $rec['exchange']);
            $sheet->getStyle('C'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('C'.$row, $rec['phase']);
            $sheet->getStyle('D'.$row)->applyFromArray($ContentStyle);
            $sheet->setCellValue('D'.$row, $rec['time']);

            $operations = $rec['operations'];
            foreach ($operations as $id=>$op)
            {
                $sheet->getStyle($opCol[$id].$row)->applyFromArray($ContentStyle);
                $sheet->setCellValue($opCol[$id].$row, $op['count']);
            }
            $row++;
        }

// Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PDCP-Stat-Report'.$project['project'].'.xls"');
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
