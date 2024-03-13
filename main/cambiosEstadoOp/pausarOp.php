<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php"; 



// Verificar si la sesión está iniciada correctamente y el rol es 1 o 2
if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["usu_rol"]) || ($_SESSION["user"]["usu_rol"] == 1)) {
    // Obtener el ID de la OP desde la URL
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if ($id <= 0) {
        // Manejar error o redirigir a una página de error
        http_response_code(400); // Bad Request
        exit("ID de OP no válido");
    }

    // Consultar la información de la OP en la base de datos
    $statement = $conn->prepare("SELECT * FROM op WHERE op_id = :id");
    $statement->execute([":id" => $id]);

    // Verificar si la consulta devolvió resultados
    if ($statement->rowCount() == 0) {
        // Si no se encuentra la OP, redirigir o manejar el error según corresponda
        http_response_code(404); // Not Found
        header("Location: ../pages-error-404.html");
        exit;
    }

    // Obtener el resultado de la consulta
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    // Actualizar el estado de la OP
    $conn->prepare("UPDATE op SET op_estado = :estado WHERE op_id = :id")->execute([

        ":id" => $id,
        ":estado" => "OP PAUSADA"
    ]);
    
    
    

    // Registrar el movimiento en el kardex
    registrarEnKardex($_SESSION["user"]["cedula"], "SE HA PAUSADO UNA OP", 'OP', $id);

     //RERIDIRIGIR A OPCIONESOP.PHP
     header("Location: ../opcionesOp.php");

    return;
} else {
    // Redirigir a index.php si la sesión no es válida o el rol no es correcto
    header("Location: ../index.php");
    return;
}
