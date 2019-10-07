<?php
namespace Portal\Core;

class Router
{
    /**
     * Alle verfügbaren Routes
     *
     * @var array
     */
    protected $routes = [
        'GET'=>[],
        'POST'=>[],
    ];
    /**
     * @param string $file
     * @return \Portal\Core\Router
     */
    public static function router($file){
        $router = new static;
        require_once $file;
        return $router;
    }

    public static function getParams($key, $default=null){
        $get = filter_input_array(INPUT_GET);
        $get = array_map('trim', $get);
        $get = array_map('htmlspecialchars', $get);
        return isset($get[$key]) ? $get[$key] : $default;
    }

    public static function postParams($key, $default=null){
        $get = filter_input_array(INPUT_POST);
        $get = array_map('trim', $get);
        $get = array_map('htmlspecialchars', $get);
        return isset($get[$key]) ? $get[$key] : $default;
    }
    /**
     * Setzen einer GET Route
     *
     * @param string $uri
     * @param string $controller
     *
     * @return \Portal\Core\Router
     */
    public function get($uri, $controller){
        $this->routes['GET'][$uri] = $controller;
        return $this;
    }
    /**
     * Setzen einer POST route
     *
     * @param string $uri
     * @param string $controller
     *
     * @return \Portal\Core\Router
     */
    public function post($uri, $controller){
        $this->routes['POST'][$uri] = $controller;
        return $this;
    }
    /**
     * Gibt die URI zurück
     *
     * @return string
     */
    public function getURI(){
        $uri = URI;
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        return $uri;
    }
    /**
     * Gib die Methode der Anfrage zurück
     *
     * @return string 
     */
    protected function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
    /**
     * Überprüft ob eine Route existiert und gibt den Controller und die Action zurück
     *
     * @return array
     */
    public function dispatch(){
        $uri = $this->getURI();
        $requestType = $this->getMethod();
        if(array_key_exists($uri, $this->routes[$requestType])){
            list($controller, $action) = explode('@', $this->routes[$requestType][$uri]);
        }else{
            $controller = 'ErrorController';
            $action = 'notFound';
        }
        return compact('controller', 'action');

    }


}

