<?php

namespace app\controllers;

use app\models\ContactForm;
use app\models\EntryForm;
use app\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSay($message = 'Hello-World')
    {
	return $this->render('say', ['message' => $message]);
    }

    public function actionEntry()
    {
        $model = new EntryForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // 验证 $model 收到的数据

            // 做些有意义的事 ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // 无论是初始化显示还是数据验证错误
            return $this->render('entry', ['model' => $model]);
        }    
    }
    
    public function actionRedis()
    {
        /*
        $redisKey = 'tc-user-info-10831802';       
        //Yii::$app->redis->set($redisKey,'111');  //设置redis缓存
        $redisRes = Yii::$app->redis->get($redisKey);   //读取redis缓存
        //var_export($redisRes);
        if(''==$redisRes||\is_null($redisRes)){
            echo "empty redis\n";
        }
        //print_r($redisRes);echo "\n";
        $redisRes = \json_decode($redisRes,true);
        //print_r($redisRes);
        $nickname= isset($redisRes['nickname'])?$redisRes['nickname']:'unknown';
        
        
        $userInviteRes = Yii::$app->redis->get('tc-user-invite-template-list');   //读取redis缓存
        if(''==$userInviteRes||\is_null($userInviteRes)){
            echo "empty userInviteRes\n";
        }
        var_export($userInviteRes);
        */
        
        $nickname='cart';
        
        //$oRes = $this->setcart();
        $oRes = $this->getcart();
        print_r($oRes);
        
        
        return $this->render('redis', ['message' => $nickname]);
    }
    
    private function setcart(){
        $_REQUEST['uid']=10808;
        $_REQUEST['cart']=\time();
//         $_REQUEST['cart']=[
//             ['id'=>101,'price'=>10.20,'num'=>6],
//             ['id'=>108,'price'=>15.02,'num'=>2],
//         ];
        if (isset($_REQUEST['uid'])&&isset($_REQUEST['cart'])) {
            $uid = $_REQUEST['uid'];
            $cart = $_REQUEST['cart'];
            //Redis保存购物车数据：30分钟
            $redis = Yii::$app->redis;
            $redis->set('cart:'.$uid, $cart);
            $redis->expire('cart:'.$uid, 30*60);
            $result['error'] = 0;
            $result['msg'] = '保存成功';
        } else {
            $result['error'] = 1;
            $result['msg'] = '参数错误';
        }
        return $result;
    }
    
    private function getcart(){
        $_REQUEST['uid']=10808;
        if (isset($_REQUEST['uid'])) {
            $uid = $_REQUEST['uid'];
            //Redis保存购物车数据：30分钟
            $redis = Yii::$app->redis;
            $cart = $redis->get('cart:'.$uid);
            $result['error'] = 0;
            $result['msg'] = '获取成功';
            $result['cart'] = $cart ? $cart : '';
        } else {
            $result['error'] = 1;
            $result['msg'] = '参数错误';
        }
        return $result;
    }
}
