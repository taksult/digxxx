<?php
class JumpController extends Controller{

    function __construct(){
        $this->request = new Request();
        $this->view = new View('jump.tpl');
    }

    public function run(){
        $url = preg_replace('/^.*?\/jump\/\?url=(.*)$/',"$1",$_SERVER['REQUEST_URI']);
        if(!empty($url)){
            if(preg_match('/^http:\/\/diglue.com.*|https:\/\/diglue.com.*/',$url)){
                header('Location: '. $url);
            }
            else{
                $this->view->assign($url,'reference_url');
                $this->view->display();
            }
        }
        else{
            header("HTTP/1.1 404 Not Found");
            include(PATH_ROOT . 'missing.php');
            exit;
        }
    }
}


?>
