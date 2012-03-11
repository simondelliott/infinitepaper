<?php

class SiteControler extends ApplicationControler{

    public function index(){
            $view = new SiteView();
            $view->show();
    }

}
?>

