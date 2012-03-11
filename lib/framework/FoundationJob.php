<?php

class FoundationJob {
	
    public $period = 86400; /* one day in seconds */
    private $setting_key;

    public function run (){
        echo "No job to run\n";
        return true;
    }

    public function exec(){
        Setting::put($this->setting_key, time());
        $this->run();
    }

    public function __construct(){
        $this->setting_key = "[Job][" . get_class($this) . "] last run";
    }

    public function execute_if_over_schedule(){
        $last_executed = Setting::get($this->setting_key);
        if ($last_executed == null)
            $last_executed = 0;

        if (($last_executed + $this->period) < time()){
            $this->exec();
            return true;
        }
        else{
            return false;
        }
    }
    public static function get_all(){

        $jobs = array();
        $dir = dirname(__FILE__) . "/../../app/jobs/";
        $dh = opendir($dir);
        while (($file = readdir($dh)) !== false) {
            if(filetype($dir . $file)=="file"){
                $job_class = basename($file, ".php");
                array_push($jobs, new $job_class());
            }
        }
        closedir($dh);
        return $jobs;
    }
}
?>