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
$estado = 1;

// Verificar si el ID y el detalle están presentes
if (empty($id)) {
  // Si alguno de los parámetros está vacío, mostrar un mensaje de error y detener la ejecución
  echo "ID de la notificacion no proporcionado.";
  return;
}

// Eliminar la actividad con el ID proporcionado
$statement = $conn->prepare("UPDATE notificaciones SET noti_vista = :estado WHERE noti_id = :id");
$statement->execute([":id" => $id, ":estado" => $estado]);

// Redirigir al usuario de regreso a la página de actividades
header("Location: ../index.php");
// Finalizar el script para evitar que se ejecute más código
return;
?>