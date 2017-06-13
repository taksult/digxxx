<?php
class ContentController extends Controller{

    private $content = null;

    function __construct($path){
        $this->request = new Request();
        $this->view = new View();
        $this->view_module['add_checklist'] = 'addchecklist_button.module.tpl';
        $this->view_module['article_edit'] = 'article_edit.module.tpl';
        //$this->content = new Content();
    }

    public function run(){
        $request_name = null;
        //urlからコンテンツ名を設定

        if(isset($_SESSION['params'][2])){
            $request_name = preg_replace('/.*content\/.*?\//u','',$_SERVER['REQUEST_URI']);    //$request_name = $_SESSION['params'][2];
            //GETパラメータの分離
            $m = [];
            preg_match('/(.*?)\/\?(.*)$/',$request_name,$m);
            if(!empty($m)){
                $request_name = preg_replace('/(.*?)\/\?(.*)$/',"$1",$request_name);
            }
            //スペース(半角,全角)の処理
            $request_name = preg_replace('/\+/u','＋',$request_name);
            $request_name = str_replace('　',' ',urldecode($request_name));
            $request_name = preg_replace('/＋/u','+',$request_name);
            $spell = $request_name;
            $request_name = preg_replace('/^\s*(.*)\s*$/u',"$1",$request_name);
            $request_name = preg_replace('/\s{2,}/u',' ', $request_name);
            if($request_name == ''){
                header("HTTP/1.1 404 Not Found");
                include(PATH_ROOT . 'missing.php');
                exit;
            }
            $this->content = new Content(($request_name));
            $content_data = $this->content->getData(); 
        }
        else{
            header("HTTP/1.1 404 Not Found");
            include(PATH_ROOT . 'missing.php');
            exit;
        }

        //記事表示
        if($_SESSION['params'][1] == 'a'){
            //POSTフォームを受け取った場合の処理
            if($this->request->chkToken()){
                if($_SESSION['user_num'] !== -1){
                    //新規コンテンツ登録
                    if($this->request->getPostValues('form-type') == 'registration'){
                        $Data = ['content_name' => null,'spell' => null];
                        if(isset($_POST['content_name']) && isset($_POST['spell']) ){
                            $Data = array_overwrite($Data,$_POST);
                            if(ContentDataDatabase::createContent($Data)){
                                echo "<script>alert('登録しました')</script>";
                                header('Location: /content/a/'.ex_urlencode($request_name));
                                exit;
                            }
                            else{
                                echo "<script>alert('登録に失敗しました')</script>";
                            }
                        }
                        else{
                            echo "<script>alert('登録に失敗しました')</script>";
                        }
                    }
                    //コンテンツ記事編集
                    else if($this->request->getPostValues('form-type') == 'article-edit'){
                        $updateData = $this->request->getPostValues();
                        if($updateData['rlsdate'] != ''){
                            $date = new DateTime();
                            $date->setDate($updateData['rlsdate'], 1, 1);
                            $updateData['rlsdate'] = $date->format('Y-m-d');
                        }
                        else{
                            unset($updateData['rlsdate']);
                        }
                        if($this->content->is_exist() && $this->content->update($updateData)){
                            echo "<script>alert('更新しました')</script>";
                            header('Location: /content/a/'. $request_name);
                            exit;
                        }
                        else{
                            echo "<script>alert('更新に失敗しました')</script>";
                        }
                    }
                    $this->content = new Content($request_name);
                    $content_data = $this->content->getData();
                }
                else{
                    echo 'ゲストユーザーはコンテンツ登録・記事の編集ができません';
                }
            }
            //未登録のコンテンツの場合
            if(!$this->content->is_exist()){
                $this->view->setTemplate('content_not_exist.tpl');
                $this->view->assign($request_name,'content_name');
                $this->view->assign(ex_urlencode($request_name),'content_link');
                $this->view->assign(strtolower($request_name),'registration_name');
                $this->view->assign($spell,'spell');
                $this->view->assign(genToken(session_id()),'token');
                $this->view->display();
            }

            //登録済みコンテンツの場合
            else{
                $this->view->setTemplate('content.tpl');
                $this->view->insertExternalTemplate($this->view_module['add_checklist'],'add_checklist');
                $this->view->insertExternalTemplate($this->view_module['article_edit'],'article_edit');
                $this->view->assignAll($content_data);
                $this->view->insertExternalTemplate($this->content->getArticle(),'article',true);
                $this->view->insertExternalTemplate($this->content->getRawArticle(),'raw_article',true);
                $this->view->assign(ex_urlencode($request_name),'content_link');
                $this->view->assign($this->content->countUsers(),'users');
                $this->view->assign(genToken(session_id()),'token');
                //登録ジャンル一覧を取得して編集用datalistに追加
                //$genre_list = ContentDataDatabase::extractGenres();
                $genres = [];
                foreach(GENRES as $g){
                    $genres[]['genre'] = $g;
                }
                $categories = [];
                foreach(CATEGORIES as $c){
                    $categories[]['category'] = $c;
                }

                $this->view->assignLoopElements($genres,'genre_list');
                $this->view->assignLoopElements($categories,'category_list');
                $years = [];
                $years[]['year'] = '';
                $current_year = intval(date('Y'));
                for($i = $current_year; $i > $current_year - 100; $i--){
                    $years[]['year'] = $i;
                }
                unset($i);
                $this->view->assignLoopElements($years,'years');

                $this->view->display();
            }
        }

        //チェック中のユーザリスト
        else if($_SESSION['params'][1] == "u"){
            if(!$this->content->is_exist()){
                echo '指定されたコンテンツはデータベースに登録されていません';

            }
            else{
                $this->view->setTemplate('content_users.tpl');
                $me = new User($_SESSION['user_id']);
                $this->view->assign($me->getID(),'user_id');
                $this->view->assignAll($content_data);
                $cond = [];
                $cond['genre'] = $this->request->getGetValues('genre');
                $cond['category'] = $this->request->getGetValues('category');
                $cond['tags'] = $this->request->getGetValues('tags');
                $genres = []; 
                foreach(GENRES as $g){
                    $genres[]['genre'] = $g;
                }
                $genres[]['genres'] = '';
                $categories = [];
                foreach(CATEGORIES as $c){
                    $categories[]['category'] = $c;
                }
                $categories[]['category'] = '';
                $this->view->assignLoopElements($genres,'genre_list');
                $this->view->assignLoopElements($categories,'category_list');

                $this->view->assignAll($cond);
                $this->view->display();
            }

        }
    }
}
?>
