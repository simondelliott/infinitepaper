<?php

//routes for the cell
$routes["cell"]   = new Route(array(
    "controler"=>"cell",
    "action"=>"show",
    "pattern"=>"/cell\/:xcord\/:ycord/",
    "xcord" => "[\w ]+",
    "ycord" => "[\w ]+"
    ));


$routes["cell_post"]   = new Route(array(
    "controler"=>"cell",
    "action"=>"save",
    "pattern"=>"/cell\/:xcord\/:ycord/",
    "xcord" => "[\w ]+",
    "ycord" => "[\w ]+",
    "method" => "POST"
    ));

//other routes
$routes["user_login"] = new Route(array("controler"=>"user", "action"=>"login"));
$routes["admin_index"] = new Route(array("controler"=>"admin", "action"=>"index"));
$routes["root"] = new Route(array("controler"=>"site", "action"=>"index"));

?>
