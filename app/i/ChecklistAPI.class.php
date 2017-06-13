<?php
class ChecklistAPI extends APIController{
/*  継承
    protected $params;
    protected $request;
    protected $method;
*/

    private static $retColumns = ['content_num'  => null,'content_name' => null, 'content_link' => null,
                                'spell' => null, 'genre' => null,
                                'user_comment' => null, 'user_ref' => null, 'tags' => null ,'user_count' => null,
                                'user_image' => null,'regdate' => null, 'moddate' => null,
                                'favorite' => null,'hidden' => null ,'origin' => null
                            ];

    private static $escapeCols = ['content_name','content_link','spell','genre','user_comment','user_ref','tags'];

    private static $ElementDataTemp = [ 'content_num' => null, 'content_name' => null, 'user_num' => null,
                                        'user_comment' => null, 'user_ref' => null, 'user_image' => 'noimage.png',
                                ];

    private $target_num = null;
    private $target_id = null;

    private $targetContent = null;
    private $targetUser = null;
    private $me = null;
    private $mylist = null;

    function __construct(){
        parent::__construct();
        $this->targetContent = new Content();
        $this->targetUser = new User();
        $this->me = new User();

         if($this->method == 'get'){
            if($this->request->getGetValues('target_num') != null){
                $this->target_num = intval($this->request->getGetValues('target_num'));
            }
            if($this->request->getGetValues('target_id') != null){
                $this->target_id = $this->request->getGetValues('target_id');
            }
        }
        else if($this->method == 'post'){
            if($this->request->getPostValues('target_num') != null){
                $this->target_num = intval($this->request->getPostValues('target_num'));
            }
            if($this->request->getPostValues('target_id') != null){
                $this->target_id = $this->request->getPostValues('target_id');
            }
        }

        //リクエストしたユーザをセット
        $this->me = new User($_SESSION['user_id']); 
        $this->mylist = new CheckList($this->me->getNum());

        //第一パラメータがuserなら対象ユーザをセット
        if($this->params[0] == 'user' || $this->params[0] == 'count'){
            if($this->target_num !== null){
                $this->targetUser = new User($this->target_num);
            }
            else if($this->target_id !== null){
                $this->targetUser = new User($this->target_id);
            }
            else{
                $this->error('no target is set');   //対象ユーザが設定されていなければエラーで終了
                exit;
            }
        }

        //それ以外なら対象コンテンツをセット
        else{
            if($this->target_num !== null){
                $this->targetContent = new Content($this->target_num); //現状整数でのインスタンス生成は不可
            }
            else if($this->target_id !== null){
                $this->targetContent = new Content($this->target_id);
            }
            else{
                $this->error('no target is set');   //対象コンテンツが設定されていなければエラーで終了
                exit;
            }
        }
    }

    
    public function get(){
        $flags = [];
        $genre = $this->request->getGetValues('genre');
        $category = $this->request->getGetValues('category');
        $tags = $this->request->getGetValues('tags');
        if($tags == null){
            $tags = '';
        }

        if($this->targetUser->is_exist()){
            $targetList = new CheckList($this->targetUser->getNum());
            $retList = new CheckList();
            //自分以外には非公開要素を返さない
            if($this->targetUser->getNum() != $this->me->getNum()){
                $retList->set($targetList->getPublicList());
            }
            //publicがtrueなら自分自身でも公開リストを返す
            else if($this->request->getGetValues('public') == 'true'){
                $retList->set($targetList->getPublicList());
            }
            else{
                $retList->set($targetList->getList());
            }

            if($this->params[0] == 'user'){
                $this->outputResult($retList->getByGenre($genre,$category,$tags));
            }
            else if($this->params[0] == 'count'){
                echo json_encode( count($retList->getByGenre($genre,$category,$tags)));
            }
            else{
                $this->error('end point does not exist');
            }
        }
        else{
            $this->error('target user does not exist');
        }
    }

    public function post(){
        if(!$this->me->is_exist()){
            $this->error('user does not exist');
            exit;
        }
        //対象コンテンツが存在すれば各種処理
        if($this->targetContent->is_exist()){
            $Data = [];
            $Data['user_num'] = $this->me->getNum();    //呼び出したユーザの番号を追加
            $Data['content_num'] = $this->targetContent->getNum();

            //リストに新規追加
            if($this->params[0] == 'create'){
                $Data = self::$ElementDataTemp;   //DBモデルに投げるデータ
                $Data['user_num'] = $this->me->getNum();    //呼び出したユーザの番号を追加
                $Data = array_overwrite($Data,$_POST);      //POSTデータを追加
                $Data = array_overwrite($Data,$this->targetContent->getData()); //対象コンテンツデータを追加
                if(isset($_POST['favorite'])){
                    $Data['favorite'] = ( $_POST['favorite'] === 'true' ? true : false);
                }
                if(isset($_POST['hidden'])){
                    $Data['hidden'] = ( $_POST['hidden'] === 'true' ? true : false);
                }
                if(isset($_POST['origin'])){
                    $Data['origin'] = ( $_POST['origin'] === 'true' ? true : false);
                }

                $url_valid = 'valid';
                if(isset($Data['user_ref']) && !is_uri($Data['user_ref'])){
                    $Data['user_ref'] = null;
                    $url_valid = 'invalid';
                }
                if(empty($this->mylist->getElement($this->targetContent->getNum() ))){
                    if( CheckListDatabase::createElement($Data)){
                        echo json_encode(['result'=>'succsess','message' => 'complete','action' => 'create','target' => str_escape($this->targetContent->getName()), 'user_ref' => $url_valid]);
                        exit;
                    }
                    else{
                        $this->error('database error');
                    }
                }
                //存在すればなにもせずメッセージを返す
                else{
                    echo json_encode(['result' => 'fail', 'message' => 'element already exists', 'action' => 'create', 'target' => str_escape($this->targetContent->getName()) ]);
                    exit;
                }
            }
            else if($this->params[0] == 'publish'){
                $Data['hidden'] = false;
                $this->updateElement($Data,'publish');
            }
            else if($this->params[0] == 'unpublish'){
                $Data['hidden'] = true;
                $this->updateElement($Data,'unpublish');
            }
            else if($this->params[0] == 'star'){
                $Data['favorite'] = true;
                $this->updateElement($Data,'star');
            }
            else if($this->params[0] == 'unstar'){
                $Data['favorite'] = false;
                $this->updateElement($Data,'unstar');
            }

            else if($this->params[0] == 'origin'){
                $Data['origin'] = true;
                $this->updateElement($Data,'origin');
            }

            else{
                $this->error('parameter is not found');
            }
        }
        else{
            $this->error('target does not exist');    //対象コンテンツが存在しなければエラーで終了
        }

    }

    private function updateElement($Data,$action_name = null){
        if(!empty($this->mylist->getElement($this->targetContent->getNum()))){
            if(CheckListDatabase::updateElement($Data)){
                echo json_encode(['result'=>'succsess','message' => 'complete','action' => $action_name,'target' => str_escape($this->targetContent->getName()) ]);
                exit;
            }
            else{
                $this->error('database error');
            }
        }
        else{
            $this->error('target does not exist in list');
        }
    }
    private function outputResult($rawResult){
        $ret = [];
        $i = 0;
        foreach($rawResult as $key => &$res){
            $res['content_link'] = ex_urlencode($res['content_name']);
            $res['content_link'] = preg_replace('/[\.]/','%2E',$res['content_link']);
            $res['content_link'] = preg_replace('/[\/]/','%2F',$res['content_link']);
            $res['content_link'] = preg_replace('/\x5c/','\\',$res['content_link']);
            $ret[$i] = self::$retColumns;
            $ret[$i] = array_overwrite($ret[$i],str_escape($res)); //エスケープした上で上書き
            array_shift($ret[$i]['tags']);
            array_shift($ret[$i]['tags']);
            $i++;
        }
        unset($res);
        unset($i);
        //$ret = $this->setReturnValues($rawResult)
        echo json_encode($ret);
    }
}

?>
