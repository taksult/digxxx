<?php
//  /i/relationship
//  GET
//                 /lookup/?targetnum= &target_id=
//    
//  POST
//                 /create
//                 /remove

 class RelationshipAPI extends APIController{
/*  継承
    protected $params;
    protected $request;
    protected $method;
 */
    private static $retColumns = ['user_num' => null, 'user_id' => null,'user_name' => null,
                        'user_followed_by' => null, 'user_follow_to' => null, 'block' => null, 'blocked' => null,
                        'regdate' => null, 'moddate' => null];

    private $target = null;
    private $target_num = null;
    private $target_id = null;
    private $taste_content_num = null;
    private $taste_content_id = null;
    private $content = null;
    private $me = null;

    function __construct(){
        parent::__construct();

        $this->target = new User();

        if($this->method == 'get'){
            if($this->request->getGetValues('target_num') != null){
                $this->target_num = intval($this->request->getGetValues('target_num'));
            }
            if($this->request->getGetValues('target_id') != null){
                $this->target_id = $this->request->getGetValues('target_id');
            }
            if($this->request->getGetValues('content_num') != null){
                $this->taste_content_num = intval($this->request->getGetValues('content_num'));
            }
            if($this->request->getGetValues('content_id') != null){
                $this->taste_content_id = urldecode($this->request->getGetValues('content_id'));
            }

        }
        else if($this->method == 'post'){
            if($this->request->getPostValues('target_num') != null){
                $this->target_num = $this->request->getPostValues('target_num');
            }
            if($this->request->getPostValues('target_id') != null){
                $this->target_id = $this->request->getPostValues('target_id');
            }
        }

        //リクエストしたユーザをセット
        $this->me = new User($_SESSION['user_num']);
        if(!$this->me->is_exist() && $_SESSION['user_num'] !== -1){
            $this->error('user does not exist');
            exit;
        }

        //ターゲットユーザをセット
        if($this->target_num !== null){
            $this->target = new User($this->target_num);
        }
        else if($this->target_id !== null){
            $this->target = new User($this->target_id);
        }
        else if($this->params[0] != 'taste'){
            $this->error('no target is set');   //対象ユーザが設定されていなければエラーで終了
            exit;
        }
        //コンテンツをパラメータにとる処理用
        else{
            $this->content = new Content($this->taste_content_num);
            if(!$this->content->is_exist()){
                $this->content = new Content($this->taste_content_id);
            }
            if(!$this->content->is_exist()){
                $this->error('no target content is set');   //対象ユーザが設定されていなければエラーで終了
                exit;
            }
        }

        //ターゲットが自分自身ならエラー
        if($this->me->getNum() === $this->target->getNum() && $_SESSION['user_num'] !== -1){
            $this->error('cannot target yourself');
            exit;
        }
    }

    protected function get(){
        if($this->params[0] == 'lookup'){
            $relation = $this->me->lookup($this->target->getNum());
            echo json_encode($relation);
        }

        //相性
        else if($this->params[0] == 'taste' ){
            if($this->target->is_exist()){
                $res = DBProcedure::tasteWithUser($this->me->getNum(),
                                    $this->target->getNum(),
                                    $this->request->getGetValues('genre'),
                                    $this->request->getGetValues('category'),
                                    $this->request->getGetValues('tags')
                                );
                $res['user_id'] = $this->target->getID();
                $res['user_name'] = $this->target->getName();
                $res['user_icon'] = $this->target->getIcon();
                foreach($res as &$col){
                    $col = str_escape($col);
                }
                unset($col);
                echo json_encode($res);
                exit;
            }
            else if($this->content->is_exist()){
                $res = DBProcedure::tasteWithContentFollowers(
                                    $this->me->getNum(),
                                    $this->content->getNum(),
                                    $this->request->getGetValues('genre'),
                                    $this->request->getGetValues('category'),
                                    $this->request->getGetValues('tags'),
                                    intval($this->request->getGetValues('limit')),
                                    intval($this->request->getGetValues('offset'))
                                );
                $ret = [];
                foreach($res as &$e){
                    if($e['target_num'] != $this->me->getNum()){
                        $user = new User(intval($e['target_num']));
                        $e['user_id'] = $user->getID();
                        $e['user_name'] = $user->getName();
                        $e['user_icon'] = $user->getIcon();
                        $ret[] = $e;
                    }
                }
                unset($e);
                $this->outputResult($ret);
            }
        }
        else{
            $this->error('parameter is not found');
        }
    }

    protected function post(){
        if($this->target->is_exist()){
            //フォロー
            if($this->params[0] == 'create'){
                if( FollowListDatabase::follow($this->me->getNum(), $this->target->getNum()) ){
                    echo json_encode(['result'=>'succsess','action' => 'follow']);
                }
                else{
                    $this->error('database error');
                }
            }
            else if($this->params[0] == 'remove'){
                if( FollowListDatabase::remove($this->me->getNum(), $this->target->getNum()) ){
                    echo json_encode(['result'=>'succsess','action' => 'remove']);
                }
                else{
                    $this->error('database error');
                }
            }
            else{
                $this->error('parameter is not found');
            }
        }
        else{
            $this->error('target does not exist');    //対象ユーザが存在しなければエラーで終了
        }
    }

    private function outputResult($rawResult){
        $ret = [];
        foreach($rawResult as $user){
            foreach($user as &$col){
                $col = str_escape($col);
            }
            unset($col);
            $ret[] = $user;
        }
        echo json_encode($ret);
    }
}
?>
