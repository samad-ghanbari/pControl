<?php

namespace app\controllers;

use phpDocumentor\Reflection\Types\Scalar;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;


class MainController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                //'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $session->open();
        if ((isset($session['user'])) && (Yii::$app->controller->action->id == "login"))
        {
            return $this->redirect(["main/home"]);
        }
        else if ((isset($session['user'])) || (Yii::$app->controller->action->id == "login") || (Yii::$app->controller->action->id == "captcha"))
        {
            return parent::beforeAction($action);
        } else
        {
            return $this->redirect(["main/login"]);
        }
    }

    // ckeditor image upload
    public function actionImg_upload()
    {
        $funcNum = $_REQUEST['CKEditorFuncNum'];

        if(!empty($_FILES['upload']))
        {
            $message = "";
            if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])))
            {
                $message = "لطفا تصویر را بارگذاری نمایید.";
            }
            else if ($_FILES['upload']["size"] == 0 OR $_FILES['upload']["size"] > 5*1024*1024)
            {
                $message = "حجم تصویر نمی تواند از ۵ مگابایت بیشتر باشد.";
            }
            else if ( ($_FILES['upload']["type"] != "image/jpg")
                AND ($_FILES['upload']["type"] != "image/jpeg")
                AND ($_FILES['upload']["type"] != "image/png") )
            {
                $message = "فرمت تصویر معتبر نیست";
            }
            else if (!is_uploaded_file($_FILES['upload']["tmp_name"]))
            {

                $message = "بارگذاری تصویر با خطا مواجه شد";
            }
            else
            {
                $extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
                $name = time().'.'.$extension;
                // Here is the folder where you will save the images
                $bp = \getcwd();
                $folder = '/web/images/uploads/';
                $url = Yii::$app->urlManager->createAbsoluteUrl($folder.$name);

                move_uploaded_file( $_FILES['upload']['tmp_name'], $bp.$folder.$name );
            }

            echo '<script type="text/javascript">
                window.parent.CKEDITOR.tools.callFunction("'.$funcNum.'", "'.$url.'", "'.$message.'" );
                  </script>';
        }

    }

    public function actionImg_browse()
    {
        //get function num to pass it to the view (need to be called to pass data of selected file to CKEditor)
        $CKEditorFuncNum = Yii::$app->request->get('CKEditorFuncNum');

        //get list of uploaded files
        $files = FileHelper::findFiles(getcwd().'/web/images/uploads/');
        $array = [];
        foreach ($files as $file)
        {
            array_push($array, Yii::$app->request->baseUrl.'/web/images/uploads/'.basename($file));
        }

        return $this->renderAjax('browse', [
            'funcNum' => $CKEditorFuncNum,
            'files' => $array,
        ]);
    }

//

    public function actionLogin()
    {
        $model = new \app\models\PcUsers();

        if (Yii::$app->request->isPost)
        {
            if ($model->load(Yii::$app->request->post()))
            {
                $model->password = md5($model->password);
                $user = \app\models\PcUsers::find()->where(["nid" => $model->nid, "password" => $model->password, 'enabled'=>1])->asArray()->one();
                if (!empty($user))
                {
                    $session = Yii::$app->session;
                    $session->open();
                    
                    if (isset($session['user']))
                        $session->remove("user");
                    
                    $user['password'] = "";
                    unset($user['password']);
                    $session['user'] = $user;
                    
                    (new \app\components\PdcpHelper)->setUserProjectSession();
                    (new \app\components\PdcpHelper)->setUserProjectOwnerSession();
                    
                    if($user['admin'])
                    {
                        $newTicket = \app\models\PcTickets::find()->select("COUNT(*)")->where(['read'=>false])->scalar();
                        if($newTicket > 0)
                            return $this->redirect(['main/ticket_inbox']);
                    }
                    else
                    {
                        $newTicket = \app\models\PcTickets::find()->select("COUNT(*)")->where(['new_reply'=>true])->scalar();
                        if($newTicket > 0)
                            return $this->redirect(['main/ticket_inbox']);
                    }
                    return $this->redirect(['main/home']);
                }
                
                Yii::$app->session->setFlash('error', "نام کاربری یا رمز عبور اشتباه است");
                $model->password = NULL;
            }
        }

        $this->layout = "login";

        $flash = ""; $msg = "";$user='';
        if(Yii::$app->session->hasFlash('error'))  { $flash = "error"; $msg = Yii::$app->session->getFlash("error");}
        $session = Yii::$app->session;
        $session->open();
        if (isset($session['user'])) $user = $session['user'];
        Yii::$app->session->destroy();
        if(!empty($user))
             $session['user'] = $user;
        if(!empty($flash)) Yii::$app->session->setFlash($flash, $msg);

        return $this->render('login',['model' => $model]);
    }

    public function actionLogout()
    {
        $session = Yii::$app->session;
        $session->open();
        if (isset($session['user']))
        {
            $session->remove('user');
            
        }

	$session->destroy();
        return $this->redirect(['main/login']);
    }

    public function actionHome0()
    {
        $this->layout = "main";
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $user = $session['user'];

            if($user['reset_password'] == true)
            {
                return $this->redirect(['main/reset_password']);
            }
            
            if($user['admin'] == 1)
                $userProjects = \app\models\PcViewUserProjects::find()->select('project_id, project, office, ts, project_weight, contract_subject, contract_company, contract_date, contract_duration')->distinct()->where(['enabled'=>1, 'project_enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();
            else
                $userProjects = \app\models\PcViewUserProjects::find()->select('project_id, project, office, ts, project_weight, contract_subject, contract_company, contract_date, contract_duration')->distinct()->where(['user_id'=>$user['id'], 'enabled'=>1, 'project_enabled'=>true])->orderBy(['ts'=>SORT_DESC])->asArray()->all();

            $bulletin = \app\models\PcBulletin::find()->select('id,panel_color,ts,title')->orderBy(['ts'=>SORT_DESC])->limit(4)->all();

            return $this->render("home0", ['userProjects'=>$userProjects, 'bulletin'=>$bulletin]);
        }
        else
            return $this->redirect(['main/login']);

    }

    public function actionHome()
    {
        $this->layout = "main";
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $user = $session['user'];

            if($user['reset_password'] == true)
            {
                return $this->redirect(['main/reset_password']);
            }

            $searchModel = new \app\models\PcViewUserProjectsSearch();
            $params = Yii::$app->request->queryParams;
            if ($params)
            {
                $dataProvider = $searchModel->search($params);
                $dataProvider->query->andWhere(['enabled'=>1]);
                $dataProvider->query->andWhere(['project_enabled'=>true]);
                $dataProvider->query->andWhere(['user_id'=>$user['id']]);
                $dataProvider->query->orderBy(['ts'=>SORT_DESC]);                
            }
            else
            {

              if($user['admin'] == 1)
                 $qry = \app\models\PcViewUserProjects::find()->select('project_id, project, office, ts, project_weight, contract_subject, contract_company, contract_date, contract_duration')->distinct()->where(['enabled'=>1, 'project_enabled'=>true])->orderBy(['ts'=>SORT_DESC]);
              else
                 $qry = \app\models\PcViewUserProjects::find()->select('project_id, project, office, ts, project_weight, contract_subject, contract_company, contract_date, contract_duration')->distinct()->where(['user_id'=>$user['id'], 'enabled'=>1, 'project_enabled'=>true])->orderBy(['ts'=>SORT_DESC]);

               $dataProvider = new \yii\data\ActiveDataProvider(['query' => $qry]);
            }
            $dataProvider->pagination->pageSize = 25;

            $bulletin = \app\models\PcBulletin::find()->select('id,panel_color,ts,title')->orderBy(['ts'=>SORT_DESC])->limit(4)->all();

            return $this->render("home", ['searchModel'=>$searchModel, 'dataProvider'=>$dataProvider, 'bulletin'=>$bulletin]);
        }
        else
            return $this->redirect(['main/login']);

    }

    public function actionNotifications()
    {
        $this->layout = "main";
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $user = $session['user'];

            $bulletin = \app\models\PcBulletin::find()->select('id,panel_color,ts,title')->orderBy(['ts'=>SORT_DESC]);
            $notiTotalCount = $bulletin->count();
            $notiPages = new Pagination(['totalCount'=>$notiTotalCount]);
            $notiPages->pageSize = 20;
            $bulletin = $bulletin->offset($notiPages->offset)->limit($notiPages->limit)->all();

            $admin = $session['user']['admin'];

            return $this->render("notifications", ['user'=>$user, 'notiPages'=>$notiPages, 'bulletin'=>$bulletin, 'admin'=>$admin]);
        }
        else
            return $this->redirect(['main/login']);

    }

//    users
    public function actionReset_password()
    {
        $this->layout = "main";
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];

        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $id = $post['id'];
            $password = md5($post['password']);
            $passwordConfirm = md5($post['passwordConfirm']);
            $cp = md5($post['cp']);
            $model = \app\models\PcUsers::findOne($user['id']);

            if(($model->password == $cp) && ($id == $model->id) )
            {
                if($password == $passwordConfirm)
                {
                    $model->passwordConfirm = $password;
                    $model->password = $password;
                    $model->reset_password = false;
                    if($model->update(false))
                    {
                        if($user['reset_password'] == true)
                        {
                            $session->remove('user');
                            $user['reset_password'] = false;
                            $session['user'] = $user;
                        }
                        Yii::$app->session->setFlash('success', 'رمز شما با موفقیت تغییر یافت.');
                        return $this->redirect(['main/home']);
                    }
                    else {Yii::$app->session->setFlash('error', 'تغییر رمز با خطا مواجه شد.');}
                }
                else
                    Yii::$app->session->setFlash('error', 'رمزهای جدید وارد شده با هم تطابق ندارند.');
            }
            else
                Yii::$app->session->setFlash('error', 'رمز جاری وارد شده صحیح نمی باشد.');
        }

        return $this->render('resetPass', ['id'=>$user['id']]);
    }

//    projects
    public function actionProjects()
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if($user['admin'] == 1)
        {
            $this->layout = "home";
            $model = new \app\models\PublicViewUserProjects();

            return $this->render("projects", ['model'=>$model]);
        }
        else
            return $this->redirect(['main/logout']);
    }

    // log
    public function actionLog()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['project']))
        {
            $project = $session['project'];
            $userProjects = $session['userProjects'][$project['id']];
            $area = $userProjects['area'];
            $exchange_id = $userProjects['exchange_id'];
            $sModel = new \app\models\PcViewLogsSearch();
            $params = Yii::$app->request->queryParams;

            if(($area > 0) && (empty($exchange_id)))
            {
                //area
                $params['PcViewLogsSearch']['area'] = $area;
            }
            else if( ($area > 0) && ($exchange_id >0))
            {
                //exchange
                $params['PcViewLogsSearch']['area'] = $area;
                $params['PcViewLogsSearch']['exchange_id'] = $exchange_id;
            }

            $dProvider = $sModel->search($params);

            return $this->render('logs', ['project'=>$project, 'sModel'=>$sModel, 'dProvider'=>$dProvider]);
        }

        return $this->redirect(['main/home']);
    }


    // bulletin
    public function actionNew_notification()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            if($session['user']['admin'] == true)
            {
                $model = new \app\models\PcBulletin();
                if(Yii::$app->request->isPost)
                {
                    if($model->load(Yii::$app->request->post())) {
                        $model->ts = time();
                        if ($model->save())
                        {
                            Yii::$app->session->setFlash('success', 'اعلان جدید با موفقیت ایجاد شد.');
                            return $this->redirect(['main/home']);
                        }
                        else
                            Yii::$app->session->setFlash('error', 'ایجاد اعلان جدید با خطا مواجه شد.');
                    }
                }

                return $this->render("notification_new", ['model'=>$model]);
            }
        }

        return $this->redirect(['main/home']);
    }

    public function actionBulletin_view($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            if($id > 0)
            {
                $this->layout = "blank";
                $bulletin = \app\models\PcBulletin::findOne($id);
                return $this->render("bulletin_view", ['bulletin'=>$bulletin]);
            }
            return "<h3 style='text-align: center; color:#721c24; padding:10px;'>اعلان مورد نظر موجود نیست</h3>";
        }

        return $this->redirect(['main/login']);
    }

    public function actionDelete_notification($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if($user['admin'] == true)
        {
            if(Yii::$app->request->isPost)
            {
                $id = Yii::$app->request->post()['PcBulletin']['id'];
                $model = \app\models\PcBulletin::findOne($id);
                if($model->delete())
                {
                    Yii::$app->session->setFlash('success', 'اعلان با موفقیت حذف گردید.');
                }
                else
                {
                    Yii::$app->session->setFlash('error', 'حذف اعلان با خطا مواجه شد.');
                }

                return $this->redirect(['main/home']);
            }

            $model = \app\models\PcBulletin::find()->where(['id'=>$id])->one();
            return $this->render('notification_remove',['model'=>$model]);
        }

        return $this->redirect(['main/home']);
    }

    public function actionUpdate_notification($id = -1)
    {
        $session = Yii::$app->session;
        $session->open();
        $user = $session['user'];
        if( ($user['admin'] == true) && ($id > 0) )
        {
            $model = \app\models\PcBulletin::find()->where(['id'=>$id])->one();

            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    if($model->update())
                    {
                        Yii::$app->session->setFlash('success', 'ویرایش اعلان با موفقیت انجام شد.');
                        return $this->redirect(['main/home']);
                    }
                    else
                        Yii::$app->session->setFlash('error', 'ویرایش اعلان با خطا مواجه شد.');
                }
            }

            return $this->render('notification_update',['model'=>$model]);
        }

        return $this->redirect(['main/home']);
    }

    //    ticket
    public function actionTicket_inbox()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $admin = $session['user']['admin'];
            $cond = [];
            if(!$admin)
                $cond = ['user_id'=>$session['user']['id']];
            $searchModel = new \app\models\PcViewTicketsSearch();
            $params = Yii::$app->request->queryParams;
            if ($params)
            {
                $params['PcViewTicketsSearch']['user_id'] = $session['user']['id'];
                $dataProvider = $searchModel->search($params);
            }
            else
            {
                $qry = \app\models\PcViewTickets::find()->where($cond)->orderBy(['ts'=>SORT_DESC]);
                $dataProvider = new \yii\data\ActiveDataProvider(['query' => $qry]);
            }
            $dataProvider->pagination->pageSize = 25;

            return $this->render('ticket_inbox', ['dataProvider'=>$dataProvider, 'searchModel'=>$searchModel, 'admin'=>$admin]);
        }

        return $this->redirect(['main/login']);
    }
    public function actionTicket_view($id=-1)
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            if($id > 0)
            {
                $ticket = \app\models\PcTickets::findOne($id);
                if($session['user']['admin'])
                    $ticket->read = true;
                else
                    $ticket->new_reply = false;
                $ticket->update(false);
                $ticket = \app\models\PcViewTickets::findOne($id);
                $reply = new \app\models\PcTicketReplies();
                $reply->ticket_id = $id;
                $reply->replier_id = $session['user']['id'];

                $replies = \app\models\PcViewTicketReplies::find()->where(['ticket_id'=>$id])->orderBy(['ts'=>SORT_ASC])->asArray()->all();

                return $this->render("ticket_view", ['ticket'=>$ticket,'replies'=>$replies, 'reply'=>$reply, 'admin'=>$session['user']['admin']]);
            }

            return $this->redirect(['main/home']);
        }
        return $this->redirect(['main/login']);

    }
    public function actionTicket_insert()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            $model = new \app\models\PcTickets();
            $model->user_id = $session['user']['id'];
            if(Yii::$app->request->isPost)
            {
                if($model->load(Yii::$app->request->post()))
                {
                    $model->ts = time();
                    $model->read = false;
                    $model->new_reply = false;
                    if($model->save())
                    {
                        Yii::$app->session->setFlash("success", "درخواست شما با موفقیت ثبت شد.");
                        return $this->redirect(['main/ticket_inbox']);
                    }
                    else
                        Yii::$app->session->setFlash("error", "ثبت درخواست با خطا مواجه شد.");
                }
            }

            $projects = \app\models\PcViewUserProjects::find()->select('project_id, project')->where(['user_id'=>$session['user']['id']])->orderBy('project')->asArray()->all();
            $array = [];
            foreach ($projects as $p)
            {
                $array[$p['project_id']] = $p['project'];
            }
            $projects = $array; $array = [];

            return $this->render('ticket_insert', ['model'=>$model, 'projects'=>$projects]);
        }

        return $this->redirect(['main/login']);
    }
    public function actionTicket_reply()
    {
        $session = Yii::$app->session;
        $session->open();
        if(isset($session['user']))
        {
            if(Yii::$app->request->isPost)
            {
                $model = new \app\models\PcTicketReplies();
                if($model->load(Yii::$app->request->post()))
                {
                    $model->replier_id = $session['user']['id'];
                    $model->ts = time();
                    if($model->save())
                    {
                        Yii::$app->session->setFlash("success", "پاسخ شما با موفقیت ثبت شد.");
                        //new reply
                        $ticket = \app\models\PcTickets::findOne($model->ticket_id);
                        $ticket->new_reply = true;
                        $ticket->update(false);

                        return $this->redirect(['main/ticket_inbox']);
                    }
                    else
                    {
                        Yii::$app->session->setFlash("error", "ثبت پاسخ با خطا مواجه شد.");
                        return $this->redirect(['main/ticket_view', 'id'=>$model->ticket_id]);
                    }
                }
            }
            return $this->redirect(['main/home']);
        }

        return $this->redirect(['main/login']);
    }
}
