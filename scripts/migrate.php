<?php
//assume that you are in the root directory
require_once ( './lib/framework/cli_common.php');

echo "Starting upgrade procedure\n";
echo "target schema version=". DATABASE_SCHEMA_VERSION . "\n";

$database=new Database();

$schema_version = Setting::get("db_schema_version");
echo "current schema version=$schema_version\n";
if ($schema_version==NULL){
        $schema_to_install = 0;
}
else{
        $schema_to_install = $schema_version + 1;
}


while ($schema_to_install<=DATABASE_SCHEMA_VERSION){
        echo "attempt to upgrade to schema version $schema_to_install\n";

        if ($database->execute_db_install_step($schema_to_install)){
            echo  "successfully installed schema version $schema_to_install\n";
        }
        else {
            echo  "[ERROR] failed to install schema version $schema_to_install\n";
        }

        $schema_to_install++;
}
$schema_version = Setting::get("db_schema_version");

echo "Upgrade to $schema_version complete\n";

?>
