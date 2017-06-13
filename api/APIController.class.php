<?php
abstract class APIController{

    protected $params;
    protected $request;
    protected $method;

    function __construct(){
        $this->params = $_SESSION['params'];
        $this->request = new Request();
        switch($_SERVER['REQUEST_METHOD']){
        case 'GET':
            $this->method = 'get';
            break;
        case 'POST':
            $this->method = 'post';
            break;
        case 'PUT':
            $this->method = 'put';
            break;
        case 'DELETE':
            $this->method = 'delete';
            break;
        default:
            break;
        }
    }

    public function run(){
        if(isset($this->method)){
            $apimethod = $this->method;
            static::$apimethod();
        }
        else{
            self::error('HTTP method not defined');
        }
    }

    protected function get(){
        ;
    }

    protected function post(){
        ;
    }

    protected function put(){
        ;
    }

    protected function delete(){
        ;
    }

    protected function error($msg = null){
        header("HTTP/1.1 404 Not Found");
        $error_res = [
                    'error' => [ 'message' => null]
                    ];
        $error_res['error']['message'] = $msg;
        echo json_encode($error_res);
        exit;
        //echo $msg;  //debug
    }
}
?>
