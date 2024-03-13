<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
if (($_SESSION["user"]["usu_rol"] != 2)) {
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

// Actualizar el estado de la orden de diseño a "Revisando" (código de estado 4)
$conn->prepare("UPDATE orden_disenio SET od_estado = 'PROPUESTA' WHERE od_id = :id")->execute([
    ":id" => $id,
]);

// Registramos el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["cedula"], "APROBÓ", 'ORDEN DISEÑO', "Producto: " . $orden_diseño["od_producto"]);

// Redirigimos a la página de ordenes de diseño
header("Location: ../historialOd.php");
?>
