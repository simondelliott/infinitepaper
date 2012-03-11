<?php


class RouteTest extends PHPUnit_Framework_TestCase {

    private $_fixture_valid_route;
    private $_fixture_valid_url = "/city/Worthing/cabs/T4XEY?format=html";
    private $_fixture_valid_complex_route_post;

    function setUp(){

        $this->_fixture_valid_route_get = new Route(array(
                "controler"=>"cab",
                "action"=>"show",
                "pattern"=>"/city\/:city_name\/cabs\/:reg/",
                "city_name" => "[\w ]+",
                "reg" => "[\w ]+"));

        $this->_fixture_valid_route_post = new Route(array(
                "controler"=>"cab",
                "action"=>"show",
                "pattern"=>"/city\/:city_name\/cabs\/:reg/",
                "city_name" => "[\w ]+",
                "reg" => "[\w ]+", "method"=>"POST"));

        $this->_fixture_valid_complex_route = new Route(array(
                "controler"=>"cell",
                "action"=>"save",
                "pattern"=>"/cell\/:xcord\/:ycord/",
                "xcord" => "[\w ]+",
                "ycord" => "[\w ]+",
                "method"=>"POST"));
    }

    public function test_matches_get(){
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REDIRECT_URL"] = $this->_fixture_valid_url;
        $this->assertTrue($this->_fixture_valid_route_get->match());
    }

    public function test_matches_post(){
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REDIRECT_URL"] = $this->_fixture_valid_url;
        $this->assertTrue($this->_fixture_valid_route_post->match());
    }

    public function test_get_route(){
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REDIRECT_URL"] = $this->_fixture_valid_url;
        $this->assertEquals($this->_fixture_valid_route_get->get_route(), "city/Worthing/cabs/T4XEY");
    }

    public function test_complex_route_post(){
        $_SERVER["REQUEST_METHOD"] = "POST";

        $_SERVER["REDIRECT_URL"] = "cell/123/456";
        $this->assertTrue($this->_fixture_valid_complex_route->match());

    }

}
?>