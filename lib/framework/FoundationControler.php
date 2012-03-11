<?php

class FoundationControler {
	
    public $params = array();
    private $action = "";

    public function __construct( $act, $params = NULL ) {
        $this->action = $act;
        $this->params = $params;

        if ($this->action != NULL){
            call_user_func_array(array($this, $this->action), array());
        }
        else{
            header("HTTP/1.0 404 Not Found");
            exit();
        }
    }

}
?>