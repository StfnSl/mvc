<?php
namespace Portal\Core;
/**
 * Hauptsteuerung der Application
 * @todo Steuerung übe namespace für einfaches Include
 */
class App
{
    private $config = [];
    /**
     * Setzen des Pfades für Header anweisungen
     *
     * @var string
     */
    private $loeServerPfad = null;
    
    /**
     * Instanz der Klasse
     */
    public function __construct(){
        $this->loeServerPfad = 'http://'. $_SERVER['SERVER_NAME'] . '/loeweprog';
        $this->setConfig('readST_AS400', 'lib/ReadSteuerungAS400.php');
        
        define('URI', $_SERVER['REQUEST_URI']);
        define('ROOT', $_SERVER['DOCUMENT_ROOT']);
        
    }
    /**
     * Setzen einer Konfiguration
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    protected function setConfig($key, $value = null){
        $this->config[$key] = $value;
    }
    /**
     * Zurückgeben einer Variable aus dem config array, es kann ein default wert übergeben werden.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getConfig($key, $default = null){
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
    /**
     * Gibt einen Wert aus der globalen $_SESSION zurück
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public static function getSession($key, $default=null){
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    /**
     * Setzt einen Wert in der globalen $_SESSION
     * @param string $key
     * @param mixed|null $value
     */
    public static function setSession($key, $value=null){
        $_SESSION[$key] = $value;
    }

    public function run($router){
        error_reporting(E_ALL);
        session_start();
        
    }
    /**
     * Gibt den Header aus, damit die Seite oder Grafik nicht im cache landet
     *
     * @return void
     */
    public static function setHeader(){
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '. gmd('D, d M Y H:i:s').' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header ('Content-type: text/html; charset=charset=utf-8');
    }

    protected function callAction($controller, $action){
        
        try{
            $controller = new $controller();
            return $controller->{$action}();
        }catch(\Exception $e){
            throw new \Exception('Die Seite ['. $controller .'::'. $action .'] ist nicht vorhanden');
        }
        
    }



}

