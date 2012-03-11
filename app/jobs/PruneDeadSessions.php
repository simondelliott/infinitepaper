<?php

class PruneDeadSessions extends FoundationJob{

    public $period = 864000; /* 10 days */

    public function run (){
        
	$db = new Database();

        $sql = "delete from session_data where exists (select * from session where session.session_id = session_data.session_id and session.expire < now());";
        $sql .= "delete from session where expire < now();";

        $db->setQuery ( $sql );
        $db->query_batch();

    }

}

?>