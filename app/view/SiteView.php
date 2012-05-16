<?php

class SiteView extends ApplicationView {

    public function contents (){
?>
    <h1>Welcome to Your Next Application</h1>
    <ul>
        <li><a href="/draw2.html">draw 2</a></li>
        <li><a href="/move.html">move</a></li>
        <li><a href="/draw.html">draw</a></li>
    </ul>
    <h2>API</h2>
    <ul>
        <li>GET cell/x/y</li>
        <li>POST cell/x/y [posting image data from the canvass]</li>
        <li>GET paper/x/y?hieght=[int]&amp;width=[int]</li>
    </ul>
        
    
<?php
    }
}
?>
