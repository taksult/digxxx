<?php
class LogoutController extends Controller{

    function __construct(){

    }

    public function run(){
        //ログアウト処理
        $_SESSION['online'] = false;
        setcookie("PHPSESSID", '', time() - 1800, HOSTNAME);
        AutoLoginDatabase::remove_key();
        setcookie('zmb','', time() - 1800,'/','',true,true);
        $_SESSION = array();
        session_destroy();
        header('Location: /');
        exit;
    }
}


?>
