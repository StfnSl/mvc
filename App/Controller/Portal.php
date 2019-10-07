<?php
namespace Portal\App\Controller;

use Portal\Core\Controllers\Controller as BaseController;
use Portal\Core\Models\Model;
use Portal\Core\App;

class Portal extends BaseController
{
    

    public function index(){
        App::setHeader();
        var_dump('TEST'. self::class);
        var_sump($this->user);
        return $this->render('portal.index');
    }
}

