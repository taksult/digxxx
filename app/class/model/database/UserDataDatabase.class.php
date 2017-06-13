<?php
class UserDataDatabase extends Database
{
    protected static $tableName = 'user_data';
    protected static $columns = []; //カラム名 コンストラクタ(基底クラスに記述)で取得する
                                        //$columns['カラム名'] = ['カラムの型']
    protected static $selectCols = "user_num,user_id,user_name,user_icon,user_comment,favgenres,regdate,moddate,hidden,stream,show_nsfw,std_tags,enabled";
    private static $userUpdatable = ['user_name','user_icon','user_comment','favgenres','hidden','stream','show_nsfw'];

    //新規ユーザ登録
    public static function createUser($userData){
        $Data = [
            'set' => ['user_id' => $userData['user_id'], 'user_hash' => md5($userData['user_id']), 'user_name' => $userData['user_id'],
                            'mail_address' => $userData['user_id']. '@diglue.com','user_icon' => 'noicon.png', 'regdate' => mydate(), 'moddate' => mydate(),
                            'user_pass' => password_hash($userData['user_pass'],PASSWORD_BCRYPT) ]
                ];
         return parent::insert($Data);
    }

    //ユーザデータ更新
    public static function updateUser($updateData){
        $oldUserData = self::selectByNum($updateData['user_num']);
        if(!empty($oldUserData)){
            //現在のデータで初期化
            $Data = [
                        'set'   => $oldUserData,
                        'where' => ['user_num' => ['=',$updateData['user_num'] ] ]
                    ];
            //受け取ったデータのうち$userUpdatableなものだけ更新
            foreach($updateData as $key => $val){
                if(in_array($key,self::$userUpdatable,true)){
                    $Data['set'][$key] = $val;
                }
            }
            //hidden,streamに変更があったらユーザの全投稿のscopeを変更
            if($oldUserData['hidden'] != $Data['set']['hidden']){
                PostDataDatabase::hideUserPost($updateData['user_num'],$Data['set']['hidden']);
            }
            if($oldUserData['stream'] != $Data['set']['stream']){
                 PostDataDatabase::hideUserPost($updateData['user_num'],!$Data['set']['stream']);
            }
            $Data['set']['moddate'] = mydate();
            return parent::update($Data);
        }
    }

    //enabledフラグを落とす
    public static function deleteUser($user_num){
        $Data = [
            'set' => ['enabled' => false, 'moddate' => mydate()],
            'where' =>[ 'user_num' => ['=', $user_num] ]
        ];

        parent::update($Data);
    }

    //ユーザ番号からselect
    public static function selectByNum($user_num){
        if(is_array($user_num)){
            $Data = ['where' => [ 'user_num' => ['IN', [] ] ] ];
            foreach($user_num as $num){
                $Data['where']['user_num'][1][] = $num;
            }
        }
        else{
            $Data = ['where' => ['user_num' => ['=',$user_num] ] ];
        }
        $result = parent::select($Data,self::$selectCols);
        if(!empty($result) && count($result) == 1){
            return $result[0];
        }
        else{
            return $result;
        }
    }

    //ユーザidからselect
    public static function selectByID($user_id){
        if(is_array($user_id)){
             $Data = ['where' => [ 'user_id' => ['IN', [], 'OR'], 'mail_address' => ['IN', []] ] ];
            foreach($user_id as $id){
                $Data['where']['user_id'][1][] = $id;
                $Data['where']['mail_address'][1][] = $id;
            }
        }
        else{
            $Data = ['where' => ['user_id' => ['=',$user_id, 'OR'], 'mail_address' => ['=',$user_id] ]];
        }
        
        $result = parent::select($Data,self::$selectCols);
        if(!empty($result) && count($result) == 1){
            return $result[0];
        }
        else{
            return $result;
        }
    }

    public static function selectPass($user_id){

        $Data = [ 'where' =>  ['user_id' => ['=', $user_id, 'OR'], 'mail_address' => ['=', $user_id] ] ];

        $result = parent::select($Data,'user_num,user_pass');
        if(!empty($result)){
            return $result[0];
        }
        return $result;
        
    }

    public static function updatePass($user_num, $new_pass){
        if($user_num != null && $new_pass != null){
            $Data = [
                    'set' => ['user_pass' => password_hash($new_pass,PASSWORD_BCRYPT),'moddate' => mydate()],
                    'where' => ['user_num' => ['=' , $user_num] ]
            ];

            return parent::update($Data);
        }
        else{
            return false;
        }
    }

    public static function searchByKey($key = ''){
        $keys = str_replace("　"," ",$key);
        preg_match('/^\s*$/',$keys,$none);
        if(empty($none)){
            $values = [];

            $sql = "(SELECT " .self::$selectCols ." FROM user_data WHERE user_id = ?) 
                    UNION(SELECT " .self::$selectCols ." FROM user_data WHERE user_id collate utf8_unicode_ci LIKE ?) 
                    UNION(SELECT " .self::$selectCols ." FROM user_data WHERE user_id collate utf8_unicode_ci LIKE ?) 
                    UNION(SELECT " .self::$selectCols ." FROM user_data WHERE user_name LIKE ?) 
                    UNION(SELECT " .self::$selectCols ." FROM user_data WHERE user_comment LIKE ?)";

            $val = str_replace('\\',"\\\\",$key);
            $val = str_replace('%',"\%",$val);
            $val = str_replace('_',"\_",$val);

            for($i = 0; $i < 5; $i++){
                if($i == 0){
                    $values[] = $val;
                }
                else if($i == 2){
                    $values[] = $val . '%';
                }
                else{
                    $values[] = '%' . $val . '%';
                }
            }
            return parent::get($sql,$values);
        }
        else{
            return [];
        }
    }
}
?>
