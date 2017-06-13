<?php
class FollowListDatabase extends Database{

    protected static $tableName = 'follow_list';
    protected static $columns = []; //カラム名 コンストラクタ(基底クラスに記述)で取得する
                                        //$columns['カラム名'] = ['カラムの型']
    protected static $selectCols = null;
    private static $userUpdatable = null;


    //自分がフォローしてるユーザーを検索
    public static function selectFollowing($mynum,$follow_to){
        if(is_array($follow_to)){
            $Data = ['where' => [ 'user_follow_to' => ['IN', [] ,'AND'] ] ];
            foreach($follow_to as $num){
                $Data['where']['user_num'][1][] = $num;
            }
        }
        else{
            $Data = ['where' => ['user_follow_to' => ['=',$follow_to,'AND'] ]];
        }

        $Data['where']['user_followed_by'] = ['=',$mynum,'AND'];
        $Data['where']['active'] = ['=',true];

        return parent::select($Data,self::$selectCols);
    }


    //自分をフォローしてるユーザーを検索
    public static function selectFollowed($mynum,$followed_by){
        if(is_array($followed_by)){
            $Data = ['where' => [ 'user_followed_by' => ['IN', [] ,'AND'] ] ];
            foreach($followed_by as $num){
                $Data['where']['user_num'][1][] = $num;
            }
        }
        else{
            $Data = ['where' => ['user_followed_by' => ['=',$followed_by,'AND'] ]];
        }

        $Data['where']['user_follow_to'] = ['=',$mynum,'AND'];
        $Data['where']['active'] = ['=',true];
        return parent::select($Data,self::$selectCols);
    }

    //自分がフォローしてるユーザーすべて
    public static function selectAllFollowing($mynum){
        $Data = ['where' => ['user_followed_by' => ['=',$mynum,'AND'] ] ];
        $Data['where']['active'] = ['=',true];
        return parent::select($Data,self::$selectCols);
    }

    //自分をフォローしてるユーザーすべて
    public static function selectAllFollowed($mynum){
        $Data = ['where' => ['user_follow_to' => ['=',$mynum,'AND'] ] ];
        $Data['where']['active'] = ['=',true];
        return parent::select($Data,self::$selectCols);
    }

    //アクティブ/非アクティブにかかわらずレコードが存在するか調べる
    public static function selectOnceFollowing($mynum,$follow_to){
        if(is_array($follow_to)){
            $Data = ['where' => [ 'user_follow_to' => ['IN', [] ,'AND'] ] ];
            foreach($follow_to as $num){
                $Data['where']['user_num'][1][] = $num;
            }
        }
        else{
            $Data = ['where' => ['user_follow_to' => ['=',$follow_to,'AND'] ]];
        }

        $Data['where']['user_followed_by'] = ['=',$mynum,];

        return parent::select($Data,self::$selectCols);
    }


    //新規リスト登録(or activeフラグの切り替え)
    public static function follow($followed_by,$follow_to){
        //鍵アカの場合フォロー不可
        $is_hidden = UserDataDatabase::selectByNum($follow_to);
        //すでにレコードが存在する場合activeフラグをtrueに
        if(!empty(self::selectOnceFollowing($followed_by, $follow_to))){
            $Data = [
                'set' => ['active' => true, 'moddate' => mydate()],
                'where' => ['user_followed_by' => ['=',$followed_by,'AND'],
                            'user_follow_to' => ['=',$follow_to] ]
                        ];
            return parent::update($Data);
        }

        //新規リスト登録
        else{
            $Data = [
                'set' => ['user_followed_by' => $followed_by, 'user_follow_to' => $follow_to,
                'regdate' => mydate(), 'moddate' => mydate() ]
                ];
            return parent::insert($Data);
        }
    }

    //リムーブ処理(activeフラグを切り替え)
    public static function remove($followed_by,$follow_to){
        $Data = [
            'set' => ['active' => false , 'moddate' => mydate()],
            'where' => ['user_followed_by' => ['=',$followed_by,'AND'],
                            'user_follow_to' => ['=',$follow_to] ]
                ];
         return parent::update($Data);
    }


//-------------------------TO DO ---------------------
    public static function block($block,$blocked){
        //フォローしてるユーザーのブロック
        if(self::selectFollowing($follow_to))
        $blockSide = [
            'set' => ['block'  => true, 'active' => false],
            'where' => ['user_followed_by' => ['=',$blocked,'AND'],
                            'user_follow_to' => ['=',$block] ]
                        ];

        //フォローしていないユーザーのブロック


        $blockedSide = [
            'set' => ['blocked'  => true, 'active' => false],
            'where' => ['user_followed_by' => ['=',$follow_to,'AND'],
                            'user_follow_to' => ['=',$followed_by] ]
                        ];
        parent::update($Data);
    }

}
?>
