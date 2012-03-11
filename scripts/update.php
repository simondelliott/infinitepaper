<?php
//assume that you are in the root directory
require_once ( './lib/framework/cli_common.php');

$base_dir = "content/framework";

echo "copying the framework\n";
shell_exec("cp -r lib/framework $base_dir/lib");

echo "copying the .htaccess\n";
shell_exec("cp lib/framework/htaccess $base_dir/.htaccess");

echo "copying the .index.php\n";
shell_exec("cp .index.php $base_dir/index.php");

echo "copying the scripts\n";
shell_exec("cp -r scripts $base_dir");

echo "copying the README\n";
shell_exec("cp lib/framework/README $base_dir/README");

echo "copying the sample_files\n";
shell_exec("cp -r lib/framework/sample_files/* $base_dir/");

echo "remove any svn files\n";
shell_exec("find $base_dir -name \".svn\"  -exec rm -rf {} \;");


?>
