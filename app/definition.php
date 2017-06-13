<?php
DEFINE('HOSTNAME', '192.168.1.10');
DEFINE('DS', DIRECTORY_SEPARATOR);
//PHP用ディレクトリパス
DEFINE('PATH_ROOT', dirname(__FILE__) . DS);
const PATH_INDEX = ['192.168.1.10/','192.168.1.10/index.php'];
DEFINE('PATH_CLASSDIR', dirname(__FILE__) . DS . 'class' . DS);
DEFINE('PATH_API', '/productroot/api/');
DEFINE('PATH_INNERAPI', '/productroot/app/i');
const PATHS_CLASS = [  'controller'     => PATH_CLASSDIR . 'controller' . DS,
                       'model'          => PATH_CLASSDIR . 'model' . DS,
                       'model_database' => PATH_CLASSDIR . 'model'. DS . 'database'. DS,
                       'view'           => PATH_CLASSDIR . 'view' . DS,
                       'api'            => PATH_API,
                       //'view_templates'  => PATH_CLASSDIR .'view' . DS . 'templates' . DS,
                   ];
DEFINE('PATH_TEMPLATES', PATHS_CLASS['view']. DS. 'templates' . DS);
DEFINE('PATH_LIBRARY',dirname(__FILE__) . DS . 'library.php');
DEFINE('PATH_CONTENT_ARTICLES',PATHS_CLASS['view']. 'templates/contents/articles/');
const PATH_NOTLOGIN = ['top', 'login', 'registration','guest'];
//----------------------------------------------------

//ページ埋め込み用ディレクトリパス
DEFINE('PATH_IMG_ICON', '/file/img/icon/');
DEFINE('PATH_IMG_CHKLIST','/file/img/checklist/');
//-----------------------------------------------------


//DB関連-----------------------------------------------
DEFINE('DB_NAME', 'mysql:dbname=sb0;charset=UTF8;');
DEFINE('DB_USER','username');
DEFINE('DB_PROC_USER','username');
DEFINE('DB_PASS', 'hogehoge');


//Databaseのバリデーション設定用
DEFINE('VALIDATION_MODE_SET', 1);
DEFINE('VALIDATION_MODE_WHERE',2);
DEFINE('VALIDATION_MODE_BOTH', 3);
//------------------------------------------------------


//ファイル処理
DEFINE('THUMB_SIZE',300);

//クラスのオートローダ設定
function my_autoloader($class){
    foreach(PATHS_CLASS as $key => $dir){
        $path = $dir . $class . '.class.php';
        if(is_readable($path)){
            include $path;
        }
    }
}
spl_autoload_register('my_autoloader');

//日本時刻設定
date_default_timezone_set('Asia/Tokyo');


const GENRES = ['music','game','comic','illust','novel','movie','anime','sport','vehicle','web','gadget','apparel','tech','-'];
const CATEGORIES = ['人物','グループ','作品','製品','キャラクター','ウェブサイト','-'];
?>
