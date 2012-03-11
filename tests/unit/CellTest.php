<?php

class CellTest extends PHPUnit_Framework_TestCase {

    private $_fixture_valid_Cell;

    function setUp(){
        $this->_fixture_valid_Cell = array(
                "id"=> null);
    }
 
    public function test_fixture_is_valid(){
        $valid_Cell = new Cell($this->_fixture_valid_Cell);
        $res = $valid_Cell->validate();
        $this->assertTrue($res != 0, "cannot validate Cell");
    }


    public function test_image_combination(){
      $image_1 = imagecreatefrompng('./tests/test_data/image_1.png');
      $image_2 = imagecreatefrompng('./tests/test_data/image_2.png');
      imagealphablending($image_1, true);
      imagesavealpha($image_1, true);
      imagecopy($image_1, $image_2, 0, 0, 0, 0, 600, 300);
      imagepng($image_1, './tests/test_data/image_3.png');
    }

}
?>