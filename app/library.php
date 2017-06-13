<?php
//そのうち種類ごとにファイルまとめる

//--XSS対策関連
//エスケープ処理(テンプレートアサイン用)
function h($str){
    $str  = str_replace(["\r\n","\r","\n"],'</p><p>',htmlspecialchars($str,ENT_QUOTES,'UTF-8'));
    return str_replace("<p></p>","<br/>",$str);
}

//エスケープ処理(JSON返す用)
function str_escape($str){
    if(!is_array($str)){
        if(is_string($str) || true){
            $str = str_replace(["\r\n","\r","\n"],'</p><p>',htmlspecialchars($str,ENT_QUOTES,'UTF-8'));
            return  str_replace("<p></p>","<br/>",$str);
        }
    }
    //配列の時はすべての要素をエスケープ
    else{
        $ret = [];
        $ret = $str;
        array_walk_recursive($ret,function(&$val,$key){
            if(is_string($val)){
                $val = str_replace(["\r\n","\r","\n"],'</p><p>',htmlspecialchars($val,ENT_QUOTES,'UTF-8'));
                $val = str_replace("<p></p>","<br/>",$val);
            }
        });
        unset($val);
        return $ret;
    }
}

//一部のURL予約文字以外をエンコード
function ex_urlencode($uri = null){
    $ret = urlencode($uri);
    $ret = preg_replace('/[+]/',' ',$ret);
    return $ret;
}

//一部のURL予約文字以外をデコード
function ex_urldecode($uri = null){
    $ret = preg_replace('/\+/u','＋',$uri);
    $ret = str_replace('　',' ',urldecode($ret));
    $ret = preg_replace('/＋/u','+',$ret);
    return $ret;
}


//URL判定
function is_uri( $uri )
{
    if ( preg_match( "|[^-/?:#@&=+$,\w.!~*;'()%]|", $uri ) ) {
        return FALSE;
    }
    if ( ! preg_match(
          "!^(?:https?|ftp)://"                     // scheme( http | https | ftp )
        . "(?:\w+:\w+@)?"                           // ( user:pass )?
        . "("
        . "(?:[-_0-9a-z]+\.)+(?:[a-z]+)\.?|"        // ( domain name |
        . "\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|"     //   IP Address  |
        . "localhost"                               //   localhost )
        . ")"
        . "(?::\d{1,5})?(?:/|$)!iD",                // ( :Port )?
        $uri )
    ) {
        return FALSE;
    }
    return TRUE;
}

//URLにアクセスしてステータスコードを取得
function get_status_code($url){
    if(is_uri($url)){
        $option = [
            CURLOPT_RETURNTRANSFER => true, //文字列として返す
            CURLOPT_TIMEOUT        => 3, // タイムアウト時間
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, $option);

        $json    = curl_exec($ch);
        $info    = curl_getinfo($ch);
        $errorNo = curl_errno($ch);

        // OK以外はエラーなので空白配列を返す
        if ($errorNo !== CURLE_OK) {
            // 詳しくエラーハンドリングしたい場合はerrorNoで確認
            // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
            return false;
        }
        // 200以外のステータスコードは失敗とみなし空配列を返す
        /*
        if ($info['http_code'] !== 200) {
            return false;
        }
        */
        return $info['http_code'];
        
    }
    else{
        return false;
    }
}


//--トークン関連
function genToken($seed){
    return hash("sha256",$seed);
}

function chkToken(){
    if(isset($_POST['token']) && $_SESSION['token'] == $_POST['token']){
        return true;
    }
    else{ return false; }
}

//日付取得
function mydate(){
    return @date('Y-m-d H:i:s');
}

function mydate_shift($shift = null){
    if(is_string($shift)){
        $dt = new DateTime();
        $dt = $dt->modify($shift);
        return $dt->format('Y-m-d H:i:s');
    }
    else{
        return false;
    }
}

//ランダム文字列生成
function makeRandStr($length = 15) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}

//画像アップロード
function uploadImageFile($file = null, $dir,$form_index,$max_size = 1000000){
    if(isset($file['error']) && is_int($file['error'])){
        switch($file['error']){
            case UPLOAD_ERR_OK;
                break;
            case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                //echo 'ファイルが選択されていません';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo 'ファイルサイズが大きすぎます';
                return false;
                break;
            default:
                break;
        }
        //1MB以上は受け付けない
        if($file['size'] > $max_size){
            echo 'ファイルサイズが大きすぎます';
            return false;
        }

        $ext = array_search(mime_content_type($file['tmp_name']),
            ['gif' => 'image/gif','jpg' => 'image/jpeg','png' => 'image/png'],true);
        if(!$ext){
            echo '不正なファイル形式です';
            return false;
        }

        $filename = makeRandStr();
        $i = 16;
        while(is_file($dir . DS . $filename . '.' . $ext)){
            $filename = makeRandStr($i);
            $i++;
        }
        unset($i);
        $filename = $filename . '.' . $ext;
        if(move_uploaded_file($_FILES[$form_index]['tmp_name'],$dir.$filename)){
            //Exif情報の削除
            if($ext === 'jpg'){
                $gd = imagecreatefromjpeg($dir.$filename);
                $w = imagesx( $gd );
                $h = imagesy( $gd );
                $gd_out = imagecreatetruecolor( $w, $h );
                imagecopyresampled( $gd_out, $gd, 0,0,0,0, $w,$h,$w,$h );
                imagejpeg( $gd_out, $dir.$filename);
            }
            //ファイル名を返す
            return $filename;
        }
        else{
            echo 'エラーが発生しました';
            return false;
        }
    }
}


//第一引数の配列の値を第二引数の配列の同一キーの値で上書き(存在しないキーとnull値は無視)
function array_overwrite(array $base, array $src){
    $ret = $base;
    foreach($src as $k => $v){
        //ベース側にキーが存在すれば上書き(整数インデックスは無視)
        if(array_key_exists($k, $base) && !is_int($k)){
            if($v !== null){
                $ret[$k] = $v;
            }
        }
    }
    return $ret;
}

//soundcloudのトラックIDを末尾に付与
function appendSoundCloudInfo($url){
    $check_url = $url;
    if(preg_match('/^https:\/\/.*$/',$check_url)){
        $check_url = preg_replace('/^https:(.*)$/',"http:$1",$check_url);
    }
    $status_code = get_status_code($check_url);
    if($status_code === 200 || $status_code === 301){
        $param = null;
        if(preg_match('/^.*?\/soundcloud.com\/(.*)$/',$url)){
            $param = preg_replace('/^.*?\/soundcloud.com\/(.*)$/',"$1",$url);
        }
        if($param !== null){
            $call = 'python ' . PATH_ROOT . 'get_sc_trackid.py ' . escapeshellarg($param);
            exec($call, $out);
            if(!empty($out)){
                if(preg_match('/^.*?\/soundcloud.com\/(.*?)\/sets\/(.*)$/',$url)){
                    return $url . '?|playlist_id=' . $out[0];
                }
                else{
                    return $url . '?|track_id=' . $out[0];
                }
            }
            else{
                return $url;
            }
        }
        else{
            //echo "bad paramater\n";
            return $url;
        }
    }
    else{
        //echo "page does not exist\n"; //debug
        return $url;
    }
}

?>
