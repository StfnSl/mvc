<?php
error_reporting();
session_start();
include __DIR__.'/Core/Autoload.php';

$loader = new Autoload();
$loader->addNamespace('Portal', __DIR__)->register();

$route = new Portal\Core\Router();
//Verarbeiten der Routen 
//Bei jedem Aufruf überprüfen ob ein Benutzer angemeldet ist / bzw. ob anmeldedaten vorliegen
$route->before('GET', '/.*', function() use($route){
    if(isset($_GET['BENUTZERNAME']) && !isset($_SESSION['User'])){
        session_unset();
        $model = new Portal\Core\Models\Model();
        $_SESSION['Menuebar'] = '';
        if(strlen($_GET['BENUTZERNAME']) > 0){
            $userResponse = $model->getCurrentUser($_GET['BENUTZERNAME']);
            
            if($userResponse[0] == 1){
                $_SESSION['User'] = $_GET['BENUTZERNAME'];
                $_SESSION['lesen'] = $userResponse[1];
                $_SESSION['schreiben'] = $userResponse[2];
                $_SESSION['aendern'] = $userResponse[3];
                
                header('Location: '. $route->getBasePath() . $route->getCurrentUri());
                return;
            }
            header('Location: '. $route->getBasePath() . '/error1');
            return;
        }
        //Der Benutzer ist nicht vorhanden!!!
        $_SESSION['mel'] = 0;
        header('Location: '. $route->getBasePath() . '/error1');
        return;
    }else{
        return;
    }
});
$route->setNamespace('Portal\\App\\Controllers');
$route->get('/', 'Portal@index');
//WE
$route->get('tageserfassungsuch', 'Tageserfassung@index');

$route->get('airwaybill', 'AirwayBill@index');