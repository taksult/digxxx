<?php
class Post{
    
    private $Data;
    private static $dataTemp = ['post_num' => null,
        'user_num' => null,
        'user_id' => null,
        'content_name' => null,
        'reference_url' => null,
        'post_comment' => null,
        'genre' => '',
        'tags' => '',
        'post_image_name' => null,
        'scope' => null,
        'dig' => null,
        'nsfw' => null,
        'hidden_user' => null,
        'regdate' => null
    ];

    //private static $genreList = ['','music','movie','comic','game','novel','tv','device'];

    function __construct($postData = null){
        $this->Data = self::$dataTemp;
        $this->set($postData);
    }

    public function set($postData = null){
        if($postData != null){
            $this->Data = array_overwrite($this->Data,$postData);
            return true;
        }
        else{
            return false;
        }
    }

    public function setFromDB($post_num = null){
        if($post_num != null && is_int($post_num)){
            $this->Data = array_overwrite(self::$dataTemp,PostDataDatabase::selectByNum($post_num));
        }
    }
    public function get(){
        if(!empty($this->Data)){
            return $this->Data;
        }
        else{
            return false;
        }
    }

    public function getUserNum(){
        return $this->Data['user_num'];
    }

    //自身に一致する投稿をDBから削除
    public function delete(){
        if(!empty($this->Data['post_num'])){
            return PostDataDatabase::deletePost($this->Data['post_num']);
        }
        else{
            return false;
        }
    }
    //投稿(DB登録)
    public function post(){
        if(self::validateData() === true){
            if(preg_match('/^.*?\/soundcloud.com\/(.*)$/',$this->Data['reference_url'])){
                $this->Data['reference_url'] = appendSoundCloudInfo($this->Data['reference_url']);
            }
            PostDataDatabase::createPost($this->Data);
            return true;
        }
        else{
            return false;
        }
    }

    //投稿データバリデーション
    public function validateData(){
        $valid = true;
        $msg = '';
        //コンテンツ名が空ならfalse
        if(empty($this->Data['content_name'])){
            $msg = $msg . "コンテンツ名を入力してください\r\n";
            $valid = false;
        }
        
        if(!empty($this->Data['comment']) && mb_strlen($this->Data['comment']) > 20){
            $msg = $msg . "コメントは20文字以内で入力してください\r\n";
            $valid = false;
        }

        //URLが不正ならfalse
        if($this->Data['reference_url'] != null && !is_uri($this->Data['reference_url'])){
            $msg = $msg . "URLが不正です\r\n";
            $valid = false;
        }

        if($valid){
            return true;
        }
        else{
            return $msg;
        }
    }

}
?>
