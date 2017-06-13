<?php
class AutoLoginDatabase extends Database{

    protected static $tableName = 'auto_login';
    protected static $columns = []; //カラム名 コンストラクタ(基底クラスに記述)で取得する
                                        //$columns['カラム名'] = ['カラムの型']

    //ユーザが登録されると呼び出される
    /*
    public static function register(){
        $Data = ['set' => ['login_key' => null]];
        return parent::insert($Data);
    }
    */
    
    //自動ログイン初回設定(ログイン後に実行)
    public static function save_key($key = null){
        if(isset($_SESSION['user_num']) && $_SESSION['user_num'] !== -1 && $key !== null){
            $Data = [
                'set' => ['login_key' => $key, 'user_num' => $_SESSION['user_num'], 'limitdate' => mydate_shift('+180 day')],
            ];
            return parent::insert($Data);
        }
        else{
            return false;
        }
    }
    
    //自動ログイン時にログインキーを更新
    public static function update_key($key = null){
        if(isset($_COOKIE['zmb']) && $key !== null){
            $Data = [
                'set' => ['login_key' => $key, 'limitdate' => mydate_shift('+180 day')],
                'where' => ['login_key' => ['=',$_COOKIE['zmb']] ]
            ];
            return parent::update($Data);
        }
        else{
            return false;
        }
    }
    
    //ログアウト時
    public static function remove_key(){
        if(isset($_COOKIE['zmb']) && isset($_SESSION['user_num']) && $_SESSION['user_num'] !== -1){
             $Data = [
                'set' => ['login_key' => 'lapsed', 'user_num' => -1, 'limitdate' => mydate_shift('-1 day')],
                'where' => ['login_key' => ['=',$_COOKIE['zmb']] ]
            ];
            return parent::update($Data);
        }
        else{
            return false;
        }
    }
    
    public static function verify(){
        if(isset($_COOKIE['zmb'])){
            $Data =[
                'where' => ['login_key' => ['=',$_COOKIE['zmb']]]  
            ];
            
            $res = parent::select($Data);
            if(!empty($res) && count($res) == 1){
                return $res[0]['user_num'];
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