<?php
class SessionValues extends Values{

    function __construct(){
        $this->setValues($_SESSION);
    }

    /*
    protected function setValues(){
        $this->values = $_SESSION;
    }
     */
}
?>
