<?php

class User extends FoundationModel {

    const USERS_PER_PAGE = 20;

    const USERNAME_MAX_LENGTH = 200;
    const USERNAME_SIZE = 30;
    const USERNAME_MIN_LENGTH = 4;

    const EMAIL_MAX_LENGTH = 250;
    const EMAIL_MIN_LENGTH = 4;
    const EMAIL_SIZE = 30;

    const PASSWORD_MAX_LENGTH = 250;
    const PASSWORD_MIN_LENGTH = 4;
    const PASSWORD_SIZE = 30;

    public $id;
    public $username;
    public $email;
    private $confirmemail;
    public $password;
    public $type;
	
    public function is_administrator(){
        return $this->type == "admin";
    }

    public static function ensure_logged_in(){
        $user = Session::get("user");

        $user_id = $user->id;

        if ($user_id == NULL ){
                header("Location: /user/login");
                die();
        }
        return true;
    }

    public static function get_current_user(){
        $user = Session::get("user");
        $user_id = $user->id;
        return User::find_by_id(array(id => $user_id));
    }

    public static function find_by_username_and_password($obj){
        $username = $obj["username"];
        $password = $obj["password"];

        if ($username==null or $password == NULL){
                return null;
        }

        $obj = get_instance_for_model_object(__CLASS__, array("username" => $username, "password" => $password));

        return $obj;
    }

    public static function find_by_username($obj){
            $username = $obj["username"];

            if ($username==null){
                    return null;
            }

            $obj = get_instance_for_model_object(__CLASS__, array("username" => $username));
            return $obj;
    }

	public static function find_by_id($obj){
		$id = $obj["id"];
		
		if ($id==NULL){
			return NULL;
		}
		
		$obj = get_instance_for_model_object(__CLASS__, array("id" => $id));
		return $obj;
	}
	
	/**
	 * validates that the user is ok to be saved
	 * 
	 * @return boolean success status
	 */
	public function validate (){
		$ret = true;
		
		//clear the validation errors
		$this->_validate_errors = array();

		//username
 		$ret &= $this->validate_length_of( "username", array("less_than" => User::USERNAME_MAX_LENGTH) );
 		$ret &= $this->validate_length_of( "username", array("greater_than_or_equal_to" => User::USERNAME_MIN_LENGTH) );
	 	$ret &= $this->validate_pattern( "username","/^[A-Z0-9_]*$/i","user name must only contain letters and numbers");
 		$ret &= $this->validate_uniqueness_of( "username", "User name already taken");
		
		//email
		$ret &= $this->validate_length_of( "email", array("less_than" => User::EMAIL_MAX_LENGTH) );
		$ret &= $this->validate_length_of( "email", array("greater_than_or_equal_to" => User::EMAIL_MIN_LENGTH) );
		$ret &= $this->validate_pattern( "email","/^[A-Z0-9._%-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i","Must be a valid email address");		
		$ret &= $this->validate_same( "email","confirmemail",$this->get_confirmemail(),"Email addresses do not match");		
		$ret &= $this->validate_uniqueness_of( "email", "Email already taken");
		
		//password
		$ret &= $this->validate_length_of( "password", array("less_than" => User::PASSWORD_MAX_LENGTH) );
		$ret &= $this->validate_length_of( "password", array("greater_than_or_equal_to" => User::PASSWORD_MIN_LENGTH) );
		
		return $ret;
	}
	
	public function get_confirmemail() {
		return $this->confirmemail;
	}

	public function set_confirmemail($email) {
		$this->confirmemail = $email;
	}

	public static function get_all_users(){
            return get_result_set_for_model_object(__CLASS__);
	}	
    
}
?>
