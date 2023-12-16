<?php

namespace app\controllers;

use phpDocumentor\Reflection\Types\Scalar;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;

class ImportController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $session->open();
        if (isset($session['user']))
        {
            if($session['user']['admin'] == true )
                return parent::beforeAction($action);
            else
                return $this->redirect(["main/login"]);
        }
        else
        {
            return $this->redirect(["main/login"]);
        }
    }

    public function actionTotal()
    {
        return 1;
        set_time_limit(300);// set max exec time

        // operations
        $OP_KV = 1;
        $OP_CUSTOMER = 24;
        $OP_CAP = 27;
        $OP_CRM = 28;
        $OP_BAZDID = 2;
        $OP_PHASE = 21;
        $OP_TTER = 25;//tarh termminal
        $OP_TTTER = 26;//tayid tarh terminal
        $OP_NASBTER = 3; // nasb terminal
        $OP_TAB = 4;//tarh abune
        $OP_TTAB = 5;// tayid tarh abune
        $OP_MESI = 6;// kabl keshi
        $OP_TF = 7;//eraye tarh fibr
        $OP_TTF = 8;//tayid tarh fibr
        $OP_ENTKHPEY = 9;//entekhab peymankar
        $OP_PEYMANKAR = 10;// nam peymankar
        $OP_HAZMJ = 11;//hazine mojavez
        $OP_MJSHAHR = 12;//mojavez shahrdari
        $OP_HAZEJ = 13;// hazine ejra
        $OP_ZAMAN = 14;//zaman bandi
        $OP_EF = 15;//ejraye fibr
        $OP_TAHF = 16;//tahvil fibr
        $OP_BAH = 17;//bahrebardari
        $OP_ADSL = 18;
        $OP_VDSL = 19;
        $OP_TOZ = 20;//tozihat
        $OP_SABT = 22;//sabt
        $OP_MOD = 23;// virayesh

        //choices
        $choices = \app\models\PcChoices::find()->asArray()->all();
        $array = [];// [op1=>['ch'=>id, ...],   ]
        foreach ($choices as $ch)
        {
            $array[$ch['op_id']][$ch['choice']] = $ch['id'];
        }
        $choices = $array;


        $model = \app\models\PcTemp::find()->asArray()->all();
        foreach ($model as $rec)
        {
            $exch = new \app\models\PcExchanges();
            $exch->id = $rec['id'];
            $exch->project_id = 1;
            $exch->area = $rec['area'];
            $exch->name = $rec['site_id'];
            $exch->abbr = null;
            $exch->type = 3;
            $exch->center_id = null;
            $exch->site_id = $rec['site_id'];
            $exch->kv_code = $rec['kv_code'];
            $exch->address = $rec['address'];
            $exch->position = $rec['position'];

            $center = $rec['center'];
            $center_id = \app\models\PcExchanges::find()->select('id')->where(['name'=>$center, 'type'=>2])->scalar();

            if($center_id > 0)
                $exch->center_id = $center_id;
            //else
            //   return var_dump([$center, $rec]);

            if(!$exch->save())
                return var_dump($exch);


            // kv, bazdid, tarhter, ttter, nasbter, tab, ttab, mesi, tf, ttf, peymankar, mjshahr, ejf, tahf, bah, adsl, vdsl, toz
            $op_id = $OP_CUSTOMER;
            $op_value = 43;//shatel
            $op_value = (string)$op_value;
            $record = new \app\models\PcRecords();
            $record->project_id = 1;
            $record->exchange_id = $exch->id;
            $record->op_id = $op_id;
            $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);

            $field = $rec['kv'];
            $op_id = $OP_KV;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }


            $field = $rec['bazdid'];
            $op_id = $OP_BAZDID;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['tarhter'];
            $op_id = $OP_TTER;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['ttter'];
            $op_id = $OP_TTTER;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['nasbter'];
            $op_id = $OP_NASBTER;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['tab'];
            $op_id = $OP_TAB;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['ttab'];
            $op_id = $OP_TTAB;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['mesi'];
            $op_id = $OP_MESI;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['tf'];
            $op_id = $OP_TF;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['ttf'];
            $op_id = $OP_TTF;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            //peymankar, mjshahr, ejf, tahf, bah, adsl, vdsl, toz
            $field = $rec['peymankar'];
            $op_id = $OP_PEYMANKAR;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['mjshahr'];
            $op_id = $OP_MJSHAHR;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['ejf'];
            $op_id = $OP_EF;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }


            $field = $rec['tahf'];
            $op_id = $OP_TAHF;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            $field = $rec['bah'];
            $op_id = $OP_BAH;
            if(isset($choices[$op_id][$field]))
            {
                $op_value = $choices[$op_id][$field];
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);
            }

            if($rec['adsl'] > 0)
            {
                $field = $rec['adsl'];
                $op_id = $OP_ADSL;
                $op_value = $field;
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
                if (!$record->save())
                    return var_dump($record);
            }

            if($rec['vdsl'] > 0)
            {
                $field = $rec['vdsl'];
                $op_id = $OP_VDSL;
                $op_value = $field;
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 1;
                $record->exchange_id = $exch->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
                if (!$record->save())
                    return var_dump($record);
            }


            $field = $rec['toz'];
            $op_id = $OP_TOZ;
            $op_value = $field;
            $op_value = (string)$op_value;
            $record = new \app\models\PcRecords();
            $record->project_id = 1;
            $record->exchange_id = $exch->id;
            $record->op_id = $op_id;
            $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);


            $op_id = $OP_SABT;
            $op_value = time();
            $op_value = (string)$op_value;
            $record = new \app\models\PcRecords();
            $record->project_id = 1;
            $record->exchange_id = $exch->id;
            $record->op_id = $op_id;
            $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);

            $op_id = $OP_MOD;
            $op_value = time();
            $op_value = (string)$op_value;
            $record = new \app\models\PcRecords();
            $record->project_id = 1;
            $record->exchange_id = $exch->id;
            $record->op_id = $op_id;
            $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);

            $op_id = $OP_PHASE;
            $op_value = 2;
            $op_value = (string)$op_value;
            $record = new \app\models\PcRecords();
            $record->project_id = 1;
            $record->exchange_id = $exch->id;
            $record->op_id = $op_id;
            $record->op_value = $op_value;
            if(!$record->save())
                return var_dump($record);










        }


    }

    public function actionUsers()
    { return 1;
        $users = \app\models\PcUsersTemp::find()->asArray()->all();
//        $error=[];
//        foreach ($users as $user)
//        {
//            if(!empty($user['center']))
//            {
//                $center = $user['center'];
//                $cid = \app\models\PcExchanges::find()->select('id')->where(['name'=>$center])->scalar();
//                if($cid > 0)
//                {
//                    $model = \app\models\PcUsersTemp::findOne($user['id']);
//                    $model->exchange_id = $cid;
//                    $model->save();
//                }
//                else
//                    array_push($error, ['id'=>$user['id'], 'center'=>$center]);
//            }
//        }
//
//        return var_dump($error);



        foreach ($users as $user)
        {
            $model = new \app\models\PcUsers();
            $model->id = $user['id'];
            $model->name = $user['name'];
            $model->lastname = $user['lastname'];
            $model->nid = $user['nid'];
            $model->employee_code = $user['employee_code'];
            $model->office = $user['office'];
            $model->post = $user['post'];
            $model->tel = $user['tel'];
            $model->password = md5('123');
            $model->passwordConfirm = md5('123');
            $model->admin = false;
            $model->reset_password = true;
            $model->enabled = 1;
            if(!$model->save())
                return var_dump(['id'=>$user['id'], $model->getErrors()]);

            $model = new \app\models\PcUserProjects();
            $model->user_id = $user['id'];
            $model->project_id = 1;
            $model->area = $user['area'];
            $model->exchange_id = $user['exchange_id'];
            $model->enabled = 1;
            if(!$model->save())
                return var_dump(['id'=>$user['id'], $model->getErrors()]);        }
    }


    // 1000
    public function actionKv1000_1()
    {return 1;
        $op_fiber_path = 67;
        $op_ej_fiber = 42;
        $op_phase = 34;
        $op_reg_time = 32;
        $op_mod_time = 33;
        $op_cap = 66;

        set_time_limit(300);// set max exec time
        $temp = \app\models\PcTemp1000::find()->asArray()->all();
        foreach ($temp as $t)
        {
            $exchange = $t['center'];
            $id = \app\models\PcExchanges::find()->select('id')->where(['name'=>$exchange])->scalar();
            if(empty($id) || ($id < 1))
                return var_dump($t);
        }

    }
    public function actionKv1000_2()
    { return 1;
        $op_fiber_path = 67;
        $op_ej_fiber = 42;
        $op_phase = 34;
        $op_reg_time = 32;
        $op_mod_time = 33;
        $op_cap = 66;

        set_time_limit(300);// set max exec time
        $temp = \app\models\PcTemp1000::find()->asArray()->all();
        foreach ($temp as $t)
        {
            $exchange = $t['center'];
            $data = \app\models\PcExchanges::find()->select('id, abbr')->where(['name'=>$exchange, 'type'=>2, 'project_id'=>2])->one();
            $model = \app\models\PcTemp1000::findOne($t['id']);
            $model->center_id = $data['id'];
            $model->site_id = $data['abbr'].'-'.$model->kv_code;
            if(!$model->update())
                return var_dump($t);
        }

    }

    public function actionKv1000_3()
    { return 1;
        $op_fiber_path = 67;
        $op_ej_fiber = 42;
        $op_phase = 34;
        $op_reg_time = 32;
        $op_mod_time = 33;
        $op_cap = 66;
        $op_kv_type = 68; //kv_type

        set_time_limit(300);// set max exec time

        //choices
        $choices = \app\models\PcChoices::find()->asArray()->all();
        $array = [];// [op1=>['ch'=>id, ...],   ]
        foreach ($choices as $ch)
        {
            $array[$ch['op_id']][$ch['choice']] = $ch['id'];
        }
        $choices = $array;


        $temp = \app\models\PcTemp1000::find()->asArray()->all();
        foreach ($temp as $t)
        {
            $area = \app\models\PcExchanges::find()->select('area')->where(['id'=>$t['center_id']])->scalar();
            $model = new \app\models\PcExchanges();
            $model->project_id = 2;
            $model->area = $area;
            $model->name = $t['site_id'];
            $model->type =3;
            $model->center_id = $t['center_id'];
            $model->site_id = $t['site_id'];
            $model->kv_code = $t['kv_code'];
            $model->address = $t['address'];
            $model->position = null;
            $model->done = false;

            if($model->save())
            {
                $field = $t['cap'];
                $op_id = $op_cap;
                $op_value = $field;
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 2;
                $record->exchange_id = $model->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
                if(!$record->save())
                    return var_dump($record);

                $op_id = $op_reg_time;
                $op_value = time();
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 2;
                $record->exchange_id = $model->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
                if(!$record->save())
                    return var_dump($record);

                $op_id = $op_mod_time;
                $op_value = time();
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 2;
                $record->exchange_id = $model->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
                if(!$record->save())
                    return var_dump($record);

                $op_id = $op_phase;
                $op_value = 1;
                $op_value = (string)$op_value;
                $record = new \app\models\PcRecords();
                $record->project_id = 2;
                $record->exchange_id = $model->id;
                $record->op_id = $op_id;
                $record->op_value = $op_value;
                if(!$record->save())
                    return var_dump($record);


                $field = $t['fiber_path'];
                $op_id = $op_fiber_path;
                if(isset($choices[$op_id][$field]))
                {
                    $op_value = $choices[$op_id][$field];
                    $op_value = (string)$op_value;
                    $record = new \app\models\PcRecords();
                    $record->project_id = 2;
                    $record->exchange_id = $model->id;
                    $record->op_id = $op_id;
                    $record->op_value = $op_value;
                    if(!$record->save())
                        return var_dump($record);
                }

                $field = $t['ert_fiber'];
                $op_id = $op_ej_fiber;
                if(isset($choices[$op_id][$field]))
                {
                    $op_value = $choices[$op_id][$field];
                    $op_value = (string)$op_value;
                    $record = new \app\models\PcRecords();
                    $record->project_id = 2;
                    $record->exchange_id = $model->id;
                    $record->op_id = $op_id;
                    $record->op_value = $op_value;
                    if(!$record->save())
                        return var_dump($record);
                }


                $field = $t['kv_type'];
                $op_id = $op_kv_type;
                if(isset($choices[$op_id][$field]))
                {
                    $op_value = $choices[$op_id][$field];
                    $op_value = (string)$op_value;
                    $record = new \app\models\PcRecords();
                    $record->project_id = 2;
                    $record->exchange_id = $model->id;
                    $record->op_id = $op_id;
                    $record->op_value = $op_value;
                    if(!$record->save())
                        return var_dump($record);
                }




            }



        }

    }

// update operation
    public function actionUpdate_p1_t1()
    {
        set_time_limit(300);// set max exec time
        $operations = \app\models\PcOperations::find()->where(['project_id'=>1, 'type_id'=>1])->asArray()->all();
        //choices
        $choices = \app\models\PcChoices::find()->asArray()->all();
        $array = [];// [op1=>['ch'=>id, ...],   ]
        foreach ($choices as $ch)
        {
            if ($ch['choice'] == 'انجام نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else if($ch['choice'] == 'تحویل نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else  if($ch['choice'] == 'بهره برداری نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else  if($ch['choice'] == 'نصب نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }

        }
        $choices = $array;
//        return var_dump($choices);
$notArray = [];
        $exchanges = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>1, 'type'=>3])->asArray()->all();
        foreach ($exchanges as $ex)
        {
            $exchId = $ex['id'];
            foreach ($operations as $op)
            {
                $opId = $op['id'];
                $rec = \app\models\PcRecords::find()->select('id')->where(['project_id'=>1, 'exchange_id'=>$exchId, 'op_id'=>$opId])->scalar();
                if($rec == false )
                {
                    if(isset($choices[$opId]))
                    {
                        $model = new \app\models\PcRecords();
                        $model->op_id = $opId;
                        $model->exchange_id = $exchId;
                        $model->op_value = (string)$choices[$opId];
                        $model->project_id = 1;

                        if(!$model->save()) return var_dump($model->getErrors());
                    }
                    else
                    {
                        array_push($notArray, $opId);
                    }
                }
            }

//            return var_dump($notArray);
        }



    }

    public function actionUpdate_p2_t1()
    {
        set_time_limit(300);// set max exec time
        $operations = \app\models\PcOperations::find()->where(['project_id'=>2, 'type_id'=>1])->asArray()->all();
        //choices
        $choices = \app\models\PcChoices::find()->asArray()->all();
        $array = [];// [op1=>['ch'=>id, ...],   ]
        foreach ($choices as $ch)
        {
            if ($ch['choice'] == 'انجام نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else if($ch['choice'] == 'تحویل نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else  if($ch['choice'] == 'بهره برداری نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else  if($ch['choice'] == 'نصب نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }
            else  if($ch['choice'] == 'مشخص نشده')
            {
                $array[$ch['op_id']] = $ch['id'];
            }

        }
        $choices = $array;
//        return var_dump($choices);
        $notArray = [];
        $exchanges = \app\models\PcExchanges::find()->select('id')->where(['project_id'=>2, 'type'=>3])->asArray()->all();
        foreach ($exchanges as $ex)
        {
            $exchId = $ex['id'];
            foreach ($operations as $op)
            {
                $opId = $op['id'];
                $rec = \app\models\PcRecords::find()->select('id')->where(['project_id'=>2, 'exchange_id'=>$exchId, 'op_id'=>$opId])->scalar();
                if($rec == false )
                {
                    if(isset($choices[$opId]))
                    {
                        $model = new \app\models\PcRecords();
                        $model->op_id = $opId;
                        $model->exchange_id = $exchId;
                        $model->op_value = (string)$choices[$opId];
                        $model->project_id = 2;

                        if(!$model->save()) return var_dump($model->getErrors());
                    }
                    else
                    {
                        array_push($notArray, $opId);
                    }
                }
            }

//            return var_dump($notArray);
        }



    }

//     check wrong operation
    public function actionWrong1()
    {
        set_time_limit(300);// set max exec time
        $operations = \app\models\PcOperations::find()->select('id')->where(['project_id'=>1]);

        $recId = \app\models\PcRecords::find()->select('id')->where(['project_id'=>1])->andWhere(['not', ['op_id'=>$operations]])->asArray()->all();
        var_dump($recId);
    }

    public function actionWrong2()
    {
        set_time_limit(300);// set max exec time
        $operations = \app\models\PcOperations::find()->select('id')->where(['project_id'=>2]);

        $recId = \app\models\PcRecords::find()->select('id')->where(['project_id'=>2])->andWhere(['not', ['op_id'=>$operations]])->asArray()->all();
        return var_dump($recId);
    }

//    remove wrong operation
    public function actionWrong_p1()
    {
        set_time_limit(300);// set max exec time
        $operations = \app\models\PcOperations::find()->select('id')->where(['project_id'=>1]);


        $recId = \app\models\PcRecords::find()->select('id')->where(['project_id'=>1])->andWhere(['not', ['op_id'=>$operations]]);//->asArray()->all();
//        return var_dump($recId);


        \app\models\PcRecords::deleteAll(['id'=>$recId]);


        $recId = \app\models\PcRecords::find()->select('id')->where(['project_id'=>1])->andWhere(['not', ['op_id'=>$operations]])->asArray()->all();
        var_dump($recId);


    }

    public function actionWrong_p2()
    {
        set_time_limit(300);// set max exec time
        $operations = \app\models\PcOperations::find()->select('id')->where(['project_id'=>2]);

        $recId = \app\models\PcRecords::find()->select('id')->where(['project_id'=>2])->andWhere(['not', ['op_id'=>$operations]]);//->asArray()->all();
//        return var_dump($recId);


        \app\models\PcRecords::deleteAll(['id'=>$recId]);

        $recId = \app\models\PcRecords::find()->select('id')->where(['project_id'=>2])->andWhere(['not', ['op_id'=>$operations]])->asArray()->all();
        return var_dump($recId);


    }

    // update reg time and modified time
    public function actionTime_phase1()
    {
        set_time_limit(300);// set max exec time
        //project 1
        // phase 21
        // reg 22
        // mod 23
        $records = \app\models\PcViewRecords::find()->select('exchange_id,op_id,op_value')->where(['project_id'=>1])->andWhere(['in', 'op_id', [21,22,23]])->asArray()->all();

        foreach ($records as $rec)
        {
            $exchId = $rec['exchange_id'];
            $op_id = $rec['op_id'];
            if($op_id == 21)
            {
                $phase = $rec['op_value'];
                $model = \app\models\PcExchanges::findOne($exchId);
                $model->phase = (integer)$phase;
                $model->update();
            }
            else if($op_id == 22)
            {
                $reg = $rec['op_value'];
                $model = \app\models\PcExchanges::findOne($exchId);
                $model->register_ts = (integer)$reg;
                $model->update();
            }
            else if($op_id == 23)
            {
                $mod = $rec['op_value'];
                $model = \app\models\PcExchanges::findOne($exchId);
                $model->modified_ts = (integer)$mod;
                $model->update();
            }
        }


    }
    public function actionTime_phase2()
    {
        set_time_limit(300);// set max exec time

        //project 2
        // phase 34
        // reg 32
        // mod 33
        $records = \app\models\PcViewRecords::find()->select('exchange_id,op_id,op_value')->where(['project_id'=>2])->andWhere(['in', 'op_id', [32,33,34]])->asArray()->all();

        foreach ($records as $rec)
        {
            $exchId = $rec['exchange_id'];
            $op_id = $rec['op_id'];

            if($op_id == 34)
            {
                $phase = $rec['op_value'];
                $model = \app\models\PcExchanges::findOne($exchId);
                $model->phase = (integer)$phase;
                $model->update();
            }
            else if($op_id == 32)
            {
                $reg = $rec['op_value'];
                $model = \app\models\PcExchanges::findOne($exchId);
                $model->register_ts = (integer)$reg;
                $model->update();
            }
            else if($op_id == 33)
            {
                $mod = $rec['op_value'];
                $model = \app\models\PcExchanges::findOne($exchId);
                $model->modified_ts = (integer)$mod;
                $model->update();
            }
        }


    }


//    update weights
    public function actionUpdate_weight_project($id=-1)
    {
        if($id > 0)
        {
            $sum = \app\models\PcOperations::find()->select("SUM(op_weight)")->where(['project_id'=>$id])->scalar();
            $model = \app\models\PcProjects::findOne($id);
            $model->project_weight = $sum;
            $model->update(false);
        }

        return "done";
    }

    public function actionUpdate_weight_exchange($pid=-1)
    {
        $allExchanges = \app\models\PcViewRecords::find()->select('exchange_id')->distinct()->where(['project_id'=>$pid])->asArray()->all();
        foreach ($allExchanges as $exchange)
        {
            $weight = \app\components\PdcpHelper::getWeight($pid, $exchange['exchange_id']);
            $model = \app\models\PcExchanges::findOne($exchange['exchange_id']);
            $model->weight = $weight;
            $model->update(false);
        }

        return "done";
    }


}
