<?php

namespace Src\routes;

require_once '../../vendor/autoload.php';

use Src\lib\Route;

Route::get('/', function () {
  echo 'Hello World';
});

Route::get('/test', function () {
  echo 'Route Test';
});

Route::get('/login',  function () {
  echo 'Login Panel';
});

Route::dispatch();
