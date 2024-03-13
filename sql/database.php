<?php
// DEFINIMOS LAS VARIABLES CON LOS VALORES PARA CONEXION
$host = "localhost";
$database = "javierdiez";
$user = "root";
$password = "";

try {
  $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);
  $conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8"); // Configuración de la codificación de caracteres
  //para comprobar si funciona descomenta el siguiente foreach
  // foreach ($conn->query("SHOW DATABASES") as $row) {
  //   print_r($row);
  // }
} catch (PDOException $e) {
  die("PDO Connection Error: " . $e->getMessage());
}
?>
