<?php
class MyListController extends Controller{

    private $user;
    private $posts;
    private $mylist;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->user = new User($_SESSION['user_num']);
        $this->mylist = new CheckList($_SESSION['user_num']);
        $this->view_module['list_search'] = 'list_search.module.tpl';
        $this->me = new User($_SESSION['user_num']);
    }

    public function run(){
        if(!$this->me->is_exist()){
            header("HTTP/1.1 404 Not Found");
            echo 'ユーザが存在しません';
            exit;
        }
//--リスト要素編集
        if($this->request->chkToken()){  //トークンチェック
            $updateData = [];
            $updateData = $this->request->getPostValues();
            $this->mylist->updateElement($updateData);
            header('Location: /mylist');
        }

        //--表示周り
        foreach($this->view_module as $tag => $extemplate){
            $this->view->insertExternalTemplate($extemplate,$tag);
        }
        $this->view->assign($this->user->getID(),'user_id');
        $this->view->assign(genToken(session_id()),'token');
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

        if(empty($this->mylist->getList())){
            $this->view->addTail('<p>まだリストにはなにもありません</p>');
        }
        $this->view->display();
    }

}
?>
