<?php
namespace Portal\Core;

class ViewLoader
{
    protected $viewLoaderPath;

    protected static $instance = null;

    public function __construct($path=null){
        if(null === $path){
            $this->viewLoaderPath = getcwd() . '/views/';
        }else{
            $this->viewLoaderPath = trim($path, '/') . '/';
        }
        self::$instance = $this;
    }

    public function view($template, $arguments){
        if(gettype($arguments) == 'array'){
            foreach($arguments as $name => $value){
                $$name = $value;
            }
        }
        $template = preg_replace('/(\.)/mUi', '/', $template);
        $file = $this->viewLoaderPath . $template .'.php';
        if(file_exists($file)){
            require $file;
        }
    }

        public static function partial($template){
            
            return self::$instance->view($template);
        }



}

