<?php
require_once ( dirname(__FILE__) . "/../../config.php");
require_once ( 'common.php');

class Database {
	
    const ERROR_NUM_TABLE_DOES_NOT_EXIST = 1146;
	
    /** @private string Internal variable to hold the query sql */
    private $_sql='';
    /** @private int Internal variable to hold the database error number */
    private $_errorNum=0;
    /** @private string Internal variable to hold the database error message */
    private $_errorMsg='';
    /** @var string Internal variable to hold the prefix used on all database tables */
    private $_table_prefix='';
    /** @private Internal variable to hold the connector resource */
    private $_resource='';
    /** @private Internal variable to hold the last query cursor */
    private $_cursor=null;
    /** @private boolean Debug option */
    private $_debug=0;
    /** @private int A counter for the number of queries performed by the object instance */
    private $_ticker=0;
    /** @private array A log of queries */
    private $_log=null;

    public function Database( ) {

        // perform a number of fatality checks, then die gracefully
        if (!function_exists( 'mysql_connect' )) {
            handle_error("cannot find mysql_connect_function");
            die( 'FATAL ERROR: MySQL support not available.  Please check your configuration.' );
        }

        if (!($this->_resource = @mysql_connect( DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD ))) {
            die( 'FATAL ERROR: Connection to database server failed. ' . mysql_error()  );
        }

        if (!mysql_select_db(DATABASE_NAME)) {
            die( "FATAL ERROR: Database not found. Operation failed with error: ".mysql_error());
        }

        $this->_table_prefix = $table_prefix;
        $this->_ticker = 0;
        $this->_log = array();
    }
	
    function debug( $level ) {
        $this->_debug = intval( $level );
    }

    function getErrorNum() {
        return $this->_errorNum;
    }
	
	/**
	* @return string The error message for the most recent query
	*/
	function getErrorMsg() {
		return str_replace( array( "\n", "'" ), array( '\n', "\'" ), $this->_errorMsg );
	}
	
	/**
	* @return string The error number and message for use when presenting to th user
	*/
	public function getPrettyErrorMessage(){
		return "<b>[Database]</b>:" . $this->getErrorNum() . ":" . $this->getErrorMsg();
	}
	
	/**
	* Get a database escaped string
	* @return string
	*/
	public function getEscaped( $text ) {

		try {
			$value = mysql_real_escape_string( $text );
		} 
		catch (Exception $e) {
    		handle_error('cannot escape string ');
			return false;	
		}
		return $value;
	}
	/**
	* Get a quoted database escaped string
	* @return string
	*/
	function Quote( $text ) {
		return '\'' . getEscaped( $text ) . '\'';
	}

    /**
    * Sets the SQL query string for later execution.
    *
    * This function replaces a string identifier <var>$prefix</var> with the
    * string held is the <var>_table_prefix</var> class variable.
    *
    * @param string The SQL query
    * @param string The common table prefix
    */
    function setQuery( $sql, $prefix='#__' ) {
        $sql = trim( $sql );

        $inQuote = false;
        $escaped = false;
        $quoteChar = '';

        $n = strlen( $sql );
        $np = strlen( $prefix );
        $literal = '';

        for ($j=0; $j < $n; $j++ ) {
            $c = $sql{$j};
            $test = substr( $sql, $j, $np );

            # If not already in a string, look for the start of one.
            if (!$inQuote) {
                if ($c == '"' || $c == "'") {
                        $inQuote = true;
                        $escaped = false;
                        $quoteChar = $c;
                }
            } else {
                    # Already in a string, look for end and copy characters.
                    if ($c == $quoteChar && !$escaped) {
                            $inQuote = false;
                    } else if ($c == "\\" && !$escaped) {
                            $escaped = true;
                    } else {
                            $escaped = false;
                    }
            }
            if ($test == $prefix && !$inQuote) {
                $literal .= $this->_table_prefix;
                $j += $np-1;
            } else {
                    $literal .= $c;
            }
        }
        $this->_sql = $literal;
    }
        
    /**
    * @return string The current value of the internal SQL vairable
    */
    function getQuery() {
            return "<pre>" . htmlspecialchars( $this->_sql ) . "</pre>";
    }

    /**
    * Execute the query
    * @return mixed A database resource if successful, FALSE if not.
    */
    function query() {
        $this->_errorNum = 0;
        $this->_errorMsg = '';
        $this->_cursor = mysql_query( $this->_sql, $this->_resource );
        if (!$this->_cursor) {
            $this->_errorNum = mysql_errno( $this->_resource );
            $this->_errorMsg = mysql_error( $this->_resource )." SQL=$this->_sql";
            if ($this->_debug) {
                handle_error(mysql_error( $this->_resource ), E_USER_NOTICE);
            }
            return false;
        }
        return $this->_cursor;
    }

	/**
	* Executes a batch of queries within a single transaction
	* 
	* @return the error state
	*/
	function query_batch( $abort_on_error=true, $p_transaction_safe = false) {
            $this->_errorNum = 0;
            $this->_errorMsg = '';
            if ($p_transaction_safe) {
                    $si = mysql_get_server_info();
                    preg_match_all( "/(\d+)\.(\d+)\.(\d+)/i", $si, $m );
                    if ($m[1] >= 4) {
                            $this->_sql = 'START TRANSACTION;' . $this->_sql . '; COMMIT;';
                    } else if ($m[2] >= 23 && $m[3] >= 19) {
                            $this->_sql = 'BEGIN WORK;' . $this->_sql . '; COMMIT;';
                    } else if ($m[2] >= 23 && $m[3] >= 17) {
                            $this->_sql = 'BEGIN;' . $this->_sql . '; COMMIT;';
                    }
            }

            $query_split = preg_split ("/[;]+/", $this->_sql);
            $error = 0;
            $counter = 0;
            foreach ($query_split as $command_line) {
                $counter++;
                $command_line = trim( $command_line );
                if ($command_line != '') {
                        $this->_cursor = mysql_query( $command_line, $this->_resource );
                        if (!$this->_cursor) {
                                $error = 1;
                                $this->_errorNum .= mysql_errno( $this->_resource ) . ' ';
                                $this->_errorMsg .= mysql_error( $this->_resource )." SQL=$command_line <br />";

                                if ($abort_on_error) {
                                        handle_error($this->getPrettyErrorMessage());
                                        return $this->_cursor;
                                }
                        }
                }
            }
            return $error ? false : true;
	}

	/**
	* Diagnostic function
	*/
	function explain() {
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";
		$this->query();

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buf = "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\">";
		$buf .= $this->getQuery();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($first) {
				$buf .= "<tr>";
				foreach ($row as $k=>$v) {
					$buf .= "<th bgcolor=\"#ffffff\">$k</th>";
				}
				$buf .= "</tr>";
				$first = false;
			}
			$buf .= "<tr>";
			foreach ($row as $k=>$v) {
				$buf .= "<td bgcolor=\"#ffffff\">$v</td>";
			}
			$buf .= "</tr>";
		}
		$buf .= "</table><br />&nbsp;";
		mysql_free_result( $cur );

		$this->_sql = $temp;

		return "<div style=\"background-color:#FFFFCC\" align=\"left\">$buf</div>";
	}

	/**
	* @return int The number of rows returned from the most recent query.
	*/
	function getNumRows( $cur=null ) {
		return mysql_num_rows( $cur ? $cur : $this->_cursor );
	}

	/**
	* This method loads the first field of the first row returned by the query.
	*
	* @return The value returned in the query or null if the query failed.
	*/
	function loadResult() {
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysql_free_result( $cur );
		return $ret;
	}
	/**
	* Load an array of single field results into an array
	*/
	function loadResultArray($numinarray = 0) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result( $cur );
		return $array;
	}
	/**
	* Load a assoc list of database rows
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadAssocList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	
	/**
	* This global function loads the first row of a query into an object
	*
	* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	* If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	* @param string The SQL query
	* @param object The address of variable
	*/
	function loadObject( &$object ) {
		if ($object != null) {
			if (!($cur = $this->query())) {
				return false;
			}
			if ($array = mysql_fetch_assoc( $cur )) {
				mysql_free_result( $cur );
				mosBindArrayToObject( $array, $object, null, null, false );
				return true;
			} else {
				return false;
			}
		} else {
			if ($cur = $this->query()) {
				if ($object = mysql_fetch_object( $cur )) {
					mysql_free_result( $cur );
					return true;
				} else {
					$object = null;
					return false;
				}
			} else {
				return false;
			}
		}
	}
	
	/**
	* Load a list of database objects
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadObjectList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	
	/**
	* @return The first row of the query.
	*/
	function loadRow() {
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row;
		}
		mysql_free_result( $cur );
		return $ret;
	}
	/**
	* Load a list of database rows (numeric column indexing)
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadRowList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_array( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	/**
	* Document::db_insertObject()
	*
	* { Description }
	*
	* @param [type] $keyName
	* @param [type] $verbose
	*/
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
		$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
		$fields = array();
		
		foreach (get_object_vars( $object ) as $k => $v) {

			if (is_array($v) or is_object($v)) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}

			$fields[] = "`$k`";
			$values[] = "'" . $this->getEscaped( $v ) . "'";
		}
		$sSQL = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) ;
		$this->setQuery($sSQL);
		($verbose) && print "$sql<br />\n";
		if (!$this->query()) {
			return false;
		}
		$id = mysql_insert_id();
		($verbose) && print "id=[$id]<br />\n";
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	* Document::db_updateObject()
	*
	* { Description }
	*
	* @param [type] $updateNulls
	*/
	function updateObject( $table, &$object, $keyName, $updateNulls=true ) {
		$fmtsql = "UPDATE $table SET %s WHERE %s";
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
				continue;
			}
			if( $k == $keyName ) { // PK not to be updated
				$where = "$keyName='" . $this->getEscaped( $v ) . "'";
				continue;
			}
			if ($v === NULL && !$updateNulls) {
				continue;
			}
			if( $v == '' ) {
				$val = "''";
			} else {
				$val = "'" . $this->getEscaped( $v ) . "'";
			}
			$tmp[] = "`$k`=$val";
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $this->query();
	}

	/**
	* @param boolean If TRUE, displays the last SQL statement sent to the database
	* @return string A standised error message
	*/
	function stderr( $showSQL = false ) {
		return "DB function failed with error number $this->_errorNum"
		."<br /><font color=\"red\">$this->_errorMsg</font>"
		.($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
	}

	function show_error($class){
		$msg = $this->stderr(true);
		debug("[ERROR]");
		debug("[class]$class");
		debug("[database error number]" . $this->_errorNum);
		debug("[database error messsage]" . $this->_errorMsg);
		debug("[last sql]" . $this->_sql);
	}
		
	function insertid()
	{
		return mysql_insert_id();
	}

	function getVersion()
	{
		return mysql_get_server_info();
	}

	/**
	* Fudge method for ADOdb compatibility
	*/
	function GenID( $foo1=null, $foo2=null ) {
		return '0';
	}
	/**
	* @return array A list of all the tables in the database
	*/
	function getTableList() {
		$this->setQuery( 'SHOW tables' );
		$this->query();
		return $this->loadResultArray();
	}
	/**
	* @param array A list of table names
	* @return array A list the create SQL for the tables
	*/
	function getTableCreate( $tables ) {
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW CREATE table ' . $tblval );
			$this->query();
			$result[$tblval] = $this->loadResultArray( 1 );
		}

		return $result;
	}
	/**
	* @param array A list of table names
	* @return array An array of fields by table
	*/
	function getTableFields( $tables ) {
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
			$this->query();
			$fields = $this->loadObjectList();
			foreach ($fields as $field) {
				$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
			}
		}

		return $result;
	}


	public function schema_version_okay(){
            return (Setting::get("db_schema_version") == DATABASE_SCHEMA_VERSION);
	}
	
	/**
	 * upgrades the database to the required schema
	 * @return boolean the success of the opetation
	 */
	public function execute_db_install_step($schema_to_install){
            $file_name = dirname(__FILE__) . "/../../database/upgrade_to_$schema_to_install.sql";
            return $this->execute_db_file($file_name, $schema_to_install);
	}

	public function execute_db_uninstall_step($schema_to_install){
            $file_name = dirname(__FILE__) . "/../../database/downgrade_to_$schema_to_install.sql";
            return $this->execute_db_file($file_name, $schema_to_install);
	}

	/**
	 * This function will execute as a query batch the SQL contained in an
	 * upgrade or down grade file. These files are found in the /db folder
	 * @param $file_name String the absolute path to the file to execute
	 * @param $new_version the version number that the databse schema will be if the file exwecutes correctly.
	 * @return Boolean the success status
	 */
	private function execute_db_file($file_name, $new_version){
                if (!file_exists($file_name)){
                   return false;
                }

                $file = fopen( $file_name,"r+");
                $size = filesize($file_name);
		if ($size == 0){
        	    Setting::put("db_schema_version", $new_version );
                    return true;
		}
		$SQL = fread($file,$size);
		$this->setQuery ( $SQL );
                if ($this->query_batch()){
                    Setting::put("db_schema_version", $new_version );
                    return true;
                }
		else{
                    handle_error($this->getPrettyErrorMessage() . " cannot execute $SQL");
                    return false;
		}
	}

	public function upgrade_to_db_schema_version(){
		debug("upgrade to db schema version ");

		$schema_version = Setting::get("db_schema_version");
		if ($schema_version==NULL){
                    $schema_to_install = 0;
		}
		else{
                    $schema_to_install = $schema_version + 1;
		}

		while ($schema_to_install<=DATABASE_SCHEMA_VERSION){
                    $this->execute_db_install_step($schema_to_install);
                    $schema_to_install = Setting::get("db_schema_version") + 1;
		}
	}

	/**
	 * Determins from the settings if the database has been installed corerctly.
	 *
	 * @return Boolean true if the database is installed to the correct version
	 */
	public static function database_installed(){
            if (Setting::get("db_schema_version")==NULL){
                    return false;
            }
            if (Setting::get("db_schema_version")!= DATABASE_SCHEMA_VERSION){
                    return false;
            }
            return true;
	}




}

?>
