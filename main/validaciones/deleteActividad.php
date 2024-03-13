<?php
require "../../sql/database.php";
require "../partials/session_handler.php"; 

// Iniciar sesión para identificar las sesiones

// Verificar si la sesión no existe, redirigir al login.php y detener la ejecución del script
if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  return;
}

// Obtener el ID y el detalle de la actividad a eliminar desde los parámetros GET
$id = $_GET["id"];
$od_id = $_GET["od_id"];
$estado = 1;

// Verificar si el ID y el detalle están presentes
if (empty($id) || empty($od_id)) {
  // Si alguno de los parámetros está vacío, mostrar un mensaje de error y detener la ejecución
  echo "ID o detalle de la actividad no proporcionado.";
  return;
}

// Eliminar la actividad con el ID proporcionado
$statement = $conn->prepare("UPDATE od_actividades SET odAct_estado = :estado WHERE id = :id AND od_id = :od_id");
$statement->execute([":id" => $id, ":od_id" => $od_id, ":estado" => $estado]);

// Redirigir al usuario de regreso a la página de actividades
header("Location: ../od_actividades.php?id=$od_id");
// Finalizar el script para evitar que se ejecute más código
return;
?>
