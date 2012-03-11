<?php

class UserTest extends PHPUnit_Framework_TestCase {

    private $_fixture_valid_user;

    function setUp(){
        $this->_fixture_valid_user = array(
                "id"=> null,
                "username"=> "ValidFirstName",
                "password"=> "ValidPassword",
                "email"=> "valid@email.com",
                "type"=> null );
    }
 
    public function test_fixture_is_valid(){
            $valid_user = new User($this->_fixture_valid_user);
            $res = $valid_user->validate();
            $this->assertTrue($res != 0, "cannot validate user=" . json_encode($valid_user));

    }

    public function test_save_a_user(){
            $valid_user = new User($this->_fixture_valid_user);
            $res = $valid_user->save();
            $this->assertTrue($res, "the result of user->save is $res");
            $this->assertNotNull($valid_user->id);
    }

    public function test_delete_a_user(){
            $valid_user = new User($this->_fixture_valid_user);
            $user = User::find_by_username_and_password(
                    array("username" => $valid_user->username, "password" =>  $valid_user->password )
                    );
    	$this->assertTrue($user->delete());
    }
	
    public function test_get_current_user_not_logged_in(){
            $user = User::get_current_user();
            $this->assertNull($user);
    }
	
    public function test_get_current_user(){
        //fake a login
        $user = User::find_by_username_and_password(array("username" => "testing", "password" => "password"));

        $this->assertEquals($user->username, "testing"); // the testing user
        $this->assertTrue(Session::put("user",$user));

        $logged_in_user = Session::get("user");

        $this->assertEquals($user->username, $logged_in_user->username); // the testing user

        $user = User::get_current_user();
        $this->assertNotNull($user);
    }
	
}
?>