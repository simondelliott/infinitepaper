<?php

class Route {
    public $method = "GET";
    public $controler = "";
    public $action = "";
    public $pattern = NULL;
    public $symbols = array();
    public $matches = array(); //symbols that are use will appear in here 
    
    public function __construct($obj=NULL){
        if ($obj!=NULL){

           $properties = get_object_vars($this);

           foreach ($obj as $k=>$v){
               if(array_key_exists($k, $properties)){
                   $this->$k = $v;
               }
               else{
                   $this->symbols[$k] = $v;
               }
           }
        }
    }

    private function get_pattern(){
        //the pattern can contain symbols prceded by a colon eg :city_name
        //replace symbols with their patterns, these will have been passed in the constructor
        if ($this->pattern == NULL)
            return "/$this->controler\/$this->action/";

        $p = $this->pattern;
        foreach($this->symbols as $k=>$v){
            $p = str_replace(":$k", "(?P<$k>$v)", $p);
        }

        return $p;
    }

    public function get_route(){
        $this->match();
        return $this->matches[0];
    }

    public function match(){
        if($_SERVER["REQUEST_METHOD"]!=$this->method)
            return false;

        $res = preg_match($this->get_pattern(), $_SERVER["REDIRECT_URL"], $this->matches);
        if($res == FALSE || $res==0)
            return FALSE;
        else
            return TRUE;
    }

    public function get_controler_class(){
        $controler_name = $this->controler .= "Controler";
        return ucfirst($controler_name);
    }

}
?>