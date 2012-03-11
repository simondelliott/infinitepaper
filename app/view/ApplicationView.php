<?php

class ApplicationView {

	const TITLE = "Your Next Site";
	const CREATORS = "The Development Collective";
	
	public function header (){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta HTTP-EQUIV="Content-Type" Content="text/html; charset=Windows-1252">
        <meta name="description" content="Your next application description.">
        <title><?php echo APPLICATION_NAME ?> we love the development collective</title>
        <link rel="stylesheet" type="text/css" href="/style/style.css">
    </head>
    <body>
        <div id="contents">
<?php
    }
	public function contents (){
?>
<?php
	}	
	public function footer (){
?>
            <div id="footer">
                    we love the <a href="http://www.developmentcollective.com">development collective</a>
            </div>
        </div>
    </body>

</html>
<?php
    }
    public function show (){
        $this->header();
        $this->contents();
        $this->footer();
    }
}
?>
