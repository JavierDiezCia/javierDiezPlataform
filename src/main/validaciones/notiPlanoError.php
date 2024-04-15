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
$statement = $conn->prepare("SELECT * FROM planos WHERE pla_id = :id");
$statement->execute([":id" => $id]);

$plano = $statement->fetch(PDO::FETCH_ASSOC);

// Comprobamos que el ID exista, en caso de que el usuario no sea un navegador
if ($statement->rowCount() == 0) {
    http_response_code(404);
    echo("HTTP 404 NOT FOUND");
    return;
}



// Actualizamos el row con el ID de la cédula seleccionada
$conn->prepare("UPDATE planos SET pla_estado = :estado WHERE pla_id = :id")->execute([
    ":id" => $id,
    ":estado" => "ACTIVO",
]);
// Registramos el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["cedula"], "APROBÓ ERROR", 'PLANOS', "<br>OP: " . $plano["op_id"] . "<br>Plano: " . $plano["PLANNUMERO"]);


// Redirigimos a personas.php
header("Location: ../planosError.php");

// Finalizamos el código aquí porque ya nos redirige a personas.php
return;
?>