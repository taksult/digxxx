<?php
class PostValues extends Values{
    
    function __construct(){
        $this->setValues($_POST);
    }
    
}
?>
