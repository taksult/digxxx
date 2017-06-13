<?php
class SearchController extends Controller{

    private $me;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
        $this->me = new User($_SESSION['user_num']);
    }

    public function run(){
        $input = str_replace("　"," ",rawurldecode($this->request->getGetValues('keyword')));
        $this->view->assign($input,'keyword');
        $content_res = ContentDataDatabase::searchByKeys($input);
        $contents = [];
        if(!empty($content_res)){
            $i = 0;
            foreach($content_res as $e){
                $c = new Content($e['content_num']);
                $contents[$i] = $c->getData();
                $contents[$i]['content_link'] = ex_urlencode($contents[$i]['content_name']);
            }
            unset($i);
            $this->view->assignLoopElements($contents,'content_list');
        }
        if(empty(ContentDataDatabase::searchByKey($input))){
            $input_link = rawurlencode($input);
            $this->view->insertExternalTemplate('<p class="annotation"> [::keyword] はまだデータベースに登録されていないようです<a href="/content/a/[::input_link]"> [::keyword] を登録する</a></p>','no_result',true);
            $this->view->assign($input_link,'input_link');
        }

        $user_res = UserDataDatabase::searchByKey($input);
        if(!empty($user_res)){
            //$List = $this->me->getFollowingList();

            foreach($user_res as &$e){
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
            $this->view->assignLoopElements($user_res,'user_list');

        }
        //表示
        $this->view->display();
    }
}


?>
