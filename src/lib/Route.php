<?php

namespace Src\lib;

class Route
{
  /*Definimos un array de todas nuestas rutas*/
  private static $routes = [];

  /*Definimos un metodo que se encargara de cargar las rutas de tipo GET*/
  public static function get($uri,  $callback)
  {
    self::$routes['GET'][$uri] = $callback;
  }
  /*Este metodo se encarga de cargar las rutas de tipo post en el navegador*/
  public static function post(string $uri, string $callback)
  {
    self::$routes['POST'][$uri] = $callback;
  }

  /*Este metodo se encarga de recuperar el parametro que escribe el usuario en el navegador*/
  public static function dispatch()
  {
    $uri = $_SERVER['REQUEST_URI'];
    echo $uri;
    /* $method = $_SERVER['REQUEST_METHOD'];
    $callback = self::$routes[$method][$uri] ?? false;
    if ($callback) {
      echo call_user_func($callback);
    } else {
      echo '404 Not Found';
    }*/
  }
}
