<?php

class Location {
    public $path = '';
    public $autoload = false;

    public function Location($path, $autoload = false) {
        $this->path = $path;
        $this->autoload = $autoload;
    }
}

?>