<?php

class BaseModelControler extends ApplicationControler{
	
    public function index(){

        $all = BaseModel::get_all();
        $view = new BaseModelView($all);
        $view->show();
    }

}

?>
