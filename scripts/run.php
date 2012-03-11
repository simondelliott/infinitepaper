<?php
require_once ( './lib/framework/cli_common.php');


$jobs = array();
if ($argc > 1){
    array_shift($argv);
    while($job = array_pop($argv))
        array_push($jobs, new $job());
}
else{
    //loop through the jobs
    $jobs = FoundationJob::get_all();
}

foreach($jobs as $job) {
    echo "job about to run " . get_class($job)  . "\n";
    $job->exec();
    echo "job " . get_class($job) . " complete\n";
}


?>
