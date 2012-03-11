<?php

/**
 * A representation of a session,
 * Stores session data in the database.
 * 
 * @author developmentcollective
 * @version @@@version
 * @package fmm 
 */
class Session {
	const COOKIE_NAME = SESSION_NAME;					// the name of the cookie
	const COOKIE_DOMAIN = COOKIE_DOMAIN;	// must be set for redundency (DO NOT USE ON A DEV SYSTEM as it makes bugs that very hard to track down)
	const COOKIE_PATH = "/";							// this must be set for some browsers like FF wich will behave dieferntly

	/** the identifier of the session stored on the cleint and in the database*/
	public static $session_id = NULL;
	
	/** cache of items that have been already adeed to the session */
	public static $cache = array();
	
	/**
	 * makes sure that the tables for the session exist in the database.
	 * If the tables are missing it creates them and returns success.
	 * 
	 * @param database the database object that should contain the tables
	 * @return boolean true if tables need to be made, false if not 
	 */
	private static function ensure_session_table_exists($database){
		if ($database->getErrorNum()==0){
			return true;
		}
		elseif ($database->getErrorNum()==1146){
			//the session table is missing ! so add it
			Session::add_table($database);
			return true;
		}
		else{
			handle_error($database->getPrettyErrorMessage());
			return false;
		}
	}
	
	/** creates the session database tables */
	private static function add_table($database){
		
		$sql = "";
        $sql .= "CREATE TABLE session (";
        $sql .= "session_id 	varchar(50) NOT NULL,";
        $sql .= "expire 		datetime default NULL,";
        $sql .= "last_activity datetime default NULL,";
        $sql .= "PRIMARY KEY  (session_id)";
        $sql .= ");";

        $database->setQuery ( $sql );
		if(!$database->query ()){
			handle_error('Creating session table failed: error number:' . $database->getErrorNum() . " error message :" . $database->getErrorMsg() , E_USER_NOTICE);
		}

        $sql = "CREATE TABLE session_data (";
        $sql .= "session_id 	varchar(50) NOT NULL,";
        $sql .= "data_key 		varchar(50) default NULL,";
        $sql .= "data_value 	varchar(4096) default NULL";
        $sql .= ");";
        $database->setQuery ( $sql );
		if(!$database->query ()){
			handle_error('Creating session_data table failed: error number:' . $database->getErrorNum() . " error message :" . $database->getErrorMsg() , E_USER_NOTICE);
		}
	}

	
	/**
	 * creates a random number for the session and sets it as the cleint cookie
	 * this needed to be called before any writing to the session can happen
	 * 
	 * @return string the session identifier 
	 */
	private static function create_session_cookie(){
		$database=new Database();

		// make up to 20 attempts to get a unique ID
		$failsafe = 20;
		$randnum = 0;
		
		// while we still have attempts left...
		while ( $failsafe -- ) {
			// create unique id
			$randnum = md5 ( uniqid ( microtime (), 1 ) );
			
			// ensure that it is not a blank string
			if ($randnum != "") {
				// determine whether this unique ID is in use
                                $database->setQuery ( "SELECT session_id FROM session WHERE session_id=MD5('$randnum')");
				$result = $database->query();

				// ensure query was carried out
				if (! $result) {
                                    Session::ensure_session_table_exists($database);
                                    break;
				}

				// if there are no matching rows, our uniqueId is indeed unique
                                if ($database->getNumRows ( $result ) == 0) {
                                    break;
				}
                                
			}
		}
		
		$lifetime = time () + 365 * 24 * 60 * 60; // 1  year
		if (!setcookie ( Session::COOKIE_NAME, $randnum, $lifetime, Session::COOKIE_PATH )){
			//however if this code is running on the CLI then it will fail
			if (!defined("_RUNNING_CLI_MODE")){
				//cookies cannot be set in CLI Mode
				handle_error("cannot set the cookie");
				return null;
			}
		}
		
		return $randnum;		
	}

	/**
	 * initalises the session 
	 * by determining if there is a client cookie, if there is it will 
	 * use it if not it will create it. It will also ensure that there is a row 
	 * in the database for the cookie
	 * 
	 * @return string the session identifier
	 */
	public static function start(){

		if (Session::$session_id!= NULL){
			return Session::$session_id;
		}
		
		//is there a cookie?
		Session::$session_id = $_COOKIE[Session::COOKIE_NAME];
		
		//if the cookie does not exist create it
		if (!Session::$session_id){
			Session::$session_id = Session::create_session_cookie();
		}
		
		// dose the session exist in the database
		$database = new Database( );
		$database->setQuery ( "SELECT session_id FROM session WHERE session_id='" . Session::$session_id . "'" );
		$expiration = "DATE_ADD(now(),INTERVAL " . (_DEFAULT_TIMEOUT) . " MINUTE)";
		if ($database->loadObject ( $row )) {
			// yes session exists, 
			// update time in session table
			$database->setQuery ( "UPDATE session SET last_activity=now(), expire=$expiration WHERE session_id='" . Session::$session_id . "'" );
		} 
		else {
			//no data has been found or the database has errored
			Session::ensure_session_table_exists($database);

			//add the session row
			$database->setQuery ( "INSERT INTO session SET last_activity=now(), session_id='" . Session::$session_id . "', expire=$expiration " );
		}
		if (!$database->query ()){
			handle_error('unexpected database query failed ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg() , E_USER_NOTICE);
		}
		
		return Session::$session_id;
	}
	
	/**
	 * writes a string value to the session
	 * puts a string value into the session against 
	 * the passed in key, will overwrite if the key already exists
	 * 
	 * @param string key the key to store the vale against 
	 * @param string value to be stored
	 * @return boolean status
	 */
	public static function put ($key, $value){		
		if (Session::$cache[$key]==$value){
			return true;
		}

		Session::start();
		$session_id = Session::$session_id;
		
		//store the value and key in the database against the session id
		$database = new Database( );
		$database->setQuery ( "DELETE FROM session_data WHERE session_id = '$session_id' AND data_key='$key'" );
		if (!$database->query ()){
			handle_error('unexpected database query failure ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg());
			return false;
		}
		
		//process the value for storage in the database
		//$serialised_value = ;
		$serialised_value = $database->getEscaped(serialize($value));
		
		//store the value into the database
		$database->setQuery ( "INSERT INTO session_data (session_id, data_key, data_value) VALUES ('$session_id','$key','$serialised_value')" );
		if(!$database->query ()){
			handle_error('session database error ' . $database->getErrorNum() . " error message :" . $database->getErrorMsg());
			return false;
		}

		Session::$cache[$key] = $value;
		
		return true;
	}

	/**
	 * returns a value from the session
	 * will get the bvalue stored against the key and 
	 * return it from the currenty session
	 * 
	 * @param string key the key to store the vale against 
	 * @return string the value or NULL
	 */
	public static function get ($key){
		if (Session::$cache[$key]!=NULL){
			return Session::$cache[$key];
		}

		Session::start();

                //debug("after session start");
                //exit;

		$session_id = Session::$session_id;
		
		$database = new Database( );
		$sql = "SELECT data_value FROM session_data WHERE data_key='$key' AND session_id='$session_id'";
		$database->setQuery ( $sql );

		if ($database->loadObject ( $row )) {
			
			$serialised_data = $row->data_value;
			
			$obj = unserialize($serialised_data);
			
			Session::$cache[$key] = $obj;
			return Session::$cache[$key];
		}
		else{
			return NULL;
		}
	}		

	/**
	 * deletes a key value pair 
	 * 
	 * @param string key the key to delete
	 * @return boolean the success of the call
	 */
	public static function delete ($key){
		if (Session::$cache[$key]!=NULL){
			Session::$cache[$key] = NULL;
		}
		Session::start();

		$session_id = Session::$session_id;
		
		$database = new Database();
		$sql = "DELETE FROM session_data WHERE data_key='$key' AND session_id='$session_id'";
		$database->setQuery ( $sql );

		return $database->query();
	}		
	
	public static function kill_all_sessions(){
		$database = new Database( );
		$sql = "DELETE FROM session; DELETE FROM session_data;";
		$database->setQuery ( $sql );
		return $database->query_batch();
	}
}
?>