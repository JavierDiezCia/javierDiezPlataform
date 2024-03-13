<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php"; 

// Verificar si la sesión está iniciada correctamente y el rol es 1 o 2
if (!isset($_SESSION["user"]) && !isset($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2)) {

    // Obtener el ID de la OP desde la URL
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if ($id <= 0) {
        // Manejar error o redirigir a una página de error
        http_response_code(400); // Bad Request
        exit("ID de OP no válido");
    }

    // Usaremos el método GET para buscar el row que vamos a Anular
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

    // Validar que el ID de la OP sea un entero válido
    if ($id <= 0) {
        http_response_code(400); // Bad Request
        exit("ID de OP no válido");
    }

    // Validar la sesión y el rol del usuario
    if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["usu_rol"]) || ($_SESSION["user"]["usu_rol"] != 1 && $_SESSION["user"]["usu_rol"] != 2)) {
        http_response_code(401); // Unauthorized
        exit("Acceso no autorizado");
    }

    // Obtener el ID de usuario de la sesión
    $userId = isset($_SESSION["user"]["cedula"]) ? $_SESSION["user"]["cedula"] : "";

    // Verificar si el usuario tiene permisos para anular la OP
    if ($_SESSION["user"]["usu_rol"] != 1 && $_SESSION["user"]["usu_rol"] != 2) {
        http_response_code(403); // Forbidden
        exit("No tienes permisos para anular la OP");
    }

    // Obtener el ID de la OP desde la URL
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

    // Validar que el ID de la OP sea un entero válido
    if ($id <= 0) {
        http_response_code(400); // Bad Request
        exit("ID de OP no válido");
    }

    // Verificar si la consulta devolvió resultados
    $stament = $conn->prepare("SELECT * FROM op WHERE op_id = :id");
    $stament->bindParam(":id", $id);
    $stament->execute();
    if ($stament->rowCount() == 0) {
        http_response_code(404); // Not Found
        exit("No se encontró la OP");
    }

    // Obtener los registros_empleado que no tienen fecha de finalización y están asociados a los registros de producción
    $registroQuery = $conn->prepare("SELECT *
        FROM registro
        JOIN registro_empleado ON registro.reg_id = registro_empleado.reg_id
        JOIN registro_empleado_actividades AS Re ON registro.reg_id = Re.reg_id
        JOIN produccion ON Re.pro_id = produccion.pro_id
        WHERE produccion.op_id = :id
        AND registro_empleado.reg_fechaFin IS NULL");

    // Validar que el usuario tenga permisos para anular la OP
    if ($_SESSION["user"]["usu_rol"] != 1 && $_SESSION["user"]["usu_rol"] != 2) {
        http_response_code(403); // Forbidden
        exit("No tienes permisos para anular la OP");
    }

    // Update the op table
    $updateOpStatement = $conn->prepare("UPDATE op SET op_estado = 'OP ANULADA', op_fechaFinalizacion = NOW() WHERE op_id = :id");
    $updateOpStatement->bindParam(":id", $id);
    $updateOpStatement->execute();

    // Update the planos table
    $updatePlanoStatement = $conn->prepare("UPDATE planos SET pla_estado = 'ANULADO' WHERE op_id = :id");
    $updatePlanoStatement->bindParam(":id", $id);
    $updatePlanoStatement->execute();

    // Update the registro_empleado table
    $updateRegistroEmpleadoStatement = $conn->prepare("UPDATE registro_empleado SET reg_fechaFin = NOW() WHERE reg_id IN (SELECT reg_id FROM registro WHERE op_id = :id AND reg_fechaFin IS NULL)");
    $updateRegistroEmpleadoStatement->bindParam(":id", $id);
    $updateRegistroEmpleadoStatement->execute();

    // Update the registro table
    $updateRegistroStatement = $conn->prepare("UPDATE registro SET reg_observacion = CONCAT(COALESCE(reg_observacion, ''), ' OP ANULADA') WHERE op_id = :id");
    $updateRegistroStatement->bindParam(":id", $id);
    $updateRegistroStatement->execute();

    //REGISTRAR EL MOVIMIENTO EN EL KARDEX
    registrarEnKardex($userId, "SE HA ANULADO LA OP", 'OP', $id);

    //REDIRIGIR A OPCIONESOP.PHP
    header("Location: ../opcionesOp.php");
    // Finalizamos el código aquí porque ya nos redirige a OPCIONESOP.PHP
    return;
} else {
    // Redirigir a index.php si la sesión no es válida o el rol no es correcto
    header("Location: ../index.php");
    return;
}
