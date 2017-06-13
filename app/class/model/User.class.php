<?php
class User{
    private $exist = false;
    private $user_num = null;
    private $user_id = null;
    private $user_name = null;
    private $user_icon = null;
    private $user_comment = null;
    private $hidden = null;
    private $stream = null;
    private $show_nsfw = null;
    private $std_tags = null;
    private $posts = null;
    private $FFList = ['following' => [], 'followed' => [], 'brocking' => [], 'brocked' => [] ];

    private $user_data = ['user_num' => null, 'user_id'  => null, 'user_name' => null, 
                        'user_icon' => null, 'user_comment' => null, 
                        'hidden' => null, 'stream' => null, 'show_nsfw' => null,
                        ];

    private $mylist;

    function __construct($user = null){
        $this->setUser($user);
    }

    //ユーザ番号orユーザIDからインスタンスを生成
    public function setUser($user = null){
        //userが指定されていてかつ該当ユーザレコードが存在したらデータをセット
        if($user == null || (empty(UserDataDatabase::selectByNum($user)) && empty(UserDataDatabase::selectByID($user))) ){
            $this->exist = false;
            $this->user_num = -1;
            $this->user_id = 'guest';
            $this->user_data = ['user_id' => '@guest','user_name' => 'ゲストユーザー', 'user_icon' => 'noicon.png',
                               'user_comment' => 'ゲストユーザは投稿やチェックリストが使用できません' ];
        }

        else{
            if(is_int($user)){
                $this->setUserData(UserDataDatabase::selectByNum($user));

            }
            else if(is_string($user)){
                $this->setUserData(UserDataDatabase::selectByID($user));

            }

            //フォロー情報引き出し
            foreach(FollowListDatabase::selectAllFollowing($this->user_num) as $following){
                $this->FFList['following'][] = UserDataDataBase::selectByNum($following['user_follow_to']);
            }
            foreach(followListDatabase::selectAllFollowed($this->user_num) as $followed){
                $this->FFList['followed'][] = UserDataDataBase::selectByNum($followed['user_followed_by']);
            }
            $this->exist = true;
        }
    }

    private function setUserData($data){
        $this->user_data = array_overwrite($this->user_data,$data);
        $this->user_id = $data['user_id'];
        $this->user_num = $data['user_num'];
        $this->user_name = $data['user_name'];
        $this->user_icon = $data['user_icon'];
        $this->user_comment = $data['user_comment'];
        $this->hidden = $data['hidden'];
        $this->stream = $data['stream'];
        $this->std_tags =  $data['std_tags'];
        $this->show_nsfw = $data['show_nsfw'];
    }

    public function getUserData(){
        return $this->user_data;
    }

    public function getNum(){
        return $this->user_num;
    }

    public function getID(){
        return $this->user_id;
    }

    public function getName(){
        return $this->user_name;
    }

    public function getIcon(){
        return $this->user_icon;
    }

    public function getComment(){
        return $this->user_comment;
    }

    public function getFFList(){
        return $this->FFList;
    }
    
    public function getStdTags(){
        return explode(',',$this->std_tags);
    }

    public function getFollowingList(){
        return $this->FFList['following'];
    }
    public function getFollowedList(){
        return $this->FFList['followed'];
    }

    public function is_exist(){
        return $this->exist;
    }

    public function is_hidden(){
        return $this->hidden;
    }

    public function is_stream(){
        return $this->stream;
    }

    public function is_nsfw(){
        return $this->show_nsfw;
    }

    public function followingUsersNum(){
        $num = [];
        //自分を追加
        $num[] = $this->user_num;
        //フォローしているユーザーを追加
        foreach($this->FFList['following'] as $user){
            $num[] = $user['user_num'];
        }
        return $num;
    }

    public function followedUsersNum(){
        $num = [];
        //自分を追加
        $num[] = $this->user_num;
        //フォローしているユーザーを追加
        foreach($this->FFList['followed'] as $user){
            $num[] = $user['user_num'];
        }
        return $num;
    }

    public function blockingUsersNum(){
        /* TO DO */
    }

    public function blockedUsersNum(){
        /* TO DO */
    }

    //$targetとの関係を返す(フォローしている/されている)
    public function lookup($target_num){
        if(is_int($target_num)){
            $following = $this->followingUsersNum();
            $followed = $this->followedUsersNum();

            $ret = [];
            $ret['following'] = !empty(array_search($target_num,$following,true)) ? true : false;
            $ret['followed'] = !empty(array_search($target_num,$followed,true)) ? true : false;
            return $ret;
        }
        else{
            return false;
        }
    }

    public function countFollowing(){
        $ret = count($this->FFList['following']);
        return $ret ? $ret : 0;
    }
    public function countFollower(){
        $ret = count($this->FFList['followed']);
        return $ret ? $ret : 0;
    }

    //登録処理
    public static function register($request = null){
        if($request == null){
            $req = new Request();
            $request = $req->getPostValues();
        }

        //リクエストがあれば処理
        if(!empty($request['user_id']) && !empty($request['pword'])){
            //バリデーションを通ったらDB登録
            if(self::validateUserID($request['user_id']) && self::validateUserPass($request['pword'])){
                $regData = [ 'user_id' => $request['user_id'], 'user_pass' => $request['pword'] ];
                UserDataDatabase::createUser($regData);
                echo '登録完了';
                $regUser = new User($request['user_id']);
                return $regUser->getNum();
            }

            else{
                //echo '入力内容を確認してください';
                return false;
            }
        }

        //リクエストがなければfalseを返す
        else{
            return false;
        }
    }

    //認証処理
    public static function authenticate($request = null){
        //引数orリクエストからログイン情報を取得
        if($request == null){
            $req = new Request();
            $request = $req->getPostValues();
        }

        //$requestがあれば処理
        if(!empty($request['user_id']) && !empty($request['pword'])){
            $buf = UserDataDatabase::selectPass($request['user_id']);   //DBからパスワード(ハッシュ)取得
            $pass = $request['pword'];
            $auth = false;

            //DBからレコードが返ってきたらパスワード確認
            if($buf != false){
                $p_hash = $buf['user_pass'];
                //認証
                if(password_verify($pass,$p_hash)){
                    $auth = true;
                }
            }
            else{
                $p_hash = '';
                password_verify($pass,$p_hash);
            }
            if($auth){
                //認証に成功したらユーザ番号を返す
                return $buf['user_num'];
            }
            else{
                echo '認証に失敗しました 入力内容を確認してください';
                return false;
            }
        }
        //リクエストがなければfalseを返す
        else{
            return false;
        }
    }

    public function login($user_num){
        session_regenerate_id(true);
        $user = new User($user_num);
        $_SESSION['user_num'] = $user->getNum();
        $_SESSION['user_id'] = $user->getID();
        $_SESSION['online'] = true;
        $_SESSION['token'] = genToken(session_id());
        //echo 'ログイン成功';
    }

    public function logout($request){

    }

    //ユーザ情報変更
    public function update($updateData){
        if($this->is_exist()){
            $Data = $this->getUserData();
            $Data = array_overwrite($Data,$updateData);
            return UserDataDatabase::updateUser($Data);
        }
        else{
            echo 'ユーザが存在しません';
            return false;
        }
    }

    //パスワード変更
    public function changeUserPass($current,$p1,$p2){ 
        if($current != null && $p1 != null && $p2 != null){
            if($p1 !== $p2){
                echo '新しいパスワードと確認用の入力が一致しません';
                return false;
            }
            else{
                $req = [];
                $req['pword'] = $current;
                $req['user_id'] = $this->getID();
                if(self::authenticate($req) && self::validateUserPass($p1)){
                    return UserDataDatabase::updatePass($this->getNum(),$p1);
                }
            }
        }
        else{
            echo '入力を確認してください';
        }
    }
    //自身のリストと$user_numで渡されたユーザのリストの一致要素数を返す
    /*
        public function taste($user_num = null,$genre = null,$category = null, $tags = []){
        $this->mylist = new CheckList($this->user_num);
        $p_mylist = $this->mylist->getByGenre($genre,$category,$tags); //マイリストの部分リスト
        $targetuser = new User($user_num);
        //戻り値テンプレート
        $ret_temp = ['target_id' => $targetuser->getID(),'genre'=> $genre, 'tags' => $tags,
                    'targetlist_count' => 0,  'targetpart' => [], 'targetpart_count' => 0, 
                    'mylist_count' => count($this->mylist->getList()), 'mypart' => $p_mylist, 'mypart_count' => count($p_mylist),
                    'matchlist' => [], 'match_count' => null
                ];

        if($this->is_exist()){
            if(!is_array($user_num)){
                if(is_int($user_num)){
                    $ret = $ret_temp;
                    $targetlist = new CheckList($user_num);
                    $targetlist->set($targetlist->getPublicList());
                    $ret['targetlist_count'] = count($targetlist->getList());
                    $p_targetlist = $targetlist->getByGenre($genre,$category,$tags);
                    $ret['targetpart'] = $p_targetlist;
                    $ret['targetpart_count'] = count($p_targetlist);
                    //一致する要素を抽出(標準関数にありそう)
                    foreach($p_targetlist as $e){
                        $key = false;
                        $key = array_search($e['content_num'],array_column($p_mylist,'content_num'));
                        if($key !== false){
                            $ret['matchlist'][] = $p_mylist[$key];
                        }
                    }
                    $ret['match_count'] = count($ret['matchlist']);
                    return $ret;
                }
                else{
                    return false;
                }
            }
            else{
            }
        }
    }
     */

    //登録可能なIDか検証
    public static function validateUserID($id = null){
        $req = new Request();
        if($id == null && $req->getPostValues('user_id')){
            $id = $req->getPostValues('user_id');
        }
        else if($id == null){
            echo 'IDが未入力です<br/>';
            return false;
        }

        //使用文字検証
        if(!preg_match('/^[a-zA-Z0-9_]{1,32}$/',$id)){
            echo 'IDは半角英数および_(アンダースコア)の組み合わせで32文字以内で入力してください<br/>';
            return false;
        }
        //未使用か検証
        else if(!empty(UserDataDatabase::selectByID($id))) {
            echo 'そのIDはすでに使用されています<br/>';
            return false;
        }
        //問題なければtrue
        else{
            return true;
        };

    }

    //登録可能なパスワードか検証
    public static function validateUserPass($pass = null){
        $req = new Request();
        if($pass == null && $req->getPostValues('pword') ){
            $id = $req->getPostValues('pword');
        }

        if($pass == null){
            echo 'パスワードが未入力です<br/>';
        }
        else{
            //使用文字検証
            if(!preg_match('#^[a-zA-Z0-9!$%&/+*,;(){}^~]{8,}$#',$pass)){
                echo 'パスワードは指定文字の組み合わせで8文字以上で入力してください<br/>';
                return false;
            }

            else{
                return true;
            }
        }
    }
}
?>
