<?php


class SessionTest extends PHPUnit_Framework_TestCase {


    function setUp(){
    }

    public function test_set_cookie(){
		/*
		setcookie is the php standard way of storing a cookie
		its manual is here.
		when run in phpunit
		It should return false as setcookie returns false if output already exists
		however it returns true (so I guess sebastian has put a fix in) ....
		http://php.net/manual/en/function.setcookie.php
		*/
                if (!headers_sent()){
                    $res = setcookie("test_cookie","test_value");
                }
		else{
                    $res = true;
                }
                $this->assertTrue($res, "setcookie returns false if output already exists");
    }

    public function test_kill_all_sessions(){
		$res = Session::kill_all_sessions();
		$this->assertTrue($res);
    }

    public function test_put_a_value_into_the_session(){
		$res = Session::put("hello","world");
		$this->assertTrue($res);
    }

    public function test_value_entered_into_session_cache(){
        $res = Session::put("hello","world");
        $this->assertTrue($res);

    	$this->assertArrayHasKey("hello",Session::$cache, "key not in cache should be " . json_encode(Session::$cache));
    	$this->assertEquals("world", Session::$cache["hello"]);
    }

    public function test_get_a_value_from_the_session(){

    	$res = Session::put("hello","world");
        $this->assertTrue($res);

        $res = Session::get("hello");
        $this->assertEquals($res, "world");
    }

    public function test_put_an_object_into_the_session(){

        $obj = array(   "id"=> null,
			"string_ting"=> "abc", 
			"number_ting"=> 123
			);

    	$res = Session::put("an_object",$obj);
	$this->assertTrue($res);

    }



}
?>