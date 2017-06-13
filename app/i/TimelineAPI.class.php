<?php
class TimelineAPI extends APIController{
/*  継承
    protected $params;
    protected $request;
    protected $method;
 */

    //返すカラム
    private static $retColumns = ['post_num' => null, 'user_id' => null, 'content_name' => null, 'content_link' => null,
                    'reference_url' => null, 'post_comment' => null, 'genre' => null, 'tags' => null, 'dig' => null,'nsfw' => null,
                    'post_image_name' => null,  'regdate' => null,
                    'user_name' => 'null','user_icon' => 'noicon.png'];

    private $offset = PHP_INT_MAX;
    private $latest = PHP_INT_MAX;
    private $tags = '';
    private $nsfw = false;

    function __construct(){
        parent::__construct();
        if(isset($_GET['offset'])){
            $this->offset = $this->request->getGetValues('offset');
        }
        if($this->request->getPostValues('latest') != null){
            $this->latest =  $this->request->getPostValues('latest');
        }

        if($this->request->getGetValues('tags') != null){
            $this->tags = $this->request->getGetValues('tags');
        }
        
        //nsfw
        if($this->request->getGetValues('nsfw') != null || $this->request->getPostValues('nsfw') != null){
            $this->nsfw = true;
        }
        else{
            $this->nsfw = false;
        }
        
    }

    protected function post(){
        if( (isset($this->params[0]) && $this->params[0] == 'update') ||
            (isset($this->params[1]) && $this->params[1] == 'update') || ($this->params[0] == 'user' && $this->params[2] == 'update') ) {

            $this->tags = $this->request->getPostValues('tags');

            if(empty($this->params) || $this->params[0] == 'stream' || substr($this->params[0],0,1) == '?'){
                $rawResult = PostDataDatabase::selectUnreadAllPosts($this->tags,$this->latest,$this->nsfw);
                $this->outputResult($rawResult);
            }

            //フォローしているユーザから取得
            else if($this->params[0] == 'friends'){
                $me = new User($_SESSION['user_id']);
                if($me->is_exist()){
                    $usernums = $me->followingUsersNum();
                    $usernums[] = $me->getNum();
                    $rawResult = PostDataDatabase::selectUnreadPostsByUser($usernums ,$this->tags,$this->latest,$this->nsfw);
                    $this->outputResult($rawResult);
                }
                else{
                    $this->error('user does not exist');
                }
            }

            //ユーザを指定して取得
            else if($this->params[0] == 'user'){
                if(isset($this->params[1])){
                    $user = new User($this->params[1]);
                    if($user->is_exist()){
                        $rawResult = PostDataDatabase::selectUnreadPostsByUser($user->getNum(),$this->tags,$this->latest,$this->nsfw);
                        $this->outputResult($rawResult);
                    }
                    else{
                        $this->error('user does not exist');
                    }
                }
            }
        }
        else{
            $this->error('incorrect parameter');
        }
    }

    protected function get(){
        //ストリーム取得 (パラメータなし or stream or GETパラメータあり)
        if(empty($this->params) || $this->params[0] == 'stream' || substr($this->params[0],0,1) == '?'){
            $rawResult = PostDataDatabase::selectAllPosts($this->tags,$this->offset,$this->nsfw);
            $this->outputResult($rawResult);
        }

        //フォローしているユーザから取得
        else if($this->params[0] == 'friends'){
            $me = new User($_SESSION['user_id']);
            if($me->is_exist()){
                $usernums = $me->followingUsersNum();
                $usernums[] = $me->getNum();
                $rawResult = PostDataDatabase::selectByUser($usernums ,$this->tags,$this->offset,$this->nsfw);
                $this->outputResult($rawResult);
            }
            else{
                $this->error('user does not exist');
            }
        }

        //ユーザを指定して取得
        else if($this->params[0] == 'user'){
            if(isset($this->params[1])){
                $user = new User($this->params[1]);
                if($user->is_exist()){
                    $rawResult = PostDataDatabase::selectByUser($user->getNum(),$this->tags,$this->offset,$this->nsfw);
                    $this->outputResult($rawResult);
                }
                else{
                    $this->error('user does not exist');
                }
            }
        }
        else{
            $this->error('incorrect parameter');
        }

    }

    //DBから取得したデータをユーザに返す形に整形して出力(カラムの選択、特殊文字エスケープ等)
    private function outputResult($rawResult){
        $ret = [];
        $i = 0;
        foreach($rawResult as $key => &$res){
            $res = $res + UserDataDatabase::selectByID($res['user_id']);
            $buf = explode(',',$res['tags']);
            $res['tags'] = [];
            foreach($buf as $t){
                if($t !== ''){
                    $res['tags'][] = $t;
                }
            }
            array_pop($res['tags']);   //最後のタグはコンテンツ名なので除外
            $buf = explode(',',$res['post_image_name']);
            $res['post_image_name'] = [];
            foreach($buf as $img){
                if($img !== ''){
                    $res['post_image_name'][] = $img;
                }
            }
            $res['content_link'] = ex_urlencode($res['content_name']);
            $res['content_link'] = preg_replace('/[\.]/','%2E',$res['content_link']);
            $res['content_link'] = preg_replace('/[\/]/','%2F',$res['content_link']);
            $res['content_link'] = preg_replace('/\x5c/','\\',$res['content_link']);
            $ret[$i] = self::$retColumns;
            $ret[$i] = array_overwrite($ret[$i],str_escape($res)); //エスケープした上で上書き
            $i++;
        }
        unset($res);
        unset($i);
        //$ret = $this->setReturnValues($rawResult);
        echo json_encode($ret);
    }

    private function setReturnValues($result){
        $ret = [];
        $i = 0;
        foreach($result as $e){
            $ret[$i] = self::$retColumns;
            $ret[$i] = array_overwrite($ret[$i],str_escape($e)); //エスケープした上で上書き
            $i++;
        }
        unset($i);
        return json_encode($ret);
    }
}
?>
