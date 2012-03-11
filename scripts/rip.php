<?php
//assume that you are in the root directory
require_once ( './lib/framework/cli_common.php');

echo "Starting ripping script\n";

$base_dir = "content/framework";

$output = shell_exec("rm -rf $base_dir");
$output = shell_exec("mkdir $base_dir");

foreach ($GLOBALS["locations"] as $loc){
    echo "creating $loc->path\n";
    if ($loc->path)
        shell_exec("mkdir $base_dir/$loc->path");

}

echo "copying the framework\n";
shell_exec("cp -r lib/framework $base_dir/lib");

echo "copying the .htaccess\n";
shell_exec("cp .htaccess $base_dir/.htaccess");

echo "copying the .index.php\n";
shell_exec("cp .htaccess $base_dir/index.php");

echo "copying the scripts\n";
shell_exec("cp -r scripts $base_dir");

echo "copying the README\n";
shell_exec("cp lib/framework/README $base_dir/README");

echo "copying the sample_files\n";
shell_exec("cp -r lib/framework/sample_files/* $base_dir/");

echo "remove any svn files\n";
shell_exec("find $base_dir -name \".svn\"  -exec rm -rf {} \;");


echo "ripping script complete\n";
echo "\n";
?>