<?php

require_once ( 'lib/framework/common.php');
require_once ( 'routes.php');

$selected_route = "";
$route = null;

foreach ($routes as $name => $r){
    $selected_route = $name;
    if ($r->match()){
        $route = $r;
        break;
    }
    if ($name == "root"){
        $root_route = $r;
    }
}

if ($route ==NULL)
    $route = $root_route; //note could still be null

if ($route ==NULL){
    handle_error("No route to follow");
}

$GLOBALS["route"] = $route;
$controler = $route->get_controler_class();
$action = $route->action;

$controler = new $controler($action, $route->matches);

?>