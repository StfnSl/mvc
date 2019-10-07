<?php
namespace Portal\Core\Controllers;

use Portal\Core\ViewLoader;

abstract class Controller
{
    protected $pageTitle;
    protected $message;
    protected $model;
    protected $router;
    protected $view;
    protected $loeServerPfad;

    public function __construct($router){
        $this->loeServerPfad = $router->getBasePath();
        //$this->router = $router;
        $this->model = new Model();
        $this->user[0] = $_SESSION['User'];
        $this->user += $this->model->getCurrentUser($_SESSION['User']);
        $this->view = new ViewLoader();
    }


    public function redirect($to){
        header('location: '. $this->loeServerPfad . '/'. $to);
    }

    protected function can($permission = null){
        
    }

    public function render($template, $arguments = null){
        return $this->view->view($template, $arguments);
    }
}

