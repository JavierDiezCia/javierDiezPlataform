<?php
require "../../../sql/database.php";
require "../../partials/kardex_delete.php";
require "../../partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
if (($_SESSION["user"]["usu_rol"] != 2)) {
    header("Location: ../index.php");
    return;
}

// Obtener el ID de la orden de diseño
$id = $_GET["id"];

// Verificamos si se proporcionó un ID válido en la URL
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    // Si no se proporcionó un ID, redirigimos a alguna página de error o a la página principal
    header("Location: ../pages-error-404.html");
    return;
}



// Verificamos si la orden de diseño existe en la base de datos
$statement = $conn->prepare("SELECT od_estado, od_detalle
                            FROM orden_disenio 
                            WHERE od_estado = 'MATERIALIDAD' AND od_id = :id;"
                            );
$statement->execute([":id" => $id]);
$orden_diseño = $statement->fetch(PDO::FETCH_ASSOC);
$detalle = $orden_diseño['od_detalle'];

if (!$orden_diseño) {
    // Si no se encuentra la orden de diseño, redirigimos a alguna página de error o a la página principal
    header("Location: ../od.php");
    return;
}

// Actualizar el estado de la orden de diseño a "Revisando" (código de estado 4)
$conn->prepare("UPDATE orden_disenio SET od_estado = 'OP' WHERE od_id = :id")->execute([
    ":id" => $id,
]);

// registramos la notificacion
$conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_destinatario, noti_detalle, noti_fecha) VALUES (:cedula, :destinatario, :detalle, :fecha)")->execute([
    ":cedula" => $_SESSION["user"]["cedula"],
    ":destinatario" => 3,
    ":detalle" => "La orden de diseño " . "#" . $id . " " . "<b>$detalle</b>" . " ha sido aprobada. Puedes crear una OP.",
    ":fecha" => date("Y-m-d H:i:s"),
]);

// notificaciones con visualizaciones en la tabla noti_visualizaciones
$notiId = $conn->lastInsertId();
$usuarios = $conn->prepare("SELECT P.cedula FROM personas P
                            JOIN orden_disenio OD ON P.cedula = OD.od_responsable
                            JOIN usuarios U ON P.cedula = U.cedula
                            WHERE usu_rol = 3 AND OD.od_id = :id");
$usuarios->execute();
$usuarios = $usuarios->fetchAll(PDO::FETCH_ASSOC);

$notiVisualizacion = $conn->prepare("INSERT INTO noti_visualizaciones (noti_id, notiVis_cedula) VALUES (:noti_id, :cedula)");
$notiVisualizacion->execute([
    ":noti_id" => $notiId,
    ":cedula" => $usuarios["cedula"]
]);

// Registramos el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["cedula"], "APROBÓ", 'ORDEN DISEÑO', "Producto: " . $orden_diseño["od_producto"]);

// Redirigimos a la página de ordenes de diseño
header("Location: ../../historialOd.php#content2");
?>
