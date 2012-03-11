<?php
require_once ( $_SERVER["DOCUMENT_ROOT"] . '/common.inc.php');

class TextArea {

	public function __construct($model_object, $field, $rows, $value, $cols, $validation_error ){
	
		$field_name = $model_object . "_" . $field;
		$pretty_field_name = str_replace("_", " ", $field);
?>
		<div id="<?php echo $field_name ?>_container" class="form_row" >
			<label for="<?php echo $field_name ?>"><?php echo $pretty_field_name ?></label>
			<textarea 
				id="<?php echo $field_name ?>" 
				rows="<?php echo $rows ?>" 
				cols="<?php echo $cols ?>" 
				name="<?php echo $model_object ?>[<?php echo $field ?>]" ><?php echo $value ?></textarea>
			<?php echo $validation_error ?>
		</div>
<?php		
	}
}
?>