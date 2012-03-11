<?php

class BaseModel extends FoundationModel {

    public $id;
	
    public static function find_by_id($obj){
        $id = $obj["id"];
        if ($id==NULL)
            return NULL;

        $obj = get_instance_for_model_object(__CLASS__, array("id" => $id));
        return $obj;
    }

    public static function get_all(){
        return get_result_set_for_model_object(__CLASS__);
    }
	
    public function validate (){
        $ret = true;

        //clear the validation errors
        $this->_validate_errors = array();

        return $ret;
    }

}
?>
