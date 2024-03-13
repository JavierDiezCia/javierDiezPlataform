<?php
require "../sql/database.php";

if (isset($_GET['cedula'])) {
    // Manejar la solicitud para obtener la cédula por cédula
    $cedula = $_GET['cedula'];

    // Realizar la consulta a la base de datos para obtener el per_nombres del trabajador
    $statement = $conn->prepare("SELECT per_nombres, per_apellidos FROM personas WHERE cedula = :cedula");
    $statement->bindParam(":cedula", $cedula);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la consulta fue exitosa antes de acceder a los valores del array
    if ($result !== false) {
        // Devuelve el per_nombres del trabajador como respuesta
        echo $result['per_nombres'] . ' ' . $result['per_apellidos'];
    } else {
        // Manejar el caso en que la consulta no fue exitosa
        echo "NO SE ENCONTRÓ TRABAJADOR CON ESA CÉDULA.";
    }
} elseif (isset($_GET['nombres'])) {
    // Manejar la solicitud para obtener la cédula por nombres
    $nombres = $_GET['nombres'];

    // Realizar la consulta a la base de datos para obtener la cedula del trabajador
    $statement = $conn->prepare("SELECT cedula FROM personas WHERE per_nombres = :nombres");
    $statement->bindParam(":nombres", $nombres);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la consulta fue exitosa antes de acceder a los valores del array
    if ($result !== false) {
        // Devuelve la cédula del trabajador como respuesta
        echo $result['cedula'];
    } else {
        // Manejar el caso en que la consulta no fue exitosa
        echo "NO SE ENCONTRÓ TRABAJADOR CON ESE NOMBRE.";
    }
} elseif (isset($_GET['op'])) {
    // Manejar la solicitud para obtener el cliente de la OP
    $op = $_GET['op'];

    // Realizar la consulta a la base de datos para obtener el cliente de la OP
    $statement = $conn->prepare("SELECT op_cliente FROM op WHERE op_id = :op");
    $statement->bindParam(":op", $op);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la consulta fue exitosa antes de acceder a los valores del array
    if ($result !== false) {
        // Devuelve el cliente de la OP como respuesta
        echo $result['op_cliente'];
    } else {
        // Manejar el caso en que la consulta no fue exitosa
        echo "NO SE ENCONTRÓ EL CLIENTE CON LA OP INGRESADA";
    }
} elseif (isset($_GET['op_id'])) {
    // Manejar la solicitud para obtener los planos asociados a la OP seleccionada
    $op_id = $_GET['op_id'];

    // Consulta para obtener los planos asociados a la OP seleccionada
    $query = "SELECT pla_id, pla_numero FROM planos WHERE op_id = :op_id AND pla_estado = 'ACTIVO'";
    $statement = $conn->prepare($query);
    $statement->bindParam(':op_id', $op_id, PDO::PARAM_INT);
    $statement->execute();

    // Obtener los resultados como un array asociativo
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados como JSON
    echo json_encode($result);
} elseif (isset($_POST["od_id"])) {
    $od_id = $_POST["od_id"];

    // Consulta SQL para obtener las actividades basadas en el od_id seleccionado
    $query = "SELECT id, odAct_detalle, odAct_fechaEntrega FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0";
    $statement = $conn->prepare($query);
    $statement->bindParam(':od_id', $od_id); 
    $statement->execute();
    $actividades = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Formatear los datos como JSON y devolverlos
    echo json_encode($actividades);
} else {
    // Si no se recibió ningún parámetro válido en la solicitud, devolver un mensaje de error
    echo json_encode(array('error' => 'No se recibió ningún parámetro válido'));
}
?>
