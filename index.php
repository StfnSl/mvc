<?php 

require __DIR__ .'/Core/Autoload.php';
$loader = new Autoload();
$loader->addNamespace('App', __DIR__)->register();

$app = new App\Core\Application();