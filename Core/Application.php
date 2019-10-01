<?php
namespace App\Core;

class Application
{
  public function __construct()
  {
    echo 'test';
    var_dump(self::class);
  }
}

