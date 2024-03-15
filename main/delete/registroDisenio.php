<?php

require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}

if (($_SESSION["user"]["usu_rol"] != 2)) {
    header("Location: ../../index.php");
    return;
}

// Usaremos el método GET para buscar el row que vamos a eliminar
$id = $_GET["id"];

// Primero lo solicitamos a la base de datos
$statement = $conn->prepare("SELECT * FROM registros_disenio WHERE rd_id = :id");
$statement->execute([":id" => $id]);

// Comprobamos que el ID exista, en caso de que el usuario no sea un navegador
if ($statement->rowCount() == 0) {
    http_response_code(404);
    echo("HTTP 404 NOT FOUND");
    return;
}

// Actualizamos el row con el ID de la cédula seleccionada
$conn->prepare("UPDATE registros_disenio SET rd_delete = :estado WHERE rd_id = :id")->execute([
    ":id" => $id,
    ":estado" => 1,
]);
// Registramos el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["cedula"], "ELIMINÓ", 'REGISTRO', $id);


// Redirigimos a personas.php
header("Location: ../historialRegistros.php");

// Finalizamos el código aquí porque ya nos redirige a personas.php
return;
?>
