<?php
abstract class Controller
{
    protected $root;
    protected $request;
    protected $view;
    protected $view_module = [];


    function __construct($path){
        $this->request = new Request();
        $this->root = PATH_ROOT;
        $this->view = new View($path);
    }

    abstract function run();
}

?>
