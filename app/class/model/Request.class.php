<?php
class Request
{
    protected $post;    //$_POST
    protected $get;     //$_GET
    protected $session;

    function __construct(){
        $this->post = new PostValues();
        $this->get = new GetValues();
        $this->session = new SessionValues();
    }

    public function update(){
        $this->post->updateValues();
        $this->get->updateValues();
        $this->session->updateValues();
    }

    public function get(){
        return array_merge($this->post->get(), $this->get->get(), $this->session->get() );
    }

    public function getPostValues($key = null){
        self::update();
        return $this->post->get($key);
    }

    public function getGetValues($key = null){
        self::update();
        return $this->get->get($key);
    }

    public function getSessionValues($key = null){
        self::update();
        return $this->session->get($key);
    }

    //トークンが有効かチェック
    public function chkToken(){
        //echo $this->getSessionValues('token');
        if($this->getPostValues('token') != false && $this->getSessionValues('token') == $this->getPostValues('token') ){
            return true;
        }
        else{
            return false;
        }
    }
}


?>
