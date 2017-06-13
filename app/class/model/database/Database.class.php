<?php
include_once(PATH_LIBRARY);
//DBアクセス 基底クラス
abstract class Database
{
    protected static $database = null;    //DBハンドラ(子クラスで使いまわし)
    protected static $tableName = NULL;   //子クラスがアクセスするテーブル名
    protected static $columns;            //子クラスがアクセスするテーブルのカラム名 
    //$columns['カラム名'] = ['カラムの型']
    protected static $selectCols;         //SELECTで持ってくるテーブル毎の基本カラムリスト
    
    //static function init() みたいなのに変えるかも
    function __construct(){
        if(static::$tableName != NULL && empty(static::$columns)){
            //インスタンス化されたときにカラム名と型を$columnに格納
            $getColumns = self::getColumns(static::$tableName);
            
            foreach($getColumns as $col => $arr){
                static::$columns[$arr['Field']] = $arr['Type'];
            }
        }
    }

    public function init(){
        if(static::$tableName != NULL && empty(static::$columns)){
            //カラム名と型を$columnに格納
            $getColumns = self::getColumns(static::$tableName);
            
            foreach($getColumns as $col => $arr){
                static::$columns[$arr['Field']] = $arr['Type'];
            }
        }

    }

    //DB接続開始
    public static function initDB(){
        try{
            self::$database = new PDO(DB_NAME,DB_USER,DB_PASS, array(PDO::ATTR_PERSISTENT => true));
            self::$database->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            self::$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            self::$database->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

            return true;
        }catch(PDOException $e){
            die('接続エラー'); //:' . $e->getMessage());
        }
    }

    //プリペアドステートメント($sql)にパラメータ($params)をバインドして実行
    //fetchAll()の結果を返す
    protected function get($sql, $params = null){
        try{
            $stmt = self::$database->prepare($sql);

            //パラメータが渡されなければそのまま実行
            if(!$params){
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt = null;   //PDOステートメントのクローズ
                return $result;
            }
            //パラメータが渡されたらバインドして実行
            else{
                //引数が1変数のとき配列に変換
                if(!is_array($params)){
                    $params = array($params);   //こういう再定義っていいのか...
                }

                foreach($params as $i => $val){ //bindParamを使う場合は$valを参照渡しにする
                    //整数値はPRAM_INT指定で渡す
                    if(is_int($val)){
                        $stmt->bindValue($i+1,$val,PDO::PARAM_INT); 
                    }
                    else{
                        $stmt->bindValue($i+1,$val);
                    }
                }
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt = null;   //PDOステートメントのクローズ
                
                return $result;
            }
        //例外処理
        }catch(PDOException $e){
            //echo 'ステートメントエラー'; //. $e->getMessage();
            return false;
        }
    }

    //プリペアドステートメント($sql)にパラメータ($params)をバインドして実行
    //INSERT,UPDATE,DELETE等
    protected function set($sql,$params = null){
        try{
            $stmt = self::$database->prepare($sql);
            //パラメータが渡されなければそのまま実行
            //(set系でそんな操作しなさそうだから消すかも)
            if(!$params){
                $stmt->execute();
                $stmt=null;
                return true;
            }
            else{
                //引数が1変数のとき配列に変換
                if(!is_array($params)){
                    $params = array($params);   //こういう再定義っていいのか...
                }

                foreach($params as $i => $val){ //bindParamを使う場合は$valを参照渡しにする
                    //整数値はPRAM_INT指定で渡す
                    if(is_int($val)){
                        $stmt->bindValue($i+1,$val,PDO::PARAM_INT);
                    }
                    else{
                        $stmt->bindValue($i+1,$val);
                    }
                }
                $stmt->execute();
                $stmt = null;   //PDOステートメントのクローズ
                return true;
            }
        }
        catch(PDOException $e){
            echo 'ステートメントエラー'; // . $e->getMessage();
            return false;
        }
    }



//基本的なCRUDメソッド     SQL作成→get() or set()に渡す
//渡されるデータ $Data = array( 'set'   => array('col1' => val1, 'col2' => val2)
//                              'where' => array('col3' => array(comp1 , 'val3' ,'AND', ) , array(comp2 , 'val4') )
//logics省略の場合すべてANDとみなす
//
    //INSERT INTO $tableName SET $col1 = $val1, $col2 = $val2   まとめてINSERTは保留 
    public static function insert($Data){
        //データが空ならエラーを返す
        if(empty($Data)){
            self::classError(1,'データがありません');
            return false;
        }
        //値をバリデートして抽出
        $values = self::validateData($Data, VALIDATION_MODE_SET);
        if(!$values){
            self::classError(1,'バリデーション失敗');
            return false;
        }
        //プレースホルダ付きクエリ作成
        $sql = "INSERT INTO " . static::$tableName . " SET " . self::genSetQuery($Data['set']);  //呼び出し元の$tableName
        return self::set($sql,$values);
    }


    //SELECT * FROM $tableName WHERE $key1 IN ($val[0][0],$val[0][1]) AND(OR) $key2 IN($val[1][0] ...
    //複数のkeyに対して複数のvalue $Data(['key1'] =>( val1, val2, val3), ['key2'] =>(val1,val2...)
    public static function select($Data,$columns = null){
        if(empty($Data)){
            self::classError(1,'データがありません');
            return false;
        }
        $values = self::validateData($Data, VALIDATION_MODE_WHERE);
        if(!$values){
            self::classError(1,'バリデーション失敗');
            return false;
        }
        $sql = "SELECT * FROM ";
        
        //カラム指定
        if($columns){
            $sql = "SELECT ";
            //カラム指定複数
            if(is_array($columns)){
                foreach($columns as $col){
                    $sql = $sql . $col.  ',';
                }
                $sql = rtrim($sql,',');
            }
            //カラム指定単一
            else{
                $sql = $sql . $columns;
            }
            $sql =  $sql . " FROM ";
        }
        $sql = $sql . static::$tableName . " WHERE " . self::genWhereQuery($Data['where']);

        //並べ替え設定
        if(isset($Data['order'])){
            if(self::column_exists($Data['order'])){
                $sql = $sql . " ORDER BY  " . $Data['order'] . " ";

                //optionで昇順、降順等設定
                if(!empty($Data['option']) && ($Data['option'] == 'DESC' || $Data['option'] == 'ASC')){
                    $sql = $sql . $Data['option'] . " ";
                }
            }
        }
        //件数上限
        if(isset($Data['limit'])){
            if(is_string($Data['limit'])){
                $Data['limit'] = intval($Data['limit']);
            }
            $sql = $sql . " LIMIT ? ";
            $values[] = $Data['limit'];
        }
//debug
//echo '<br/>' . $sql . '<br/>';
//print_r($values);
        return  self::get($sql,$values);
    }


    //UPDATE table SET $col1 = $val1, $col2 = $val2 WHERE $col1 = $val1  AND  ... , OR ... , OR $colN IN (),...
    public static function update($Data){
        if(empty($Data)){
            self::classError(1,'データがありません');
            return false;
        }
        $values = self::validateData($Data, VALIDATION_MODE_BOTH);
        if(!$values){
            self::classError(1,'バリデーション失敗');
            return false;
        }
        $sql = "UPDATE " . static::$tableName . " SET " . self::genSetQuery($Data['set']) . " WHERE " . self::genWhereQuery($Data['where']);

        /*   デバッグ用  SQLと$valuesの確認
        echo $sql . '<br/>';
        foreach($values as $val){
            echo $val;
        }
         */
        return self::set($sql,$values);
    }


    //DELETE FROM table WHERE 略
    public static function delete($Data){
        if(empty($Data)){
            self::classError(1,'データがありません');
            return false;
        }
        $values = self::validateData($Data,VALIDATION_MODE_WHERE);
        if(!$values){
            self::classError(1,'バリデーション失敗');
            return false;
        }

        $sql = "DELETE FROM  " . static::$tableName . " WHERE " . self::genWhereQuery($Data['where']);

        return self::set($sql,$values);

    }

    public static function commit(){
        return self::$database->commit();
    }
    public static function rollback(){
        return self::$database->rollback();
    }

    //明示的にクローズすることはほぼなさそう(同スクリプト中で二度と使えなくなるので)
    public function close(){
        return self::$database=null;
    }

    public function getError(){
        return self::$database->errorInfo();
    }

    public function getInsertId(){
        return self::$database->lastInsertId();
    }



    //SETクエリ生成
    protected static function genSetQuery($setData){
        $placeholder = '';

        if(empty($setData)){
            self::classError(2,'データがありません');
        }
        //カラム名とプレースホルダを追加
        foreach($setData as $key => $val){
            $placeholder = $placeholder . ' ' . $key . " = ? ,";
        }
        //末尾のカンマを消去して返す
        return rtrim($placeholder, ',');
    }

    //WHEREクエリ生成
    protected static function genWhereQuery($whereData,$end = true){
        $placeholder = '';
        if(empty($whereData)){
            self::classError(2,'条件が設定されていません');
            return false;
        }

        //$val[0] => 比較演算子 [1] => 値 [2] => 論理演算子
        foreach($whereData as $key => $val){
            //一つのキーに対して複数条件が付くとき 'post_num' => [ ['<=',$offset,'AND'] , ['>=', $oldest, 'AND'] ]
            if(is_array($val[0])){
                foreach($val as $v){
                    $placeholder = $placeholder . $key . ' ' . $v[0] . ' ? ' . $v[2] . ' ';
                }
            }
            //値が配列のとき 
            else if(is_array($val[1])){
                //IN句
                if($val[0] == 'IN'){
                    $placeholder = $placeholder . $key . ' IN' . "(";

                    foreach($val[1] as $v){
                        $placeholder = $placeholder . "? ,";
                    }
                    $placeholder = rtrim($placeholder, ',') . ") " . $val[2] . ' ';
                }
                //それ以外
                else{
                    foreach($val[1] as $v){
                        $placeholder = $placeholder . $key .' ' . $val[0] . ' ? ' . $val[2] . ' ';
                    }
                }
            }
            else{
                $placeholder = $placeholder . ' ' . $key . ' ' . $val[0] . ' ? ';
                //論理演算子の配置
                if(!empty($val[2])){
                    $placeholder = $placeholder . $val[2] . ' ';
                }
            }
        }
        return preg_replace('/AND\s*?$|OR\s*?$/', '', $placeholder);
    }

    //データをバリデートして値の配列$valuesを返す($modeを参照してset系操作とwhere系操作で処理を分ける)
    protected static function validateData($Data,$mode = 'both'){
        $values = array();
        //set配列から値を抽出
        if($mode == VALIDATION_MODE_SET || $mode == VALIDATION_MODE_BOTH){
            //setキーに値がないときfalseで抜ける
            if(empty($Data['set'])){
                self::classError(2,'入力値がありません');
                return false;
            }
            foreach($Data['set'] as $key => $value){
                //値が配列の時falseで抜ける
                if(is_array($value)){
                    self::classError(2,'値に配列は設定できません');
                    return false;
                }
                //カラム名が正しいかチェック
                if(self::column_exists($key)){    //呼び出し元の$columns
                    //カラムがint型の場合は$valueをキャスト
                    if(self::is_column_type($key) ==  'int'){
                        $values[] = intval($value);
                    }
                    else{
                        $values[] = $value;
                    }
                    $keys[] = $key;
                }
                //カラムが存在しない場合falseで抜ける
                else{
                    print_r(static::$columns);
                    self::classError(2,'無効なキーです');
                    return false;
                }
            }
        }
        //where配列から値を抽出
        if($mode == VALIDATION_MODE_WHERE || $mode == VALIDATION_MODE_BOTH){
            //whereキーに値がないときfalse
            if(empty($Data['where'])){
                self::classError(2,'条件が設定されていません');
                return false;
            }
            foreach($Data['where'] as $key => $value){
                //カラム名が正しいかチェック
                if(self::column_exists($key)){

                    //一つのキーに対して複数条件が付くとき 'post_num' => [ ['<=',$offset,'AND'] , ['>=', $oldest, 'AND'] ]
                    if(is_array($value[0])){
                        foreach($value as $v){
                            if($v[0] == 'LIKE'){
                                $values[] = self::cast_value($key,$v[1],true);
                            }
                            else{
                                $values[] = self::cast_value($key,$v[1]);
                            }
                        }
                    }
                    //条件値が配列のとき
                    else if(is_array($value[1])){
                        //IN演算子
                        if($value[0] == 'IN'){
                            //条件値が非配列だったとしても配列に変換してやる
                            if(!is_array($value[1])){
                                $value[1] = array($value[1]);
                            }
                            foreach($value[1] as $v){
                                $values[] = self::cast_value($key,$v);
                            }
                        }
                        else if($value[0] == 'LIKE'){
                            if(!is_array($value[1])){
                                $value[1] = array($value[1]);
                            }
                            foreach($value[1] as $v){
                                 $values[] = self::cast_value($key,$v,true);
                            }
                        }
                    }
                    //条件値が非配列のとき
                    else{
                        if($value[0] == 'LIKE'){
                            $values[] = self::cast_value($key,$value[1],true);
                        }
                        else{
                            $values[] = self::cast_value($key,$value[1]);
                        }
                    }
                }
                else{
                    echo $key;
                    self::classError(2,'無効なキーです');
                    return false;
                }
            }
        }
        return $values;
    }

    //カラムがint型の場合はキャストして返す(WHERE句のパラメータだけに適用)
    private static function cast_value($key = '',$val = null,$flag_LIKE = false){
        if(self::is_column_type($key) ==  'int'){
            $ret = intval($val);
        }
        else{
            if($flag_LIKE){
                //echo '<br/>$val:' . $val . '<br/>';
                $word = preg_replace('/\\\\/',"\\\\\\\\",$val);
                $word = preg_replace('/%/','\\\\%',$word);
                $ret = '%' . $word . '%';
                //echo '$preg:' . $ret . '<br/>';
            }
            else{
                $ret = $val;
            }
        }
        return $ret;
    }

    //各テーブル(サブクラス)のカラム情報をstaticメンバ$colulmnsに
    //protected function getColumns();
    protected static function getColumns($tablename){
        try{
            $sql = "SHOW COLUMNS FROM " . $tablename;
            $stmt = self::$database->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $stmt = null;
            return $result;
        }catch(PDOExecption $e){
            echo 'PDOExeption:'; //. $e->getMessage();
        }
    }

    //$keyというカラムが存在するか調べる
    protected function column_exists($key){
        return array_key_exists($key,static::$columns);
    }

    //カラム$keyの型を返す
    protected function is_column_type($key){
        //!strposは文字列の見つかった位置が0番目のとき0を返すことに注意
        if(strpos(static::$columns[$key],'int') !== false){
            return 'int';
        }
        else if(strpos(static::$columns[$key],'char') !== false){
            return 'char';
        }
        else if(strpos(static::$columns[$key],'datetime') !== false){
            return 'datetime';
        }
        /*
        else if(strpos(static::$columns[$key],'bool') !== false){
            return 'bool'
        }
        */
        else{ return  'othertype'; }
    }

    public function getLatestPrimary(){
        $sql = "SHOW COLUMNS FROM " . static::$tableName;
        $pri_key = self::get($sql)[0];
        $sql = "SELECT " . $pri_key['Field'] . " FROM " . static::$tableName . " ORDER BY " . $pri_key['Field'] . " DESC LIMIT 1";
        $res = self::get($sql)[0][$pri_key['Field']];
        return $res;
    }

    //クラス内で発生したエラー表示(本来の呼び出し元(どこまで戻るか)を$nestで指定)
    protected  function classError($nest,$msg){
        /*
		$caller = debug_backtrace();
        echo __CLASS__ . '::';
        for($i=$nest; $i >= 1; $i--){
            if($i == $nest && $i != 1){
               echo $caller[$i]['function'] . '()=>';
            }
            else{
               echo $caller[$i]['function'] . '():' . $msg . '<br/>';
            }

        }
		*/
    }
}

?>
