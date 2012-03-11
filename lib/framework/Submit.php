<?php

class Submit {

    public function __construct($name, $value){
?>
    <div id="<?php echo $name ?>_submit" class="form_row" >
        <input id="<?php echo $name ?>"
                type="submit"
                name="<?php echo $name ?>"
                value="<?php echo $value ?>"
    </div>
<?php		
	}
}
?>