<?php
class GuestController extends Controller{
    private $posts;

    function __construct($path){
        //$this->request = new Request();
        $this->root = PATH_ROOT;
        //$this->view = new View($path);
    }

    public function run(){
        $_SESSION['online'] = false;
        $_SESSION['token'] = genToken(session_id());
        $_SESSION['user_id'] = "@guest";
        $_SESSION['user_num'] = -1;
        header('Location: /home/');
        exit;
    }

}


?>
