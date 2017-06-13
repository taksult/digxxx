<?php
class UserController extends Controller{

    private $user;
    private $posts;
    private $me;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->view_module['controller'] = 'timeline_controller_u.module.tpl';
        $this->view_module['timeline'] = 'timeline.module.tpl';
        $this->user = new User(isset($_SESSION['params'][2]) ? $_SESSION['params'][2] : null);
        $this->me = new User($_SESSION['user_num']);
    }

    public function run(){

        $this->view->assign(PATH_IMG_ICON,'path_img_icon');

       //ユーザが存在しない場合
        if(!$this->user->is_exist()){
            header("HTTP/1.0 404 Not Found");
            echo '<br>誰もいないページ';
            exit;
        }

        //非公開アカウントの場合
        if($this->user->is_hidden() && !in_array($_SESSION['user_num'],$this->user->followedUsersNum(),true) && $this->user->getNum() != $this->me->getNum() ){
            echo '<br>このユーザのプロフィールは非公開です';
            $publicInfo = ['user_id' => $this->user->getID(), 'user_icon' => $this->user->getIcon()];
            $this->view->assignAll($publicInfo);
            $this->view->display();
            exit;
        }

        //公開ユーザ orフォローしている非公開ユーザ
        else if($_SESSION['params'][1] == 'p' || $_SESSION['params'][1] == ''){
            //モジュールテンプレートを読み込み
            foreach($this->view_module as $tag => $extemplate){
                $this->view->insertExternalTemplate($extemplate,$tag);
            }
            $this->view->assignAll($this->user->getUserData());
            $this->view->assignLoopElements($this->user->getFFList()['following'], 'following');
            $this->view->assign($this->user->countFollowing(),'followingcount');
            $this->view->assign($this->user->countFollower(),'followercount');
            
            if($this->me->is_nsfw()){
                $this->view->assign('checked','nsfw_checked');
            }

            $me = new User($_SESSION['user_id']);
            //自分のページでなければフォローボタンを表示
            if(!($me->getNum() == $this->user->getNum())){
                $this->view->insertExternalTemplate('follow_button.module.tpl','follow_button');
                if($me->lookup($this->user->getNum())['following']){
                    $this->view->assign('following','is_follow');
                    $this->view->assign('true','follow_status');
                }
                else{
                    $this->view->assign('follow','is_follow');
                    $this->view->assign('false','follow_status');
                }
            }
            $this->view->assign($this->me->getID(),'me');
            $this->view->display();
        }
        else if($_SESSION['params'][1] == 'ft' || $_SESSION['params'][1] == 'fb'){
            $this->view->setTemplate('user_followlist.tpl');
            $this->view->insertExternalTemplate('follow_button.module.tpl','follow_button');
            $this->view->assignAll($this->user->getUserData());
            if($_SESSION['params'][1] == 'ft'){
                $List = $this->user->getFollowingList();
                $this->view->assign('フォロー','type');
            }
            else{
                $List = $this->user->getFollowedList();
                $this->view->assign('フォロワー','type');
            }

            foreach($List as &$e){
                if($this->me->lookup($e['user_num'])['following']){
                    $e['is_follow'] = 'following';
                    $e['follow_status'] = 'true';
                }
                else{
                    $e['is_follow'] = 'follow';
                    $e['follow_status'] = 'false';
                }
                if($e['user_num'] == $this->me->getNum()){
                    $e['is_follow'] = 'you';
                    $e['follow_status'] = '';
                }
            }
            unset($e);
            $this->view->assignLoopElements($List,'FFList');
            $this->view->display();
        }
        else{
            header("HTTP/1.1 404 Not Found");
            include(PATH_ROOT . 'missing.php');
            exit;
        }
    }

}


?>
