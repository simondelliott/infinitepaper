<?php

class PaperTest extends PHPUnit_Framework_TestCase {

    private $_fixture_valid_Paper;

    function setUp(){
        $this->_fixture_valid_Paper = array(
                "id"=> null);
    }
 
    public function test_fixture_is_valid(){
        $valid_Paper = new Paper($this->_fixture_valid_Paper);
        $res = $valid_Paper->validate();
        $this->assertTrue($res != 0, "cannot validate Paper");
    }
	
}
?>