<?php
abstract class Values
{
    protected $values = [];

    function __construct(){
        static::setValues();
    }

    protected function setValues($values = null){
        if(!empty($values)){
            array_walk_recursive($values,function(&$val,$key){
                if(strpos($val,"\0") !== false){
                    echo '不正なリクエストを検出しました';
                    file_put_contents(PATH_ROOT . 'log/badreqs/request.log', 'user:'.$_SESSION['user_num'] . ' key:'. $key . ' value:'. $val . "\r\n" ,FILE_APPEND | LOCK_EX);
                    exit;
                }
                else{
                    $val = str_replace('\0','',$val);
                    if(is_string($val)){
                        //$vが数字列ならint型に変換
                        if(preg_match('/^[0-9]+$/',$val)){
                        $this->values[$key] = intval($val);
                        }
                        else{
                            $this->values[$key] = $val;
                        }
                    }
                    else{
                        $this->values[$key] = $val;
                    }
                }
            });
            unset($val);
        }
    }

    public function updateValues(){
        $this->setValues();
    }

    public function get($key = null){
        if($key == null){
            return $this->values;
        }
        else if(is_int($key)){
            if(isset($this->values[$key])){
                return $this->values[$key];
            }
            else{
                return false;
            }
        }
        else if($this->hasKey($key)){
            return $this->values[$key];
        }
        else{
            //echo 'キーが存在しません<br/>';
            return false;
        }
    }
   
    //グローバル変数に関しては直接代入ほぼしない(セッション除く)ので封印
/*
    public function set($key = null,$values){
        if($key == null){
            $this->values = $values;
        }
        else{
            $this->values[$key] = $values;
        }
    }
*/

    public function hasKey($key){
        if(array_key_exists($key, $this->values)){
            return true;
        }
        else{
            return false;
        }
    }

    protected function setValuesReplaceNull(&$val,$key){
            }
}
?>
