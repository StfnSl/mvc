<?php
namespace Portal\Core;

class Router
{
    /**
     * Die einzelnen Routen und ihre Aufrufe
     *
     * @var array
     */
    private $afterRoutes = [];
    /**
     * Aufrufe die vor der eigentlichen Route durchgeführt werden sollen
     *
     * @var array
     */
    private $beforeRoutes = [];
    /**
     * Callback function die aufgerufen wird, wenn keine passende Route gefunden wurde
     *
     * @var string
     */
    protected $notFoundCallback = '';
    /**
     * Aktuelle Route, wenn unter Routen aufgerufen werden
     *
     * @var string
     */
    private $baseRoute = '';
    /**
     * Art der Abfrage
     *
     * @var string
     */
    private $requestMethod = '';
    /**
     * Name der Application
     *
     * @var [type]
     */
    private $serverBasePath = null;
    /**
     * Name des Namespaces
     *
     * @var string
     */
    private $namespace = '';
    /**
     * Speichert eine Before-Middleware-Route und eine Bearbeitungsfunktion, 
     * die beim Zugriff mit einer der angegebenen Methoden ausgeführt werden soll.
     *
     * @param string $methods Erlaubte Methoden, mit | getrennt
     * @param string $pattern Name der Route
     * @param Object|Callable|Closure $fn Aufruf was bei Zutreffen der Route und Middleware gemacht werden soll
     *
     * @return void
     */
    public function before($methods, $pattern, $fn){
        $pattern = $this->baseRoute . '/' .trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach(explode('|', $methods) as $method){
            $this->beforeRoutes[$method][] = [
                'pattern'=>$pattern,
                'fn'=> $fn
            ];
        }
    }
    /**
     * Speichert eine Route, und ruft die Funktion bei passender Methode auf
     *
     * @param string $methods Getrennt mit |
     * @param string $pattern
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function match($methods, $pattern, $fn){
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach(explode('|', $methods) as $method){
            $this->afterRoutes[$method][] = [
                'pattern'=>$pattern,
                'fn'=> $fn
            ];
        }
    }
    /**
     * Kurzmethode um eine Route für alle Methoden verfügbar zu machen  
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function all($pattern, $fn){
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }
    /**
     * Kurzmethode für GET Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function get($pattern, $fn){
        $this->match('GET', $pattern, $fn);
    }
    /**
     * Kurzmethode für POST Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function post($pattern, $fn){
        $this->match('POST', $pattern, $fn);
    }
    /**
     * Kurzmethode für PUT Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function put($pattern, $fn){
        $this->match('PUT', $pattern, $fn);
    }
    /**
     * Kurzmethode für OPTIONS Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function options($pattern, $fn){
        $this->match('OPTIONS', $pattern, $fn);
    }
    /**
     * Kurzmethode für PATCH Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function patch($pattern, $fn){
        $this->match('PATCH', $pattern, $fn);
    }
    /**
     * Kurzmethode für HEAD Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function head($pattern, $fn){
        $this->match('HEAD', $pattern, $fn);
    }
    /**
     * Kurzmethode für DELETE Routes
     *
     * @param string $methods Getrennt mit |
     * @param Object|Callable|Closure $fn
     *
     * @return void
     */
    public function delete($pattern, $fn){
        $this->match('DELETE', $pattern, $fn);
    }
    /**
     * Möglichkeit Routen zu mit einem PArentschlüssel zu verbinden
     *
     * @param string $baseRoute
     * @param Object|Callable $fn
     *
     * @return void
     */
    public function mount($baseRoute, $fn){
        $currentBaseRoute = $this->baseRoute;
        $this->baseRoute .= $baseRoute;
        call_user_func($fn);
        $this->baseRoute = $curBaseRoute;
    }
    /**
     * Gibt alle REquest (Anfrage) Header zurück
     *
     * @return array
     */
    public function getRequestHeaders(){
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                return $headers;
            }
        }
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
    /**
     * Gibt die Request Methode zurück
     *
     * @return string
     */
    public function getRequestMethod(){
        $method = $_SERVER['REQUEST_METHOD'];
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }
    /**
     * Setzt einen Namespace, nützlich für die Übersichtlichkeit
     *
     * @param string $namespace
     *
     * @return self
     */
    public function setNamespace($namespace){
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }
        return $this;
    }
    /**
     * Gibt einen gesetzten Namespace zurück
     *
     * @return string
     */
    public function getNamespace(){
        return $this->namespace;
    }
    /**
     * Führt alle Routen aus, und ruft die $callback funktion auf, sofern vorhanden
     *
     * @param Object|Callable $callback
     *
     * @return boolean
     */
    public function run($callback=null){
        $this->requestMethod = $this->getRequestMethod();
        //Alle before-Routen ausführen, sofern vorhanden
        if (isset($this->beforeRoutes[$this->requestedMethod])) {
            $this->handle($this->beforeRoutes[$this->requestedMethod]);
        }
        $numHandled = 0;
        if (isset($this->afterRoutes[$this->requestedMethod])) {
            $numHandled = $this->handle($this->afterRoutes[$this->requestedMethod], true);
        }
        if ($numHandled === 0) {
            if ($this->notFoundCallback) {
                $this->invoke($this->notFoundCallback);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            }
        } // Wenn eine Route gefunden und verarbeitet wurde, rufe den NachderRoute aufruf durch, sofern vorhanden
        else {
            if ($callback && is_callable($callback)) {
                $callback();
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }
        return $numHandled !== 0;
    }
    /**
     * Setzen der 404 Seite
     *
     * @param Object|Callable $fn
     *
     * @return void
     */
    public function set404($fn)
    {
        $this->notFoundCallback = $fn;
    }
    /**
     * Bearbeitet eine Reihe von Routen: Wenn eine Übereinstimmung gefunden wird, 
     * wird die entsprechende Bearbeitungsfunktion ausgeführt.
     *
     * @param array $routes
     * @param bool $quitAfterRun
     *
     * @return int
     */
    private function handle($routes, $quitAfterRun=false){
        $numHandled = 0;
        $uri = $this->getCurrentUri();
        foreach ($routes as $route) {
            //Alle geschweiften Klammern ersetzen
            $route['pattern'] = preg_replace('/\/{(.*?)}/', '/(.*?)', $route['pattern']);
            if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)){
                $matches = array_slice($matches, 1);
                $params = array_map(function ($match, $index) use ($matches) {
                    if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    }
                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                },$matches, array_keys($matches));
                $this->invoke($route['fn'], $params);
                ++$numHandled;
                if ($quitAfterRun) {
                    break;
                }
            }
        }
        return $numHandled;
    }
    /**
     * Aufruf der zu verabeitenen Funktion
     *
     * @param string|Object|Callable $fn
     * @param array $params
     *
     * @return mixed
     */
    private function invoke($fn, $params = []){
        if (is_callable($fn)) {
            //Ist der funktionausführbar?
            call_user_func_array($fn, $params);
        }elseif (stripos($fn, '@') !== false) {
            //Ist ein Controller@methoden aufruf verfügbar?
            list($controller, $method) = explode('@', $fn);
            if ($this->getNamespace() !== '') {
                $controller = $this->getNamespace() . '\\' . $controller;
            }
            if (class_exists($controller)) {
                //Ist der Controller überhaupt verfügbar?
                if (call_user_func_array([new $controller($this), $method], $params) === false) {
                    //Ausführen, wenn es sich nicht um eine statische Methode handelt, ansonten die statische Methode ausführen
                    if (forward_static_call_array([$controller, $method], $params) === false);
                }
            }
        }

    }
    /**
     * Gibt die aktuelle URI ohne GET-Parameter zurück
     *
     * @return string
     */
    public function getCurrentUri(){
        //// Den aktuellen Anforderungs-URI abrufen und den Basispfad entfernen (= Ermöglicht das Ausführen des Routers in einem Unterordner)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getBasePath()));
        //GET Parameter aus der URL entfernen
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        //Entfernen Slash am Ende + einen Schrägstrich am Anfang erzwingen
        return '/' . trim($uri, '/');
    }
    /**
     * Gibt den Basis-Pfad zurück
     *
     * @return string
     */
    public function getBasePath()
    {
        // Überprüfen ob der Basis-Pfad gesetzt wurde, wenn nicht setze ihn
        if ($this->serverBasePath === null) {
            $this->serverBasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        }
        return $this->serverBasePath;
    }
    /**
     * Festlegen des Server-Basispfad.
     * Wird verwendet, wenn sich Ihr Eintragsskriptpfad von Ihren Eintrags-URLs unterscheidet.
     *
     * @param string $path
     *
     * @return void
     */
    public function setBasePath($path){
        $this->serverBasePath = $path;
    }
}

