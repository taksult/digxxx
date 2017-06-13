<?php
class AccountController extends Controller{

    private $me;
    private $posts = [];

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->me = new User($_SESSION['user_num']);
    }

    public function run(){
        if(!$this->me->is_exist()){
            header("HTTP/1.1 404 Not Found");
            echo 'ユーザが存在しません';
            exit;
        }
        //プロフィール変更
        if(!isset($_SESSION['params'][1]) || $_SESSION['params'][1] == null){

            if($this->request->chkToken()){
                $updateData = [];
                $updateData['user_name'] = $this->request->getPostValues('user_name');
                $updateData['user_comment'] = $this->request->getPostValues('user_comment');
                //stream
                if($this->request->getPostValues('stream') == '1'){
                    $updateData['stream'] = false;
                }
                else{
                    $updateData['stream'] = true;
                }
                //show_nsfw
                if($this->request->getPostValues('show_nsfw') == '1'){
                    $updateData['show_nsfw'] = true;
                }
                else{
                    $updateData['show_nsfw'] = false;
                }

                $this->me = new User($_SESSION['user_num']);

                $user_icon = $this->me->getIcon();

                //アイコン更新処理
                $iconupdate = true;
                $upload_icon = $_FILES['upload_icon'];
                if($upload_icon['error'] !== 4){
                    $user_icon = uploadImageFile($upload_icon,PATH_ROOT.PATH_IMG_ICON,'upload_icon');
                    if(!$user_icon){
                        echo '<br>アイコンの変更に失敗しました<br>';
                        $iconupdate = false;
                    }
                    else{
                        $updateData['user_icon'] = $user_icon;
                    }
                }

                if($this->me->update($updateData)){
                    echo 'プロフィールを更新しました';
                    if($iconupdate){
                        header('Location: /account/');
                        exit;
                    }
                }
                else{
                    echo '更新に失敗しました';
                }
            }

            //ユーザ情報アサイン
            $this->view->assignAll($this->me->getUserData());
            if($this->me->is_stream() == false){
                $this->view->assign('checked','stream_checked');
            }
            if($this->me->is_nsfw() == true){
                $this->view->assign('checked','nsfw_checked');
            }
            $this->view->assign(genToken(session_id()), 'token');
            $this->view->assign(PATH_IMG_ICON,'path_img_icon');
            $this->view->assignLoopElements($this->me->getFFList()['following'], 'following');
            //表示
            $this->view->display();
        }

        //パスワード変更
        else if($_SESSION['params'][1] == 'p'){
            $this->view = new View('account_pass.tpl');
            $this->view->assignAll($this->me->getUserData());
            $this->view->assign(genToken(session_id()), 'token');

            $this->view->display();

            if($this->request->chkToken()){
                $req = $this->request->getPostValues();
                $current = $this->request->getPostValues('current_pass');
                $p1 = $this->request->getPostValues('new_pass');
                $p2 = $this->request->getPostValues('confirm');
                if($this->me->changeUserPass($current,$p1,$p2)){
                    echo 'パスワードを変更しました';
                }
            }
        }
        else{
            header("HTTP/1.1 404 Not Found");
            include(PATH_ROOT . 'missing.php');
            exit;
        }
    }
}


?>
