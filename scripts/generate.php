<?php

require_once ( './lib/framework/cli_common.php');

function get_file_contents($file){
    $fh = fopen($file,"r+");
    return fread($fh,filesize($file));
}

function write_file($file, $contents){

     if (!file_exists($file)){
        echo " creating file $file\n";
        $fp = fopen($file, 'w');
        fwrite($fp, $contents);
        fclose($fp);
        return true;
    }
    else{
        echo "[ERROR] file already exists $file\n";
        return false;
    }
}

function generate_from_base($base_file, $target_file, $reps){

    $contents = get_file_contents($base_file);

    foreach ($reps as $replacement => $value)
        $contents = str_replace($replacement, $value, $contents);

    write_file($target_file,$contents);
}

//parse argeuments
if ($argc <= 1){
    echo "[ERROR]no model name passed in\n";
    echo "usage: scripts/generate.sh [new model name]\n";
    die();
}

//set up some globals
$framework_dir = "./lib/framework";

// define the new object that is being generated
$base_model = ucfirst($argv[1]);
$new_schema_version = (DATABASE_SCHEMA_VERSION + 1);
$new_model_table_name = strtolower($base_model);

//set up replacements
$replacements = array("BaseModel"=>$base_model, "base_model"=>strtolower($base_model));

generate_from_base("$framework_dir/BaseModel.php",                  "./app/model/$base_model.php", $replacements);
generate_from_base("$framework_dir/base_upgrade_migration.sql",     "./database/upgrade_to_$new_schema_version.sql", $replacements);
generate_from_base("$framework_dir/base_downgrade_migration.sql",   "./database/downgrade_to_$new_schema_version.sql", $replacements);
generate_from_base("$framework_dir/BaseControler.php",              "./app/controler/$base_model" . "Controler.php", $replacements);
generate_from_base("$framework_dir/BaseView.php",                   "./app/view/$base_model" . "View.php", $replacements);
generate_from_base("$framework_dir/BaseModelTest.php",              "./tests/unit/$base_model" . "Test.php", $replacements);


?>
