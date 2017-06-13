<?php
class PolicyController extends Controller{
    private $posts;

    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
    }

    public function run(){
        $this->view->display();
    }

}

?>