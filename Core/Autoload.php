<?php
/**
 * Lädt nachfolgenden Klassen anhand des Namensraumes
 * @author Stefan Schölzel <stefan.schoelzel@loewe-logistics.de>
 * @example $loader = new Autoload()
 *          $loader->addNamespace('App', __DIR__)->register()
 */
final class Autoload{
  /**
   * Namespace Prefix
   *
   * @var array
   */
  protected $namepacePrefixes = [];
  /**
   * Registrieren Sie den Loader beim SPL-Autoloader.
   *
   * @return void
   */
  public function register(){
    spl_autoload_register([$this, 'loadClass']);
  }
  /**
   * Fügt ein Basisverzeichnis für ein Namespace-Präfix hinzu.
   *
   * @param string $prefix
   * @param string $baseDir
   * @param boolean $prepend
   * @return \Autoload Rückgabe der Instanz für Method-Chaining
   */
  public function addNamespace($prefix, $baseDir, $prepend = false){
    $prefix = trim($prefix, '\\') . '\\';
    $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
    if(false === isset($this->namepacePrefixes[$prefix])){
      $this->namepacePrefixes[$prefix] = [];
    }
    if($prepend){
      array_unshift($this->namepacePrefixes[$prefix], $baseDir);
    }else{
      array_push($this->namepacePrefixes[$prefix], $baseDir);
    }
    return $this;
  }
  /**
   * Lädt die Datei für einen bestimmten Klassennamen.
   *
   * @param string $class
   * @return mixed Der zugeordnete Dateiname bei Erfolg oder boolean false bei einem Fehler
   */
  public function loadClass($class){
    $prefix = $class;
    while(false !== $pos = strrpos($prefix, '\\')){
      //Beibehalten des nachfolgenden Namespace-Prefixes
      $prefix = substr($class, 0, $pos + 1);
      // Der "Rest ist der Relative-Klassen-Name
      $relativeClass = substr($class, $pos + 1);
      //Versuchen, eine zugeordnete Datei für das Präfix und die relative Klasse zu laden
      $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
      if($mappedFile){
        return $mappedFile;
      }
      $prefix = rtrim($prefix, '\\');
    }
    return false;
  }
  /**
   * Lädt die benötigte Datei, für einem Namespace-Prefix und den Klassennamen
   *
   * @param string $prefix
   * @param string $relativeClass
   * @return mixed False wenn nicht vorhanden, ansonsten den Dateinamen der Klasse
   */
  protected function loadMappedFile($prefix, $relativeClass){
    if(false === isset($this->namepacePrefixes[$prefix])){
      return false;
    }
    foreach($this->namepacePrefixes[$prefix] as $baseDir){
      $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
      if($this->requireFile($file)){
        return $file;
      }
    }
    return false;
  }
  /**
   * Überprüfen ob Datei vorhanden, und zur Laufzeit laden
   *
   * @param string $file
   * @return bool
   */
  protected function requireFile($file){
    if(file_exists($file)){
      require $file;
      return true;
    }
    return false;
  }


}