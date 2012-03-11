<?php

class Input {

	public function __construct($model_object, $field, $max_length, $value, $size, $validation_error ){
	
		$field_name = $model_object . "_" . $field;
		$pretty_field_name = str_replace("_", " ", $field);
?>
		<div id="<?php echo $field_name ?>_container" class="form_row" >
			<label for="<?php echo $field_name ?>"><?php echo $pretty_field_name ?></label>
			<input id="<?php echo $field_name ?>" 
				type="text" 
				name="<?php echo $model_object ?>[<?php echo $field?>]"
				maxlength="<?php echo $max_length ?>" 
				value="<?php echo $value ?>" 
				size="<?php echo $size ?>"/>
			<?php echo $validation_error ?>
		</div>
<?php		
	}
}
?>