<?php
class ListController extends Controller{

    private $user;
    private $list;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->user = new User($_SESSION['params'][1]);
        $this->list = new CheckList($this->user->getNum());
        $this->view_module['list_search'] = 'list_search.module.tpl';
    }

    public function run(){
        //ユーザが存在しない場合
        if(!$this->user->is_exist()){
            header("HTTP/1.0 404 Not Found");
            echo '<br>誰のでもないリスト';
            exit;
        }

        $this->view->assign($this->user->getID(),'user_id');

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
        //リストに要素があれば表示処理
        if(!empty($this->list->getList())){
            foreach($this->view_module as $tag => $extemplate){
                $this->view->insertExternalTemplate($extemplate,$tag);
            }
            $genres = [];
            $genres[]['genres'] = '';
            foreach(GENRES as $g){
                $genres[]['genre'] = $g;
            }
            $categories = [];
            $categories[]['category'] = '';
            foreach(CATEGORIES as $c){
                $categories[]['category'] = $c;
            }
            $this->view->assignLoopElements($genres,'genre_list');
            $this->view->assignLoopElements($categories,'category_list');
        }
        //リストが空の場合
        else{
            $this->view->addTail('<p>このユーザーのリストにはなにもありません</p>');
        }

        $this->view->display();
    }

}
?>
