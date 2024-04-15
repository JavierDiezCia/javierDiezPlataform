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

    try {
      $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
      $dotenv->load();
    } catch (\Exception $e) {
      die('Error al cargar el archivo .env' . $e->getMessage());
    }

    $this->hostname = $_ENV('DB_HOST', 'localhost');
    $this->database = $_ENV('DB_NAME', 'javierdiez');
    $this->username = $_ENV('DB_USER', 'root');
    $this->password = $_ENV('DB_PASS', '');



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
}
