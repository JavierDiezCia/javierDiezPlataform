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
$dni = $_GET["dni"];
$estado = 1;

// Verificar si el ID y el detalle están presentes
if (empty($id) || empty($dni)) {
  // Si alguno de los parámetros está vacío, mostrar un mensaje de error y detener la ejecución
  echo "ID de la notificacion no proporcionado.";
  return;
}

$totalNotificaciones = 0;

$notis = [];

$tiempoTranscurrido = new DateTime('2022-01-01 00:00:00');
$tiempoTranscurrido->modify('-1 day');

// Prepara la consulta SQL
$stmt = $conn->prepare("SELECT N.*, NV.* FROM notificaciones N
                        JOIN noti_visualizaciones NV ON N.noti_id = NV.noti_id
                        WHERE noti_destinatario = :destinatario AND notiVis_cedula = :cedula
                        ORDER BY noti_fecha DESC LIMIT 50");
$stmt->bindParam(':destinatario', $_SESSION['user']['usu_rol']);
$stmt->bindParam(':cedula', $_SESSION["user"]["cedula"]);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultado) {
  $notis = $resultado;
  $totalNotificaciones = 0;
  foreach ($notis as $noti) {
    if ($noti['notiVis_vista'] == 0) {
      $totalNotificaciones++;
    }
  }
}


// Eliminar la actividad con el ID proporcionado
$statement = $conn->prepare("UPDATE noti_visualizaciones SET notiVis_vista = :estado WHERE noti_id = :id AND notiVis_cedula = :dni");
$statement->execute([":id" => $id, ":estado" => $estado, ":dni" => $dni]);

// Devolver una respuesta en lugar de redirigir
echo json_encode(["success" => true, "totalNotificaciones" => $totalNotificaciones]);

?>