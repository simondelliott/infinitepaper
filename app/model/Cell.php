<?php

class Cell extends FoundationModel {

    public $id;
    public $xcord;
    public $ycord;

    public static function find_by_xcord_and_ycord($x,$y){
        return get_result_set_for_model_object(__CLASS__, array(
		"xcord" => $x,
                "ycord" =>  $y));
    }

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

    /**
     * get a temporary files path
     * @return string 
     */
    public function get_temp_file_path(){
      return dirname(__FILE__) . "/../../content/tempfile.png";
    }

    /**
     * get the path to the cell
     * @return string 
     */
    public function get_cell_file_path(){
      return dirname(__FILE__) . "/../../content/cell_" . $this->xcord . "_" . $this->ycord . ".png";
    }

    public function save_picture($stroke_data){

      if (strlen($stroke_data)==0)
        handle_error("no stroke data");

      $fp = fopen($this->get_temp_file_path(), 'w');
      if (!$fp)
        handle_error("Cannot save picture data");

      // create a temporary file from the data that is passed in
      $bytes_written = fwrite($fp, base64_png_dataURI_decode($stroke_data));
      fclose($fp);

      // get png image handels from the temp file and the new cell 
      $source = imagecreatefrompng($this->get_temp_file_path());
 
      $destination = imagecreatefrompng($this->get_cell_file_path());
      imagealphablending($destination, true);
      imagesavealpha($destination, true);
     
      
      // copy image dest < source
      imagecopy($destination, $source, 0, 0, 0, 0, 600, 300);
      
      //outputs the file to disk
      $res = imagepng($destination, $this->get_cell_file_path());
      
      imagedestroy($source);
      imagedestroy($destination);

      
      
/*      $image_1 = imagecreatefrompng($this->get_temp_file_path());
      $image_2 = imagecreatefrompng($this->get_cell_file_path());
      
      imagealphablending($image_1, true);
      imagesavealpha($image_1, true);
      
      imagecopy($image_1, $image_2, 0, 0, 0, 0, 600, 300);
      $res = imagepng($image_1, $this->get_cell_file_path());
      
      imagedestroy($image_1);
      imagedestroy($image_2);
*/
      return $res;


    }

}
?>
