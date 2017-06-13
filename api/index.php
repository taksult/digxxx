<?php
include_once('/vagrant/diglue/app/definition.php');
include_once(PATH_LIBRARY);
/*
session_cache_expire(0);
session_cache_limiter('private_no_expire');
*/
header( 'Content-Type: application/json; charset=UTF-8');
Database::initDB();
UserDataDatabase::init();
FollowListDatabase::init();
PostDataDatabase::init();
CheckListDatabase::init();
$dispatcher = new APIDispatcher();
$dispatcher->dispatch();

class APIDispatcher{

    public function dispatch(){
        self::controlCORS();
        if(self::authorize() || true){
            //URIからパラメータ抽出してapiを呼ぶ
            unset($_SESSION['params']);  //パラメータはページごとにリセット
            $uri = $_SERVER['REQUEST_URI'];
            $params;
            if($uri != null){
                $params = explode('/',$uri);
            }
            array_shift($params);
            while(end($params) == null && !empty($params)){
                array_pop($params);
            }

            if(isset($params[0]) && file_exists(PATH_API . 'v' . $params[0])){
                if(isset($params[1])){
                    $class = ucfirst($params[1]) . 'API';
                    $path = PATH_API . 'v' . $params[0] . DS . $class . '.class.php'; //$params[0]はバージョン指定
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
            else{
                header("HTTP/1.1 404 Not Found");
                $this->error('バージョンが不正です');
                exit;
            }
        }
        else{
            header("HTTP/1.1 405");
            $this->error('Authorization failed');
            exit;
        }
    }

    private function authorize(){
        //そのうちちゃんと処理書く
        return false;
    }

    //CORS対応
    private function controlCORS(){
        header('Access-Control-Allow-Origin: http://diglue.com');   //後々httpsに直す
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');        //許可するメソッド
        header('Access-Control-Allow-Credentials: true');          //認証情報送信の可否
        header('Access-Control-Max-Age: 1800');       //プリフライトの有効時間

        session_start(); //session.auto_startはオフにしてある
    }

    private function error($msg = null){
        $error_res = [
                    'error' => [ 'message' => null]
                    ];
        $error_res['error']['message'] = $msg;
        echo json_encode($error_res);
        //echo $msg;  //debug
    }

}


?>
