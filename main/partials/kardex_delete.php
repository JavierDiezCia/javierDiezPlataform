<?php

require "../../sql/database.php";  

function registrarEnKardex($idUser, $accion, $tabla, $row) {
    global $conn;

    try {
        $statement = $conn->prepare("INSERT INTO kardex (kar_cedula, kar_accion, kar_tabla, kar_idRow, kar_fecha) 
                                     VALUES (:idUser, :accion, :tabla, :row, CURRENT TIMESTAMP)");

        $statement->execute([
            ":idUser" => $idUser,
            ":accion" => $accion,
            ":tabla" => $tabla,
            ":row" => $row
        ]);

        return true;  // Indica que el registro en el kardex fue exitoso
    } catch (PDOException $e) {
        // Manejo de errores (puedes loguear el error, mostrar un mensaje de error, etc.)
        // En un entorno de producción, manejar los errores de manera adecuada es crucial
        return false;  // Indica que hubo un error al intentar registrar en el kardex
    }
}

?>
