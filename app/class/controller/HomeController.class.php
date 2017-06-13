<?php
class HomeController extends Controller{

    private $me;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->view_module['controller'] = 'timeline_controller.module.tpl';
        $this->view_module['timeline'] = 'timeline.module.tpl';
        $this->me = new User($_SESSION['user_num']);
    }

    public function run(){
        //モジュールテンプレートを読み込み
        foreach($this->view_module as $tag => $extemplate){
            $this->view->insertExternalTemplate($extemplate,$tag);
        }

        //ユーザ情報アサイン
        $this->view->assignAll($this->me->getUserData());
        $this->view->assign($this->me->getID(),'me');
        $this->view->assign(genToken(session_id()), 'token');
        $this->view->assign($this->me->countFollowing(),'followingcount');
        $this->view->assign($this->me->countFollower(),'followercount');
        $this->view->assign(PATH_IMG_ICON,'path_img_icon');
        $this->view->assign(urlencode($this->me->getID()),'user_link');
        $this->view->assignLoopElements($this->me->getFFList()['following'], 'following');
        if($this->me->is_nsfw()){
            $this->view->assign('checked','nsfw_checked');
        }
        $std_tags = [];
        foreach($this->me->getStdTags() as $t){
            $std_tags[] = ['tag' => $t];
        }
        $this->view->assignLoopElements($std_tags,'std_tags');

        //表示
        $this->view->display();
    }
}


?>
