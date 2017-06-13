<?php
class GetValues extends Values{

    function __construct(){
        $this->setValues($_GET);
    }
    /*
    protected function setValues(){
        $this->values = $_GET;
    }
    */
}


?>
