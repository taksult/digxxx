<?php
include_once(dirname(__FILE__) . '/definition.php');
include_once(PATH_LIBRARY);
session_cache_expire(0);
session_cache_limiter('private_no_expire');
session_start(); //session.auto_start オフ
//文字コード指定
header( 'Content-Type: text/html; charset=UTF-8' );

DBProcedure::init();
Database::initDB();
UserDataDatabase::init();
FollowListDatabase::init();
PostDataDatabase::init();
CheckListDatabase::init();
ContentDataDatabase::init();
AutoLoginDatabase::init();

$dispatcher = new Dispatcher();
$dispatcher->dispatch();


class Dispatcher{

    private $header;    //ヘッダーテンプレート
    private $footer;    //フッターテンプレート

    function __construct(){
        $this->header = new View(PATH_TEMPLATES . 'header.tpl');
        $this->footer = new View(PATH_TEMPLATES . 'footer.tpl');
    }

    public function dispatch(){

        //URIからパラメータ抽出してページごとのコントローラを呼ぶ
        unset($_SESSION['params']);  //パラメータはページごとにリセット
        $uri = $_SERVER['REQUEST_URI'];
        if(strpos($uri,"\0") !== false || strpos($uri,"%00")){
            echo '不正なリクエストを検出しました';
            if(isset($_SESSION['user_num'])){
                file_put_contents(PATH_ROOT . 'log/badreqs/request.log',
                                'remote:'.$_SERVER["REMOTE_ADDR"] . ' user:'.$_SESSION['user_num'] . ' uri:'. $uri. ' date:' . mydate() . "\r\n" ,
                                FILE_APPEND | LOCK_EX);
            }
            else{
                file_put_contents(PATH_ROOT . 'log/badreqs/request.log',
                                'remote:'.$_SERVER["REMOTE_ADDR"] . ' uri:'. $uri . ' date:' . mydate() . "\r\n" ,
                                FILE_APPEND | LOCK_EX);
            }
            exit;

        }
        $params;
        if($uri != null){
            $params = explode('/',$uri);
        }
        array_shift($params);
        /*
        while(end($params) == null && !empty($params)){
            array_pop($params);
        }
        */
        foreach($params as &$p){
            if($params[0] == 'contents' && $p == null){
                $p = '%2F';
            }
        }
        $_SESSION['params'] = [];
        $_SESSION['params'] = $params;

        //パラメータなし(ルート指定とか) or index.php ならトップorホーム
        if(empty($params) || $params[0] == 'index.php' || $params[0] == null || $params[0] == 'top'){
            if(isset($_SESSION['user_id'])){
                $pageName = 'home';     //ログイン時はホーム
            }
            else{
                $pageName = 'top';      //非ログイン時はトップ
            }

        }
    
        //第一パラメータが'i'ならapiを呼ぶ(ajax用)
        else if(isset($_SESSION['user_id']) && $params[0] == 'i'){
            if(isset($params[1])){
                    $class = ucfirst($params[1]) . 'API';
                    $path = PATH_INNERAPI  . DS . $class . '.class.php'; //$params[0]はバージョン指定
                    if(file_exists($path)){
                        array_shift($params);  //api名以降をパラメータとして各apiに渡す
                        array_shift($params);
                        $_SESSION['params'] = $params;
                        include($path);
                        $api = new $class();
                        $api->run();
                        // header('Location:' . $_SERVER['HTTP_REFERER']);
                        exit;
                    }
                    else{
                        header("HTTP/1.1 404 Not Found");
                        $this->error('エンドポイントが見つかりません');
                        exit;
                    }
                }
                else{
                    header("HTTP/1.1 404 Not Found");
                    $this->error('パラメータ未設定');
                    exit;
                }
        }
        
        //それ以外はページごとのコントローラを設定
        else{
            $pageName = strtolower($params[0]);
        }

        //非ログイン時
        if(!isset($_SESSION['user_id'])){
            //自動ログイン
            if(isset($_COOKIE["zmb"]) && AutoLoginDatabase::verify() != false){
                $user = new User();
                $user_num =  AutoLoginDatabase::verify();
                $user->login($user_num);
                $key = genToken(session_id() . strval(time()));
                if(AutoLoginDatabase::update_key($key)){
                    setcookie('zmb',$key, time()+60*60*24*30*6,'/','',false,true);
                }
                 header('Location: /');
                 exit;
            }
            //自動ログイン設定がなければゲスト扱い
            else if(!in_array($pageName,PATH_NOTLOGIN)) {
                $_SESSION['online'] = false;
                $_SESSION['token'] = genToken(session_id());
                $_SESSION['user_id'] = "@guest";
                $_SESSION['user_num'] = -1;
            }
        }

        //各ページ実行
        $controllerName = ucfirst($pageName) . 'Controller';
        //コントローラがあれば実行
        if(class_exists($controllerName) && strtolower($pageName) != 'api'){
            // ページごとにヘッダ調整
            $this->header->addTail('<div class="header-nav">');
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] === '@guest'){
                $this->header->addTail('<a href="/home/">ホーム</a> ');
                $this->header->addTail('<a href="/logout/">トップ</a> ');
                $this->header->addTail('<a href="/login/">ログイン</a> ');
                $this->header->addTail('<a href="/registration/">登録</a>');
            }
            else{
            switch($pageName){
                    case '':
                        break;
                    case 'top':
                        break;
                    case 'login':
                    case 'registration':
                        break;
                    case 'user':
                    case 'jump':
                    case 'list':
                    case 'content':
                    case 'search':
                        $this->header->addTail('<a href="/home/">ホーム</a> ');
                        $this->header->addTail('<a href="/mylist/">マイリスト</a> ');
                        $this->header->addTail('<p class="annotation" style="float:right"><a href="/content/a/diglue">ヘルプ</a></p>');
                        break;
                    case 'home':
                    case 'mylist':
                    case 'account';
                        $this->header->addTail('<a href="/home/">ホーム</a> ');
                        $this->header->addTail('<a href="/logout/">ログアウト</a>');
                        $this->header->addTail('<p class="annotation" style="float:right"><a href="/content/a/diglue">ヘルプ</a></p>');
                        break;

                    default:
                        break;
                }
            }
            $this->header->addTail('</div>');
             if($pageName !== 'top'){
                $this->header->assign(' /  ' . $pageName, 'page');
            }

            $this->header->display();
            $controller = new $controllerName($pageName. '.tpl');
            $controller->run();
            $this->footer->display();
        }

        //コントローラがなければ404
        else{
            header("HTTP/1.1 404 Not Found");
            include(PATH_ROOT . 'missing.php');
            exit;
        }
    }

    private function error($msg = null){
        $error_res = [
                    'error' => [ 'message' => null]
                    ];
        $error_res['error']['message'] = $msg;
        echo json_encode($error_res);
        echo $msg;  //debug
    }
}


?>
