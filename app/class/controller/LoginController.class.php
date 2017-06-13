<?php
class LoginController extends Controller{

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
    }

    public function run(){
        if(!isset($_SESSION['token'])){
            $_SESSION['online'] = false;
            $_SESSION['token'] = genToken(session_id());
        }
        //ゲストユーザでログインしていたらログアウト
        if(isset($_SESSION['user_id']) && $_SESSION['user_id'] === '@guest'){
            setcookie("PHPSESSID", '', time() - 1800, HOSTNAME);
            $_SESSION = array();
            session_destroy();
            header('Location: /login/');
            exit;
        }
        //すでにログイン済みならトップへリダイレクト
        else if(isset($_SESSION['user_id'])){
             header('Location: /');
             exit;
        }
        //直前の入力を保持
        if(isset($_POST['user_id'])){
            $this->view->assign($_POST['user_id'], 'posted');
        }
        $this->view->assign(genToken(session_id()), 'token');
        $this->view->display();

        //$form = $this->request->getPostValues();

        //ログイン処理
        if($this->request->chkToken()){
            $user_num = User::authenticate();
            if($user_num != false){
                User::login($user_num);
                //自動ログイン有効にチェックがあればログイン用cookieセット
                if($this->request->getPostValues('auto-login') != null){
                    $key = genToken(session_id() . strval(time()));
                    if(AutoLoginDatabase::save_key($key)){
                        setcookie('zmb',$key, time()+60*60*24*30*6,'/','',false,true);
                    }
                    else{
                        echo '<script type="text/javascript">alert("ログイン保持設定に失敗しました")</script>';
                    }
                }
                header('Location: /');
                exit;
            }
            else{
                exit;
            }
        }
    }
}


?>
