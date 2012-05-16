<?php

class CellControler extends ApplicationControler{
	
    public function index(){

        $all = Cell::get_all();
        $view = new CellView($all);
        $view->show();
    }


    public function save(){

      debug("SAVE called");
      debug("xcord=" . $this->params["xcord"]);
      debug("ycord=" . $this->params["ycord"]);

      // if the cell does not exist the create it
      $cells = Cell::find_by_xcord_and_ycord($this->params["xcord"], $this->params["ycord"]);
      $cell = $cells[0];
      var_dump($cell);
      if(!$cell){
        $cell = new Cell($this->params);
        if(!$cell->save())
          handle_error ("cannot save the cell");
      }
      $cell->save_picture($_REQUEST["stroke_data"]);

    }
    
    public function show(){
        debug("show called");
        debug("xcord=" . $this->params["xcord"]);
        debug("ycord=" . $this->params["ycord"]);
    }
}

?>