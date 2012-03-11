<?php


class FrameworkUsageTest extends PHPUnit_Framework_TestCase {


    function setUp(){
    }
 
    public function test_schema_version(){
        $this->assertEquals(Setting::get("db_schema_version"), DATABASE_SCHEMA_VERSION);
    }

}
?>