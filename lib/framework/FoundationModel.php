<?php

class FoundationModel {
	
    /** a cache of properties that have been initalized by __get magic function */
    private $property_cache = null;

    /** the table associated with the model object */
    private $_table_name = null;

    /** an array of the errors from the last time that the object was validated - only one entry per field */
    public $_validate_errors = array();

    /**
     * determines if the model object has been persisted.
     * requires the id field to be set.
     * @return boolean
     */
    public function has_been_saved (){
        if( $this->id == null ){
            return false;
        }
        elseif( $this->id == 0 ){
            return false;
        }
        else{
            return true;
        }
    }
	
    /**
     * Constructor for all the Model objects
     * It accepts an associative array of NVP's
     * It then matches these to model properties and populates the object.
     *
     * @param $obj the assocative array of NVPs for the properties
     */
    public function __construct($obj=NULL){
        if ($obj!=NULL){
            $properties = get_object_vars($this);
            foreach($properties as $k=>$v){
                if($obj[$k]!=NULL){
                    $this->$k = $obj[$k];
                }
            }
        }
        $this->_table_name = strtolower(get_class($this));
    }
	
	
	
	/**
	 * validates that the peoprty is not null
	 * 
	 * @return Boolean the validation success
	 */
	public function validate_not_null($thing_to_vaidate){
		$properties = get_object_vars($this);
		$value = $properties[$thing_to_vaidate];
		
		if ($value == NULL ){
			$this->_validate_errors[$thing_to_vaidate] = $thing_to_vaidate . " must be supplied";
			return false;
		}
		return true;
	}

	public function validate_made_by_current_user(){
    	if ($this->user_id != User::get_current_user()->id ){
			$this->_validate_errors["user_id"] = "Only current user is allowed to change this";
			return false;
		}
    	return true;
    }

	/**
	 * validates the length of a field against a set of conditions
	 * conditions are 
	 * <ul>
	 * <li>less_than</li>
	 * <li>greater_than</li>
	 * </ul>
	 *
	 * @param string $thing_to_vaidate
	 * @param array $condition
	 * @return boolean
	 */
	public function validate_length_of ($thing_to_vaidate, $condition){
		$properties = get_object_vars($this);
		$value = $properties[$thing_to_vaidate];
		foreach ($condition as $k=>$v) {
			if ($k == "less_than" && strlen($value) >= $v){
				$this->_validate_errors[$thing_to_vaidate] = $thing_to_vaidate . " must be shorter than " . $v;
				return false; 
			}
			elseif ($k == "greater_than" && strlen($value) <= $v){
                                $this->_validate_errors[$thing_to_vaidate] = $thing_to_vaidate . " must be longer than " . $v . " characters";
				return false;
			}
			elseif ($k == "greater_than_or_equal_to" && strlen($value) < $v){
                                $this->_validate_errors[$thing_to_vaidate] = $thing_to_vaidate . " must be longer than " . $v . " characters";
				return false;
			}
		}
		return true;
	}
	
	/**
	 * validates that the field conforms to a regex pattern
	 * If the field fails validation then the error message is added to the
	 * _vaidate_errors array
	 * 
	 * @return boolean wheter the validation passed
	 * @param $thing_to_vaidate string the filed that is to be validated
	 * @param $pattern the regex pattern to validate against
	 * @param $message the error message to show is the validation fails
	 */
	public function validate_pattern($thing_to_vaidate, $pattern, $message ){
		$properties = get_object_vars($this);
		$value = $properties[$thing_to_vaidate];
		
		$x = preg_match($pattern, $value);
		if ($x == 0){
			$this->_validate_errors[$thing_to_vaidate] = $message;
			return false;
		}
		return true;
	}
	
	/**
	 * validates that the fields are the same
	 * If the field fails validation then the error message is added to the
	 * _vaidate_errors array
	 * 
	 * @return boolean whether the validation passed
	 * @param $first - first field
	 * @param $second - second field
	 * @param $message the error message to show is the validation fails
	 */
	public function validate_same($first, $second, $compareto, $message ){
		$properties = get_object_vars($this);
		$value1 = $properties[$first];
		
		if (strcmp($value1, $compareto) != 0){
			$this->_validate_errors[$second] = $message;
			return false;
		}
		return true;
	}

	/**
	 * @param  $thing_to_vaidate object the filed that you want to ensure is unique
	 * @param  $message the message to display if it is not
	 */
	public function validate_uniqueness_of ($thing_to_vaidate, $message){

                $properties = get_object_vars($this);
		$value = $properties[$thing_to_vaidate];
		
		$database=new Database();
		$row = null;

                $value = str_replace("'", "\'", $value);
		
		$SQL = "SELECT id FROM " . $this->_table_name . " WHERE " . $thing_to_vaidate . "='" . $value . "' AND id != '" . $this->id . "' LIMIT 1";
                
		$database->setQuery ( $SQL);
		if (!$database->query()){
			handle_error("unexpected SQL error", E_USER_ERROR);       			
			return false;	
		}
		
		$rows = $database->loadRowList();
		unset($database);
		
		if ($rows[0] == NULL){
			return true;
		}
		else{
			$this->_validate_errors[$thing_to_vaidate] = $message;
			return false;	
		}				
	}

	
	public function validate_unique_combination ($things_to_vaidate, $message){
		//TODO: refactor this into validate uniqueness of
		
		$properties = get_object_vars($this);
		
		$where = "";
		foreach ($things_to_vaidate as $k) {
			$where = $k . "='" . $properties[$k] ."' AND ";
		}
		
		$database=new Database();
		$row = null;
		
		$SQL = "SELECT id FROM " . $this->_table_name . " WHERE " . $where . " id != '" . $this->id . "' LIMIT 1";

		$database->setQuery ( $SQL);
		if (!$database->query()){
			handle_error("unexpected SQL error", E_USER_ERROR);       			
			return false;	
		}
		
		$rows = $database->loadRowList();
		unset($database);
		
		if ($rows[0] == NULL){
			return true;
		}
		else{
			$this->_validate_errors[ implode(",",$things_to_vaidate) ] = $message;
			return false;	
		}				
	}
		
	
	
	/**
	 * persists the data in the sub class to the database
	 * the name of the subclass must correspond to an object name in the database.
	 * 
	 * @return boolean status of the call.
	 */
	public function save(){	
                $ret = false;
		if (method_exists($this, "validate") && !$this->validate()){
                    return false;
		}

		$database=new Database();
		if ($this->id == NULL || $this->id == 0){
			// its a new object 
			$ret = $database->insertObject( $this->_table_name, $this, "id");
		}
		else{
			$ret = $database->updateObject( $this->_table_name, $this, "id");
		}
		
		if(!$ret){
			handle_error('save failed: ' . $database->getErrorNum() . ":" .  $database->getErrorMsg(), E_USER_ERROR);       			
			return false;	
		}
		return $ret;
	}	

	/**
	 * removes an object from the database
	 * 
	 * @return Boolean the success of the call, from teh database object
	 */
	public function delete(){	
		$database=new Database();
		
		if ($this->id != NULL && $this->id != 0){
			$SQL = "DELETE FROM " . $this->_table_name . " WHERE id=" . $this->id;

			$database->setQuery ( $SQL);
			return $database->query();
		}
	}	

	public function __get($name) {
		
        if ($this->property_cache[$name] != NULL ) {
            return $this->property_cache[$name];
        }

		$find_result = null;
        $model_class_name = ucwords($name);
        $property_name = $name ."_id";
        $property_exists = property_exists( $this, $property_name );
        
        if(property_exists ( $this, $property_name )){
        	//look for an _id field if it exists initalise the object and return it
        	$find_call = "return " . $model_class_name . "::find_by_id(array(id => " . $this->$property_name ."));";
        	$find_result = eval ($find_call);
        }
        elseif (class_exists( $model_class_name ) ){
        	//check for an object class of this name, it it has a [table_name]_id field then get them
        	$filed_name = $this->_table_name . "_id";
        	$id_to_find = $this->id == null ? "NULL": $this->id;
        	$find_call = "return " . $model_class_name . "::find_by_" . $filed_name. "(array('$filed_name'=>" . $id_to_find . "));";
			//debug("find_call is $find_call");
        	$find_result = eval ($find_call);
        }
        else{
        	// if nothing exists, so generate an error 
			handle_error("undefined property", E_USER_ERROR);
            return null;
        }
        
        //Add the result to the cache
        $this->property_cache[$name] = $find_result;
        
        //return the cache 
        return $this->property_cache[$name];
    }	

    
	public function __set($name, $value) {
		
        if ($this->property_cache[$name] != NULL ) {
            $this->property_cache[$name] = $value;
        }
        $model_class_name = ucwords($name);
        $property_name = $name ."_id";
        $property_exists = property_exists( $this, $property_name );
        
        if(property_exists ( $this, $property_name && $this->_table_name == $model_class_name )){
        	$this->property_cache[$name] = $value;
        	$this->$property_name = $value->id;
        }
        elseif (class_exists( $model_class_name )){
        	$filed_name = strtolower($this->_table_name) . "_id";
        	$this->property_cache[$name] = $value;
			foreach($value as $obj){
				$obj->$filed_name = $this->id;
			}
        }
        else{
			handle_error('Setting undefined property via __set(): ' . $name, E_USER_NOTICE);
            return null;
        }
    }	
    
    
	/**
	 * expresses the model object as a JSON string
	 * 
	 * @return string the object. 
	 */
	public function toJSON(){
		$json = "{";
		$properties = get_object_vars($this);
		foreach ($properties as $k=>$v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			
			$json .= '"' . $k . '":';
			$typ = gettype($v);
			if ($typ=="string"){
				$json .= '"' . $v . '"';	
			}
			else{
				$json .= $v;	
			}
			$json .= ', ';
		}
		if(strlen($json)>1){
			$json = substr($json, 0,strlen($json) -2); //remove the comma and the space 
		}
		$json .= "}";
		return $json;
	}

	/**
	* Export item list to xml
	* @param boolean Map foreign keys to text values
	*/
	function toXML( ) {
		$node_name = $this->_table_name;
		$xml = "<$node_name>";
                foreach (get_object_vars( $this ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}
		$xml .= '</' . $node_name . '>';
		return $xml;
	}

	/**
        * Export item list to xml
	* @param boolean Map foreign keys to text values
	*/
	function toTR( ) {
            $ret = "<tr>";
            foreach (get_object_vars( $this ) as $k => $v) {
                if (is_array($v) or is_object($v) or $v === NULL) {
                        continue;
                }
                if ($k[0] == '_') { // internal field
                        continue;
                }
                $ret .= "<td>$k $v</td>";
            }
            $ret .= "</tr>";

            return $ret;
	}

    function output_as_xml(){
        set_xml_header();
        echo $this->toXML();
    }

}
?>