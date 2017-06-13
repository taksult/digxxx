<?php
class PostDataDatabase extends Database{

    protected static $tableName = 'post_data';
    protected static $columns = []; //カラム名 コンストラクタ(基底クラスに記述)で取得する
                                    //$columns['カラム名'] = ['カラムの型']

    protected static $selectCols = "user_num,user_id,content_name,reference_url,post_comment,genre,post_image_name,scope,dig,nsfw,hidden_user,regdate";
    private static $userUpdatable = ['scope'];

    //投稿データ登録
    public static function createPost($postData){
        $Data = [
            'set' => [ 'user_num' => null, 'user_id' => null,'content_name' => null, 'reference_url' => null,
                        'post_comment' => null, 'genre' => '', 'tags' => '', 'post_image_name' => null,
                        'scope' => 0,'dig'=> false, 'nsfw'=> false, 'regdate' => mydate(), 'moddate' => mydate()
                ]
        ];
        $Data['set'] = array_overwrite($Data['set'],$postData);
        $Data['set']['post_image_name'] = $Data['set']['post_image_name'] . ',';
        $Data['set']['tags'] = ',,' .$Data['set']['tags'] . ',' . $Data['set']['content_name'] . ',';    //array_overwriteがrecursiveでないのでこんなことに
        //鍵ユーザの場合はhiddenをtrueにする
        $postUser = UserDataDatabase::selectByNum($postData['user_num']);
        if($postUser['hidden'] == true){
            $Data['set']['hidden_user'] = true;
        }
        //stream がfalseならscopeをtrueにする
        if($postUser['stream'] == false){
            $Data['set']['scope'] = true;
        }
        //重複投稿チェック(6時間以内に同じ投稿があればfalseを返す)
        $checkData = [
                    'where' => ['user_num' => ['=', $postData['user_num'], 'AND'], 'content_name' => ['=', $postData['content_name'], 'AND'],
                                'enabled' => ['=', true, 'AND'],
                                'post_comment' => ['=', $postData['post_comment'], 'AND'], 'regdate' => ['>=',@date('Y-m-d H:i:s',strtotime('-6 hour',time()))]
                                ]
        ];
        if(!self::checkOverlap($checkData)){
            return parent::insert($Data);  //重複がなければDB登録
        }
        else{
            echo "<script> alert('すでに同じ内容の投稿があります'); </script><br/>";
            return false;
        }
    }

    //投稿番号で取得
    public static function selectByNum($post_num){
        $Data = ['where' =>  [ 'enabled' => ['=', true, 'AND'], 'post_num' => ['=', $post_num] ]];

        $result = parent::select($Data);

        if(!empty($result)){
            return $result[0];
        }
        return $result;
    }

    //全体投稿から新しい順に取得
    public static function selectAllPosts($tags = '', $offset = PHP_INT_MAX,$nsfw = false){
        if($offset == PHP_INT_MAX){
            $offset = parent::getLatestPrimary();
        }
        $Data = [
            'where' => ['enabled' => ['=', true, 'AND'], 'post_num' =>['<=',$offset,'AND'], 'tags' => null, 'scope' => ['=',false,'AND']],
            'limit' => 15,
            'order' => 'post_num',
            'option' => 'DESC'
        ];

        //nsfw
        if($nsfw === false){
            $Data['where']['nsfw'] = ['=',false,'AND'];
        }
 
        
        $ex_tags = [];
        foreach(explode(',',$tags) as $t){
            $ex_tags[] = ',' . $t . ',';
            //$ex_tags[] = '%' . str_replace('%','\%',$t) . '%';
        }
        //タグ
        $Data['where']['tags'] = ['LIKE',$ex_tags,'AND'];
        return parent::select($Data);
    }


    //ユーザの投稿を新しい順に取得
    public static function selectByUser($user_num = -1, $tags = '', $offset = PHP_INT_MAX,$nsfw = false){
        if($offset == PHP_INT_MAX){
            $offset = parent::getLatestPrimary();
        }
        if(is_array($user_num)){
            $Data = ['where' => [ 'user_num' => ['IN', [], 'AND' ] ] ];
            foreach($user_num as $num){
                $Data['where']['user_num'][1][] = $num;
            }
        }
        else{
            $Data = ['where' => ['user_num' => ['=',$user_num, 'AND'] ] ];
        }

         if($nsfw === false){
            $Data['where']['nsfw'] = ['=',false,'AND'];
        }
 
        
        $Data['where']['post_num'] = ['<=',$offset,'AND'];
        $ex_tags = [];
        foreach(explode(',',$tags) as $t){
            $ex_tags[] = ',' . $t . ',';;
        }
        
        $Data['where']['enabled'] = ['=', true, 'AND'];
        $Data['where']['tags'] = ['LIKE',$ex_tags,'AND'];
        $Data['limit'] = 15;
        $Data['order'] = "post_num";
        $Data['option'] = 'DESC';

        return parent::select($Data);
    }

    //$current_latestより新しい投稿を取得
    public static function selectUnreadAllPosts($tags = '', $current_latest = PHP_INT_MAX,$nsfw = false){
        $Data = [
            'where' => [ 'enabled' => ['=', true, 'AND'], 'post_num' => ['>', $current_latest,'AND'], 'scope' => ['=',false,'AND'], 'tags' => []] ,
            'order' => 'post_num',
            'option' => 'DESC'
        ];
        
        if($nsfw === false){
            $Data['where']['nsfw'] = ['=',false,'AND'];
        }
 
        
        $ex_tags = [];
        foreach(explode(',',$tags) as $t){
            $ex_tags[] = ',' . $t . ',';;
        }
        
        $Data['where']['tags'] = ['LIKE',$ex_tags,'AND'];
        return parent::select($Data);
    }

    public static function selectUnreadPostsByUser($user_num,$tags = '',$current_latest = PHP_INT_MAX,$nsfw = false){
        if(is_array($user_num)){
            $Data = ['where' => [ 'user_num' => ['IN', [], 'AND' ] ] ];
            foreach($user_num as $num){
                $Data['where']['user_num'][1][] = $num;
            }
        }
        else{
            $Data = ['where' => ['user_num' => ['=',$user_num, 'AND'] ] ];
        }
        $Data['where']['post_num'] = ['>',$current_latest,'AND'];
        
        if($nsfw === false){
            $Data['where']['nsfw'] = ['=',false,'AND'];
        }
 
        
        $ex_tags = [];
        foreach(explode(',',$tags) as $t){
            $ex_tags[] = ',' . $t . ',';;
        }
        
        $Data['where']['enabled'] = ['=', true, 'AND'];
        $Data['where']['tags'] = ['LIKE',$ex_tags,'AND'];
        $Data['order'] = "post_num";
        $Data['option'] = 'DESC';

        return parent::select($Data);
    }

    //コンテンツ名で検索
    public static function searchByContent($content_name,$nsfw = false){
        $Data = [
            'where' => [ 'enabled' => ['=', true, 'AND'], 'content_name' => ['=', $content_name,'AND']]
        ];
        
        if($nsfw === false){
            $Data['where']['nsfw'] = ['=',false,'AND'];
        }
 

        return parent::select($Data);
    }

    //投稿文中のタグで検索
    public static function searchByTag($tag,$nsfw = false){
        $Data = [
            'where' => ['enabled' => ['=', true, 'AND'], 'post_comment' => ['LIKE', '%#'.$tag.'%','AND'] ] 
        ];
        
        if($nsfw === false){
            $Data['where']['nsfw'] = ['=',false,'AND'];
        }
 
        
        return parent::select($Data);
    }

    //投稿削除
    public static function deletePost($post_num){
        $Data = ['set' => ['enabled' => false ], 
                 'where' => ['post_num' => ['=', $post_num]] 
                ];
        return  parent::update($Data);
    }

    public static function hideUserPost($user_num = null,$scope = true){
        if(is_int($user_num)){
            $Data = [
                        'set' => ['scope' => $scope],
                        'where' => ['user_num' => ['=',$user_num]]
                    ];
            return parent::update($Data);
        }
    }

    //重複投稿チェック(6時間以内に同じ投稿があればfalseを返す)
    public static function checkOverlap($checkData){
        $res = parent::select($checkData);
        if(!empty($res)){
            $latest = array_pop($res);
            if($latest['enabled'] == true){
                return true;
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
