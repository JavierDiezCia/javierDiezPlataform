<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php"; 


// Si la sesión no existe o el rol no es 3, redirigir al login.php o al index.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"]) || $_SESSION["user"]["usu_rol"] != 3) {
    header("Location: ../login-form/login.php");
    header("Location: ../index.php");
    return;
}

// Verificamos si se proporcionó un ID válido en la URL
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    // Si no se proporcionó un ID, redirigimos a alguna página de error o a la página principal
    header("Location: ../pages-error-404.html");
    return;
}

// Obtener el ID de la orden de diseño
$id = $_GET["id"];

// Verificamos si la orden de diseño existe en la base de datos
$statement = $conn->prepare("SELECT * FROM orden_disenio WHERE od_id = :id");
$statement->execute([":id" => $id]);
$orden_diseño = $statement->fetch(PDO::FETCH_ASSOC);

if (!$orden_diseño) {
    // Si no se encuentra la orden de diseño, redirigimos a alguna página de error o a la página principal
    header("Location: ../pages-error-404.html");
    return;
}

//VERIFICAR SI HAY REGISTROS SIN ACTIVIDADES
$detallesSinRegistro = $conn->prepare("SELECT odAct_detalle FROM od_actividades WHERE od_id = :id AND odAct_estado = 0 AND odAct_detalle NOT IN (SELECT rd_detalle FROM registros_disenio WHERE od_id = :id AND rd_hora_fin IS NOT NULL)");
$detallesSinRegistro->execute([":id" => $orden["od_id"]]);
$detallesSinRegistro = $detallesSinRegistro->fetchAll(PDO::FETCH_ASSOC);

if (empty($detallesSinRegistro)) {
    // Actualizar el estado de la orden de diseño a "Revisando" (código de estado 4)
    $conn->prepare("UPDATE orden_disenio SET od_estado = 'MATERIALIDAD' WHERE od_id = :id AND od_estado = 'PROPUESTA'")->execute([
        ":id" => $id,
    ]);
    // Registramos el movimiento en el kardex
    registrarEnKardex($_SESSION["user"]["ID_USER"], "PASÓ A MATERIALIDAD", 'ORDEN DISEÑO', "PRODUCTO: " . $orden_diseño["od_producto"]);
}



// Redirigimos a la página de ordenes de diseño
header("Location: ../od.php");
?>
