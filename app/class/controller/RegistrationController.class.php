<?php
class RegistrationController extends Controller{

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
            header('Location: /registration/');
            exit;
        }
        
        if(isset($_SESSION['online']) && $_SESSION['online'] == true){
             header('Location: /');
             exit;
        }

        if(isset($_POST['user_id'])){
            $this->view->assign($_POST['user_id'], 'posted');
        }
        $this->view->assign(genToken(session_id()), 'token'); 
        $this->view->display();
        
        if($this->request->chkToken()){
            $user_num = User::register();
            if($user_num != false){
                echo '登録完了';
                User::login($user_num);
                header('Location: /');
                exit;
            }
        }
    }
}


?>
