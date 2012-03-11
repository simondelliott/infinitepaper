<?php

/**
 * 
 * @author developmentcollective
 * @version @@@version
 * @package fmm 
 */
class Setting {
	
	/** cache of items that have been used already */
	private static $cache = array();

	/**
	 * creates a new setting
	 * @param $key the name of the setting
	 * @param $value the value that you wish to store
	 * @return String the value
	 */
	public static function put ($key, $value){		
		if (Setting::$cache[$key]==$value){
			return $value;
		}
		$database = new database( _DB_SERVER, _DB_USER, _DB_PASS, _DB_NAME, '');
		$database->setQuery ( "DELETE FROM setting WHERE name = '$key'" );
		if (!$database->query ()){
			handle_error('unexpected database query failure ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg());
		}
		
		$database->setQuery ( "INSERT INTO setting (name, value) VALUES ('$key','$value')" );
		if (!$database->query ()){
			handle_error('unexpected database query failure ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg());
		}
		
		Setting::$cache[$key] = $value;
		return Setting::$cache[$key];
	}

	public static function get ($key){
            if (Setting::$cache[$key]!=NULL){
                return Setting::$cache[$key];
            }

            $database = new database( _DB_SERVER, _DB_USER, _DB_PASS, _DB_NAME, '');
            $sql = "SELECT value FROM setting WHERE name='$key'";
            $database->setQuery ( $sql );
		
            if ($database->loadObject ( $row )) {
                Setting::$cache[$key] = $row->value;
                return Setting::$cache[$key];
            }
            else{
                $msg = $database->getErrorMsg() . "_" . $database->getErrorNum();
                $match = ereg("setting.*doesn.*exist", $msg);

                if ($match){
                    Setting::add_table();
                }
                return NULL;
            }
	}		

	private static function add_table(){
            $database = new database( _DB_SERVER, _DB_USER, _DB_PASS, _DB_NAME, '');
            $sql = "";
            $sql .= "CREATE TABLE `setting` (";
            $sql .= "`name`                  varchar(50)             NOT NULL UNIQUE,";
            $sql .= "`value`                 varchar(500)            NOT NULL,";
            $sql .= "PRIMARY KEY (`name`)";
            $sql .= ");";

            $database->setQuery ( $sql );
            if(!$database->query ()){
                    handle_error('unexpected database query failure ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg());
            }
	}
	
	public static function delete ($key){
		if (Setting::$cache[$key]!=NULL){
			Setting::$cache[$key] = NULL;
		}
		
		$database = new database( _DB_SERVER, _DB_USER, _DB_PASS, _DB_NAME, '');
		$database->setQuery ( "DELETE FROM setting WHERE name = '$key'" );
		if(!$database->query ()){
			handle_error('unexpected database query failure ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg());
		}
		
		return true;
	}
}
?>