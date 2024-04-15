<?php

require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}

// Usaremos el método GET para buscar el row que vamos a eliminar
$id = $_GET["id"];

// Primero lo solicitamos a la base de datos
$statement = $conn->prepare("SELECT op.*, 
                                    (SELECT COUNT(*) FROM planos WHERE op_id = :id) AS total_planos
                             FROM op 
                             WHERE op_id = :id");
$statement->execute([":id" => $id]);

// Verificar si la consulta devolvió resultados
if ($statement->rowCount() == 0) {
    // Si no se encuentra la OP, redirigir o manejar el error según corresponda
    http_response_code(404);
    header("Location: ../pages-error-404.html");
    return;
}

// Obtener el resultado de la consulta
$row = $statement->fetch(PDO::FETCH_ASSOC);

// Verificar si la OP tiene planos asociados
if ($row['total_planos'] == 0) {
    // Si no hay planos asociados, redirigir o manejar el error según corresponda
    http_response_code(404);
    header("Location: ../pages-error-404.html");
    return;
}



// Actualizamos el row con el ID de la cédula seleccionada
$conn->prepare("UPDATE op SET op_estado = :estado, op_notiProFecha = CURRENT_TIMESTAMP WHERE op_id = :id")->execute([
    ":id" => $id,
    ":estado" => "EN PRODUCCION",
]);
// Registramos el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["cedula"], "NOTIFICÓ POR CORREO A PRODUCCIÓN", 'OP', $id);


// Redirigimos a personas.php
header("Location: ../op.php");

// Finalizamos el código aquí porque ya nos redirige a personas.php
return;
?>
