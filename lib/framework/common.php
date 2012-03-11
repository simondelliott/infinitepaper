<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once ( dirname(__FILE__) . "/../../config.php");
require_once ( dirname(__FILE__) . "/Location.php");

define ("_DEFAULT_TIMEOUT",             28800); //Session timeout minutes
define ("_SESSION_NAME", 		APPLICATION_NAME);

$format = "html";
if($_REQUEST["format"]){
    $format = $_REQUEST["format"];
}
define ("FORMAT", $format);

$routes = array();

$locations = array();
$locations[] = new Location("lib");
$locations[] = new Location("lib/framework", true);
$locations[] = new Location("lib/framework/tests");
$locations[] = new Location("lib/framework/tests/unit");

$locations[] = new Location("app");
$locations[] = new Location("app/helper", true);
$locations[] = new Location("app/controler", true);
$locations[] = new Location("app/model", true);
$locations[] = new Location("app/view", true);
$locations[] = new Location("app/jobs", true);

$locations[] = new Location("");
$locations[] = new Location("content");
$locations[] = new Location("database");
$locations[] = new Location("doc");
$locations[] = new Location("logs");
$locations[] = new Location("scripts");
$locations[] = new Location("style");
$locations[] = new Location("images");
$locations[] = new Location("tests");
$locations[] = new Location("tests/test_data");
$locations[] = new Location("tests/unit");
$GLOBALS["locations"] = $locations;


function getParam(&$arr, $name, $def = null) {
    if (isset ( $arr [$name] )) {
            return $arr [$name];
    } else {
            return $def;
    }
}

function autoload_framework_classes ($class_name){
    $file_name =  $class_name . '.php';
    $start_path = dirname(__FILE__) . "/../..";

    global $GLOBALS;

    foreach ($GLOBALS["locations"] as $loc){
        if ($loc->autoload){
            $file = $start_path . "/" . $loc->path . "/" . $file_name;
            if(file_exists($file)){
                require_once ( $file );
                return true;
            }
        }
    }

    handle_error("The requested library, <b>$class_name</b>, could not be found.<br/>\n\t\tlooking in <em>". $_SERVER["DOCUMENT_ROOT"] ."</em>");
}
 spl_autoload_register('autoload_framework_classes');

 Session::start();

/**
* Standard autoload function pointed to the model 
*/
/*function __autoload($class_name) {

    $file_name =  $class_name . '.php';
    $start_path = dirname(__FILE__) . "/../..";

    global $GLOBALS;

    foreach ($GLOBALS["locations"] as $loc){
        if ($loc->autoload){
            $file = $start_path . "/" . $loc->path . "/" . $file_name;
            if(file_exists($file)){
                require_once ( $file );
                return true;
            }
        }
    }

    handle_error("The requested library, <b>$class_name</b>, could not be found.<br/>\n\t\tlooking in <em>". $_SERVER["DOCUMENT_ROOT"] ."</em>");
}
*/

/**
 * returns the list of filedls for the passed in object type based on the properties 
 * comer seperated, redy for use in a SQL call
 * 
 * @return string the list of fields
 */
function get_field_list($class){
	$ret = "";
	$properties = get_class_vars($class);
	$table_name = strtolower($class);
	
	foreach ($properties as $key => $value){
		if ($key[0] == '_') { // internal field
			continue;
		}

		if(strlen($ret)>0){
			$ret .= ",";
		}
		//$ret .= "$class." . $key . " '$key'";
		//$ret .= "$class." . $key ;
		
		$ret .= "$table_name." . $key ;
		
	}
	return $ret;
}

/**
 * takes a model object class name and returns it as a SQL table name 
 *
 * @param mixed $class
 * @return String the table name for use in SQL statements
 */
function get_table_name($class){
    return strtolower($class);
}

/**
 * Gets an instance of a model object, that matches the passed in criterion 
 *
 * @param FoundationModel $class the class that the instance is required for
 * @param array $where_conditions the selection criteria
 * @param array $order_by_conditions the order by criteria TODO:// is this needed?
 * @param Integer $limit_to_condition the number of results returned TODO:// is this needed?
 * @return mixed the instance
 */
function get_instance_for_model_object($class, $where_conditions =null, $order_by_conditions = null, $limit_to_condition = null ){
	
    $res = get_result_set_for_model_object($class, $where_conditions, $order_by_conditions, $limit_to_condition);

    if(count($res)){
            return array_pop($res);
    }
    else{
            return null;
    }
}

/**
 * gets an array of model objects, that sastisfy the passed in filtering conditions.
 * Its uses the id field in the database to key the returned array
 *
 * @param FoundationModel $class the class that the instance is required for
 * @param array $where_conditions the selection criteria
 * @param array $order_by_conditions the order by criteria 
 * @param Integer $limit_to_condition the number of results returned 
 * @return mixed array of model objects keyed by the id field in the database
 */
function get_result_set_for_model_object($class, $where_conditions =null, $order_by_conditions = null, $limit_to_condition = null ){

	$where = "";
	if ($where_conditions != null){
		$where = "WHERE ";
		$x = array();
		foreach ($where_conditions as $k=>$v) {
			$operator = "=";
			$value = $v;
			if (get_class($v) == "QueryCriterion"){
				$operator = $v->operator;
				$value = $v->value;
			}
			else{
				$value = "'$v'";
			}
			$x[] = $k . " $operator " . $value ."";
		}
		$where .=  implode(" AND ",$x);
	}
	
	$order_by = "";
	if ($order_by_conditions != null){
		$x = array();
		foreach ($order_by_conditions as $k=>$v) {
			$x[] = $k . " " . strtoupper($v);
		}
		$order_by = "ORDER BY " . implode(", ", $x);
	}
	
	$limit_to = "";
	if ($limit_to_condition != null){
		$limit_to = "LIMIT " . $limit_to_condition;
	}
	$SQL = "SELECT " . get_field_list($class) . " FROM " . get_table_name($class). " $where $order_by $limit_to";

	$database=new database(_DB_SERVER, _DB_USER, _DB_PASS, _DB_NAME, '');
	$row = null;
	
	$database->setQuery ( $SQL);
	if (!$database->query()){
		if ($database->getErrorNum() == Database::ERROR_NUM_TABLE_DOES_NOT_EXIST){
			if(!$database->schema_version_okay()){
                            $msg = "Table not found, database schema version is " . Setting::get("db_schema_version") . "\n";
                            $msg .= $database->getPrettyErrorMessage();
                            handle_error($msg);
			}
		}
		handle_error($database->getPrettyErrorMessage(), E_USER_NOTICE );	
	}
	$row = null;
	$rows = $database->loadRowList();
	if ($rows ==NULL){
		return array();
	}
	$counter = 1;
	$ret = array();
	foreach ($rows as $row) {
            eval('$ret[$row[\'id\']]=new ' . $class . '($row);');
	}
	if (count($ret) == 0){
		return array();	
	}
	unset($database);

	return($ret);
}

/**
 * debugging function
 * top and tails a message with html div block 
 *
 * @param string $message the message to be displayed
 */
function debug($message){
    if (_RUNNING_CLI_MODE){
        echo "$message\n";
    }
    else{
        echo "<div class='debug_row'>\n\t$message\n</div>\n";
    }
}

/**
 * converts an array of model objects to its representation in JSON
 *
 * @param String $collection_name the name of the collection as it will appear in the outputted JSON
 * @param array $model_collection the array of FoundationModel objects
 * @return String  the JSON
 */
function model_object_collection_to_JSON ($collection_name, $model_collection ){
	
	$json = "{ \"$collection_name\":[\n";
	foreach ($model_collection as $model_obj){
		$json .= "\t" . $model_obj->toJSON();
		$json .= ", \n";
	}
	if(strlen($json)>1){
		$json = substr($json, 0,strlen($json) -3); //remove the comma and the space 
		$json .= "\n";
		
	}
	$json .= "]}";
	
	return $json;
}	

/**
 * utility function used to take the ", " off of a string.
 * used when building JSON in a loop
 *
 * @param String $json_to_strip
 * @return String
 */
function strip_json_final_comma($json_to_strip){
	$ret = "";
	if(strlen($json_to_strip)>1){
		
		$ret = substr($json_to_strip, 0,strlen($json_to_strip) -3); //remove the comma and the space 
		$ret .= "\n";
	}
	$ret .= "}";
	return $ret;
}

/**
 * Error handeling routine, will raise the error and pint out a web friendly
 * message for the user 
 *
 * @param String $message optional custom message to go along with the error  
 * @param Integer $error_notice determines the error reporting behaviour
 */
function handle_error($message = "", $error_notice = E_USER_ERROR){
    $msg = "\n\n<div class='error'>\n";
    $msg .= "\t<div class='error_row'>\n\t\t<b>[ERROR]</b>" . $message . "\n\t</div>\n";
	
    $raw = debug_backtrace();
    foreach($raw as $entry){
    	$file = substr($entry['file'], strlen($_SERVER["DOCUMENT_ROOT"]),200);
    	$line = $entry['line'];
    	
    	$function = $entry['function'];
    	//"(" . implode(", ", $entry['args']) .
    	$msg .= "\t<div class='error_row'>" . $file  . ": <b>" . $line . "</b>: " . $function . ")</div>\n";
    }
    
    $msg .= "</div>\n";
    trigger_error( $msg, $error_notice);
}

/**
 * Will create a new file of the specified dimensions.
 * Only works on jpegs
 *
 * @param String $source_file_name the name of the file to copy
 * @param String $target_file_name the new name for the destination file
 * @param Integer $new_width the new width +ve
 * @param Integer $new_height The new height +ve
 */
function resize_image( $source_file_name, $target_file_name, $new_width, $new_height ){
	
	// Create an Image from it so we can do the resize
	$src = imagecreatefromjpeg($source_file_name);
	
	// Capture the original size of the uploaded image
	list($width,$height) = getimagesize( $source_file_name );
	
	$tmp = imagecreatetruecolor( $new_width, $new_height );
	imagecopyresampled( $tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	
	// now write the resized image to disk. I have assumed that you want the
	// resized, uploaded image file to reside in the ./images subdirectory.
	//$filename = "images/". $_FILES['uploadfile']['name'];
	imagejpeg( $tmp, $target_file_name, 100 );
	
	imagedestroy($src);
	imagedestroy($tmp); // NOTE: PHP will clean up the temp file it created when the request		
}

/**
 * convert a number in to a human readable rank,
 * for ecample 121 would be 121st and 122 will be 122nd etc ...
 * @param $rank Number the position in the ranking
 * @return string
 */
function number_to_rank($rank){
	if ($rank == null || $rank==0 )
		return "unranked";
	else {
		switch ($rank % 10) {
	    case 1:
	        return $rank . "st";
	        break;
	    case 2:
	        return $rank . "nd";
	    	break;
	    case 3:
	        return $rank . "rd";
	    	break;
	    default:
	        return $rank . "th";
	    	break;
	    }		
	}
}

function number_to_currency_string($num){
	$b = $num;
	$o = "";
	
	
	while(strlen($b) > 3){
		//take right 3 characters
		$o = "," . substr($b,strlen($b)-3,strlen($b)) . $o;
		$b = substr($b,0,strlen($b)-3);
	}
	
	$o = "$" . $b . $o;
	return $o;	
}

/**
 * Used in the leaderboard to get rank from page.
 * It will get the starting rank, as in the the 
 * ranking stored in the data base from the page of the leader board that 
 * you want top display.
 * 
 * @param integer $page
 * @param integer $number_of_rows_per_page
 * @return integer the starting rank
 */
function get_start_rank_from_page($page, $number_of_rows_per_page){
	if ($page > 0){
		$start_rank = (($page - 1) * $number_of_rows_per_page) + 1;
	}
	else{
		$start_rank = 1; //note that admin user is rank 0 - so are un ranked films
	}
	return $start_rank;
}

function truncate($string, $limit) {
	if(strlen($string) <= $limit) return $string; 
}

function set_xml_header(){
    header ("Content-Type:text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
}

function output_array_as_xml($obj, $root_node){
    set_xml_header();
    echo("<$root_node>");

    foreach ($obj as $e ){
        echo $e->toXML();
    }
    echo("</" . $root_node . ">");
}

function base64_png_dataURI_decode($data){

  //The data should start "data:image/png;base64,"
  $d = str_replace("data:image/png;base64,", "", $data);
  $d = str_replace(' ','+',$d);
  return base64_decode($d);
}


?>
