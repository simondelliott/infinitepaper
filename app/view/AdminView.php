<?php

class AdminView extends ApplicationView {

	private $user;
	
	public function __construct( $user_to_show = NULL ) {
		$this->user = $user_to_show;
	}	
	
	public function contents (){
?>
        <h1>Welcome Administrator <?php echo $this->user->username ?></h1>
<?php
	}	
}
?>
