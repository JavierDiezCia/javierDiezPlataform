<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
require "../partials/session_handler.php";
// Verificar si la sesión está iniciada correctamente y el rol es 1, 2 o 3
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3)) {
    // Obtener el ID del plano desde la URL
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    // Obtener la observación del cuerpo de la solicitud POST
    $observacion = isset($_POST["observacion"]) ? $_POST["observacion"] : '';

    // Primero solicitamos a la base de datos
    $stament = $conn->prepare("SELECT * FROM planos WHERE pla_id = :id");
    $stament->execute([":id" => $id]);

    if ($id <= 0) {
        // Manejar error o redirigir a una página de error
        http_response_code(400); // Bad Request
        exit("ID de Plano no válido");
    } elseif ($stament->rowCount() == 0) {
        // Si no se encuentra el plano, redirigir o manejar el error según corresponda
        http_response_code(404);
        header("Location: ../pages-error-404.html");
        return;
    } elseif (empty($observacion)) {
        // Redirigir a la página anterior con un mensaje de error
        header("Location: " . $_SERVER["HTTP_REFERER"] . "?error=Observación requerida");
        exit; // Agrega exit para evitar que se ejecute el resto del código
    } else {
        // Obtener el resultado de la consulta
        $row = $stament->fetch(PDO::FETCH_ASSOC);

        // Actualizar el estado del plano
        $conn->prepare("UPDATE planos SET pla_estado = :estado WHERE pla_id = :id")->execute([
            ":id" => $id,
            ":estado" => "ANULADO"
        ]);
        // Registrar la observación en la tabla pla_observaciones
        $conn->prepare("INSERT INTO pla_observaciones (pla_id, plaOb_estado, plaOb_obsevacion, plaOb_fecha) VALUES (:id, :estado, :observacion, CURRENT_TIMESTAMP)")->execute([
            ":id" => $id,
            ":estado" => "ANULADO",
            ":observacion" => $observacion
        ]);
        // Aquí comienza la adición
        $registroQuery = $conn->prepare("SELECT *
        FROM registro
        JOIN registro_empleado ON registro.reg_id = registro_empleado.reg_id
        JOIN produccion ON registro.pro_id = produccion.pro_id
        WHERE produccion.pla_id = :id
         AND registro_empleado.reg_fechaFin IS NULL ");

        $registroQuery->execute(array(':id' => $id));
        $registros = $registroQuery->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as $registro) {
            $statement = $conn->prepare("UPDATE registro_empleado SET reg_fechaFin = CURRENT_TIMESTAMP WHERE reg_id = :id");
            $statement->execute([":id" => $registro["reg_id"]]);

            $statement1 = $conn->prepare("UPDATE registro SET reg_observacion = :observaciones WHERE reg_id = :id");
            $statement1->execute([
                ":observaciones" => "SE ANULO EL PLANO",
                ":id" => $registro["reg_id"]
            ]);
        }
        header("Location: ../planos.php");
        return;
    }

    // Registrar el movimiento en el kardex
    registrarEnKardex($_SESSION["user"]["cedula"], "ANULAR  EL PLANO", 'PLANO', $id, $observacion);

    // Redirigir a planos.php
    header("Location: ../planos.php");
    // Finalizar el código aquí porque ya nos redirige a planos.php
    return;
} else {
    // Redirigir a index.php si la sesión no es válida o el rol no es correcto
    header("Location: ../index.php");
    return;
}
