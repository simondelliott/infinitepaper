<?php
require_once ( './lib/framework/cli_common.php');

//loop through the jobs
$jobs = FoundationJob::get_all();

foreach($jobs as $job) {
    $job_name = get_class($job);
    echo "checking job $job_name ";
    if ($job->execute_if_over_schedule())
        echo "executed\n";
    else
        echo "ignored\n";

}

?>
