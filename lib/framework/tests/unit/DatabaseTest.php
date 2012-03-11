<?php


class DatabaseTest extends PHPUnit_Framework_TestCase {


    function setUp(){
    }

    public function test_getVersion(){
        $db = new Database();
        $version = $db->getVersion();
        $this->assertNotNull($version);
    }

}
?>