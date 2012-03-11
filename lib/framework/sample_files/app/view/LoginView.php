<?php

class LoginView extends ApplicationView {

	private $username = "";
	private $message = "";
	private $password = "";
	
	/**
	* @param map representing the user that is to be shown
	*/
	public function __construct( $user_to_show = NULL ) {
				
		if ($user_to_show && $user_to_show->username != NULL ){
			$this->username = $user_to_show->username;
			$this->password = $user_to_show->password;
			$this->message = "Invalid user name or password combination";
		}
		else {
			$this->username = "";
			$this->message = "";
			$this->password = "";
		}
	}
		
	public function contents (){
?>
	<h1>Log in </h1>

	<form action="/user/login" method="post">
		<fieldset>
			<legend>Enter your login details</legend>
			<div class="form_row" style="color: yellow">
				<?php echo $this->message ?>
			</div>
			<div class="form_row">
				<label for="username">username:</label>
				<input type="text"  
					id="username" 
					maxlength="<?php echo User::USERNAME_MAX_LENGTH ?>" 
					size="<?php echo User::USERNAME_SIZE ?>"  
					name="user[username]" 
					value="<?php echo $this->username ?>" />
			</div>
			<div class="form_row">
				<label for="password">password:</label>
				<input type="password" 
					maxlength="<?php echo User::PASSWORD_MAX_LENGTH ?>" 
					value="<?php echo $this->password ?>" 
					name="user[password]" 
					size="<?php echo User::PASSWORD_SIZE ?>" 
					 />
			</div>
			<div class="form_row">
				<input class="submit" name="commit" type="submit" value="Login" />
			</div>
		</fieldset>
	</form>
<?php
	}	
}
?>
