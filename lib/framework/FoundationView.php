<?php

class FoundationView {
	
    public function get_validation_error_div($message){
        if(!$message){
                return;
        }
?>
        <div class="validation_error"><?php echo $message ?></div>
<?php
	}
}
?>