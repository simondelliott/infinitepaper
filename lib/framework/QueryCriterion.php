<?php

class QueryCriterion {
	
	public $operator = "=";
	public $value = "";
	
	public function __construct($op, $val){
	 	$this->operator = $op;
	 	$this->value = $val;
	}
	
}
?>