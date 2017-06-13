<?php
class CheckList{
    private $user_num;      //ユーザ番号保持
    private $list = [];     //リスト保持

    private static $list_property = ['content_num' => null,'content_name' => null, 'user_comment'=> null, 'user_ref' => null,
                                    'user_image' => null, 'genre' => null, 'category' => null, 'tags' => null,
                                    'favorite' => null, 'hidden' => null, 'origin' => null, 'regdate' => null,
                                    'spell' => null,'user_count' => null,
                        ];

    function __construct($user_num = null){
        $this->setByUser($user_num);
    }

    //渡されたリストを$listに代入
    public function set($List = null){
        $this->list = [];
        if(is_array($List)){
            //一個一個コンテンツデータを持ってきて結合するクソ重処理なので今後DB側改修してどうにかする
            foreach($List as $e){
                if($this->validateData($e)){
                    $l = array_overwrite(self::$list_property,$e);
                    if(!is_array($l['tags'])){
                        $l['tags'] = explode(',',$l['tags']);
                    }
                    $content = new Content($e['content_name']);
                    $l['genre'] = $content->getGenre();
                    $l['spell'] = $content->getSpell();
                    $l['category'] = $content->getCategory();
                    $l['user_count'] = $content->getUserCount();
                    $this->list[] = $l;
                }
            }
        }
        else{
            if($this->validateData($List)){
                $this->list[] = $List;
            }
        }
    }

    //あるユーザのリストを$listに代入
    public function setByUser($user_num = null){
        if($user_num != null){
            $this->user_num = $user_num;
            $this->set(CheckListDatabase::selectByUser($user_num));   //リスト要素の配列を受け取る
            unset($e);
            return true;
        }
        else{
            $this->list = [];
            return false;
        }
    }

    //$user_numをセット
    public function setUserNum($user_num = null){
        if(is_int($user_num)){
            $this->user_num = $user_num;
            return true;
        }
        else{
            return false;
        }
    }

    //$this->user_numからリストを取得して$listに代入
    public function setMyList(){
        if(isset($this->user_num)){
            $this->list = CheckListDatabase::selectByUser($this->user_num);   //リスト要素の配列を受け取る
            foreach($this->list as &$e){
                $e['genre'] = ContentDataDatabase::selectByName($e['content_name'])['genre'];
            }
            unset($e);
            return true;
        }
        else{
            return false;
        }
    }

    //$listを返す
    public function getList(){
        return $this->list;
    }

    //特定ジャンル,タグ付きのもののみ返す
    public function getByGenre($genre = null,$category = null, $tags = []){
        $ret = [];
        if($tags == null){
            $tags = [];
        }
        if(!is_array($tags)){
            $tags = explode(',',$tags);
        }
        foreach($this->list as $e){
            //ジャンル一致判定
            if($e['genre'] == $genre || $genre == null){
                //カテゴリー一致判定
                if($e['category'] == $category || $category == null){
                    //指定タグを含むか判定
                    if(count(array_diff($tags,$e['tags'])) == 0 || empty($tags)){
                        $ret[] = $e;
                    }
                }
            }
        }
        return $ret;
    }

    //お気に入りだけ返す
    public function getFavorite(){
        $favs = [];
        foreach($this->list as $e){
            if($e['favorite']){
                $favs[] = $e;
            }
        }
        return $favs;
    }

    //リスト中から特定のコンテンツを指す要素を返す
    public function getElement($content_num = 0){
        if(!empty($this->list)){
            //コンテンツ番号から該当要素のリスト内位置を探す
            $key = array_search($content_num,array_column($this->list,'content_num'));
            if($key !== false){
                return $this->list[$key];
            }
            else{
                return false;
            }
        }
        else{
            //リストが空のとき
            return false;
        }
    }

    //ジャンル一覧を返す
    public function getGenres(){
        $genres = [];
        if(!empty($this->list)){
            foreach($this->list as $e){
                $genres[] = $e['genre'];
            }
            $genres = array_unique($genres);

            return $genres;
        }
        else{
            return false;
        }
    }

    //公開リストを返す
    public function getPublicList(){
        $ret = [];
        foreach($this->list as $e){
            if($e['hidden'] == false){
                $ret[] = $e;
            }
        }
        return $ret;
    }

    //リストに追加
    public function addElement($addData){
        if($this->validateData($addData)){
            //$is_new = ContentDataDatabase::selectByName($Data['content_name']);
            if(isset($this->user_num)){
                $addData['user_num'] = $this->user_num;
                if(CheckListDatabase::createElement($addData)){     //要素追加
                    echo '<br/>追加しました<br/>';
                }
                else{
                    echo '<br/>データべースエラー<br/>';
                }
                $this->list[] = $addData;       //$listにも追加

                return true;
            }
            else{
                echo '<br/>リストを所持しているユーザが不明です<br/>';
                return false;
            }
        }
        else{
            echo '<br/>入力データが不正です<br/>';
        }
    }

    public function updateElement($updateData){
        if($this->validateData($updateData)){
            if(isset($this->user_num) && isset($updateData['content_num'])){
                $updateData['user_num'] = $this->user_num;
                if(CheckListDatabase::updateElement($updateData)){
                    echo '<br/>更新しました<br/>';
                }
                else{
                    echo '<br/>データベースエラー<br/>';
                }
                $key = array_search($updateData['content_num'],array_column($this->list,'content_num'));   //更新したリストのキー
                $this->list[$key] = array_overwrite($this->list[$key],$updateData);
            }
            else{
                echo '<br/>更新する要素を特定できません<br/>';
            }
        }
        else{
            echo '<br/>入力データが不正です<br/>';
        }
    }

    //追加データのバリデーション
    private function validateData($Data){
        $valid = true;
        if(!isset($Data['content_name'])){
            $valid = false;
        }
        if(isset($Data['user_comment']) && strlen($Data['user_comment'] > 256)){
            //文字数オーバー
            $valid = false;
        }

        if(isset($Data['user_ref']) && (!is_uri($Data['user_ref']) && $Data['user_ref'] != null) ){
            //不正なuri
            $valid = false;
        }

        return $valid;
    }

    //お気に入り化
    public function favor($content_num){
        if(isset($content_num)){
            $old = $this->getElementInfo($content_num);
            if($old){
                $Data = [
                    'set' => [ 'favorite' => $old['favorite'], 'active' => $old['active'] ] 
                ];

                $Data = [
                    'set' => ['favorite' => true ],
                     'where' => ['content_num' => ['=',$content_num,'AND'], 'user_num' =>['=',$this->user_num] ]
                        ];
                return CheckListDatabase::updateElement();
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    //お気に入り解除
    public function unfavor($content_num){
        if(isset($content_num)){
            $old = $this->getElementInfo($content_num);
            if($old){
                $Data = [
                    'set' => [ 'favorite' => $old['favorite'], 'active' => $old['active'] ] 
                ];
                $Data = [
                    'set' => ['favorite' => false ],
                     'where' => ['content_num' => ['=',$content_num,'AND'], 'user_num' =>['=',$this->user_num] ]
                        ];
                return CheckListDatabase::updateElement();
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    //一覧から非表示にする(リストから消すことは不可)
    public function hide($content_num){
        if(isset($content_num)){
            $old = $this->getElementInfo($content_num);
            if($old){
                $Data = [
                    'set' => [ 'favorite' => $old['favorite'], 'active' => $old['active'] ]
                ];

                $Data = [
                        'set' => ['active' => false ],
                        'where' => ['content_num' => ['=',$content_num,'AND'], 'user_num' =>['=',$this->user_num] ]
                        ];
                return CheckListDatabase::updateElement();
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }
}
?>
