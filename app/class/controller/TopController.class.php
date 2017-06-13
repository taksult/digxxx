<?php
class TopController extends Controller{
    private $posts;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
    }

    public function run(){
        $_SESSION['online'] = false;
        $_SESSION['token'] = genToken(session_id());
        $this->view->display();
    }

}


?>
