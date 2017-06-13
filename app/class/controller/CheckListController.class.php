<?php
class CheckListController extends Controller{

    private $user;
    private $posts;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->user = new User($_SESSION['params'][1]);
        }

    public function run(){

       //ユーザが存在しない場合
        if(!$this->user->is_exist()){
            header("HTTP/1.0 404 Not Found");
            echo '<br>誰もいないページ';
            exit;
        }

        //非公開アカウントの場合
        else if($this->user->is_hidden() && !in_array($_SESSION['user_num'],$this->user->followedUsersNum(),true)){
            echo '<br>このユーザのプロフィールは非公開です';
            $publicInfo = ['user_id' => $this->user->getID(), 'user_icon' => $this->user->getIcon()];
            $this->view->assignAll($publicInfo);
            $this->view->display();
        }

        //公開ユーザ orフォローしている非公開ユーザの場合
        else{
            $this->view->assignAll($this->user->getUserData());
            $this->view->assignLoopElements($this->user->getFFList()['following'], 'following');
            $this->view->assign($this->user->countFollowing(),'followingcount');
            $this->view->assign($this->user->countFollower(),'followercount');
            $this->posts = PostDataDatabase::selectByUser($this->user->getNum());
            $this->posts = array_reverse($this->posts);
            $this->view->assignLoopElements($this->posts,'post');
            $this->view->display();
        }
    }

}


?>
