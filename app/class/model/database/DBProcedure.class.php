<?php
class DBProcedure{

    private static $dbh = null;

    function __construct(){
        ;
    }

    public static function init(){
        try{
            self::$dbh = new PDO(DB_NAME,DB_PROC_USER,DB_PASS, array(PDO::ATTR_PERSISTENT => true));
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            self::$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        }catch(PDOException $e){
            die('接続エラー'); //:' . $e->getMessage());
        }
    }
/*
    public static function start_taste(){
        try{
            $stmt = self::$dbh->prepare("CALL startTaste()");
            $stmt->execute();
            $stmt->fetchAll();
        }catch(PDOException $e){

            die('接続エラー:' . $e->getMessage());
        }
    }
    public static function end_taste(){
        try{
            $stmt = self::$dbh->prepare("CALL endTaste()");
            $stmt->execute();
            $stmt->fetchAll();
        }catch(PDOException $e){
            die('接続エラー:' . $e->getMessage());
        }
    }
 */
    public static function tasteWithUser($mynum,$targetnum,$genre = '%', $category = '%',$tags = '%'){
        if($genre === '' || $genre == null){
            $genre = '%';
        }
        if($category === '' || $category == null){
            $category = '%';
        }
        if($tags === '' || $tags == null){
            $tags = '%';
        }
        try{
            //self::start_taste();
            $stmt = self::$dbh->prepare("CALL tasteWithUser(?,?,?,?,?)");
            $stmt->bindParam(1,$mynum,PDO::PARAM_INT);
            $stmt->bindParam(2,$targetnum,PDO::PARAM_INT);
            $stmt->bindParam(3,$genre,PDO::PARAM_STR);
            $stmt->bindParam(4,$category,PDO::PARAM_STR);
            $stmt->bindParam(5,$tags,PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll();
            //self::end_taste();
            if(!empty($result)){
                return $result[0];
            }
            else{
                return false;
            }
        }catch(PDOException $e){
            die('taste()エラー:' . $e->getMessage());
        }
    }
    
    public static function tasteWithContentFollowers($mynum,$content_num = 0,$genre = '%', $category = '%',$tags = '%',$limit = 10, $offset = 0){
        if($genre === '' || $genre == null){
            $genre = '%';
        }
        if($category === '' || $category == null){
            $category = '%';
        }
        if($tags === '' || $tags == null){
            $tags = '%';
        }
        try{
            //self::start_taste();
            $stmt = self::$dbh->prepare('CALL tasteWithContentFollowers(?,?,?,?,?,?,?)');
            $stmt->bindParam(1,$mynum,PDO::PARAM_INT);
            $stmt->bindParam(2,$content_num,PDO::PARAM_INT);
            $stmt->bindParam(3,$genre,PDO::PARAM_STR);
            $stmt->bindParam(4,$category,PDO::PARAM_STR);
            $stmt->bindParam(5,$tags,PDO::PARAM_STR);
            $stmt->bindParam(6,$limit,PDO::PARAM_INT);
            $stmt->bindParam(7,$offset,PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll();
            //self::end_taste();
            return $result;
        }catch(PDOException $e){
            die('tastewithcontentfollowers()エラー:' . $e->getMessage());
        }
    }
}
?>
