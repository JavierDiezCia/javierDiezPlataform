<?php

namespace Src\config;

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv as Dotenv;
use mysqli as mysqli;

class database
{
  private string $hostname;
  private string $username;
  private string $password;
  private string $database;


  protected mysqli $connection;


  public function __construct()
  {
    // Cargar variables de entorno del archiov .env en el directorio raíz.
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    $this->hostname = $_ENV['DB_HOST'];
    $this->database = $_ENV['DB_DATABASE'];
    $this->username = $_ENV['DB_USERNAME'];
    $this->password = $_ENV['DB_PASSWORD'];



    date_default_timezone_set('America/Lima'); // Configuración de la zona horaria

    $this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->database);
    if ($this->connection->connect_error) {
      die('Error de conexión: ' . $this->connection->connect_error);
    }
  }

  public function getConnection()
  {
    return $this->connection;
  }

  /*Funcion para chequear la conexion a la base datos*/
  public function checkConnection()
  {
    $result = $this->connection->query('SELECT 1');
    if (!$result) {
      die('Error de conexión: ' . $this->connection->connect_error);
    } else {
      echo 'Conexión exitosa';
    }
  }
}

/* Test the connection
 * Ejecuta esta instancia para verficar la conexión con la base de datos
 * Descomenta la siguiente línea
 */
//$check = new database();
//$check->checkConnection();
