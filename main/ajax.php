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
    $query = "SELECT odAct_id, odAct_detalle, odAct_fechaEntrega FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0";
    $statement = $conn->prepare($query);
    $statement->bindParam(':od_id', $od_id); 
    $statement->execute();
    $actividades = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Formatear los datos como JSON y devolverlos
    echo json_encode($actividades);
} elseif (isset($_GET['odAct_id'])) {
    // Handle the request to get the date
    $odAct_id = $_GET['odAct_id'];

    // Query the database to get the date data
    $statement = $conn->prepare("SELECT odAct_fechaEntrega FROM od_actividades WHERE odAct_id = :odAct_id");
    $statement->bindParam(":odAct_id", $odAct_id);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Check if the query was successful before accessing the array value
    if ($result !== false) {
        // Return the date data as the response
        echo $result['odAct_fechaEntrega'];
    } else {
        // Handle the case where the query was not successful
        echo "NO SE ENCONTRÓ LA FECHA DE ENTREGA.";
    }

}elseif (isset($_POST['area'])) {
    // Manejar la solicitud para obtener las actividades por área
    $area = $_POST['area'];

    // Aquí es donde defines tus arrays de actividades
    $actividades_pintura = array("REVISIÓN OP", "CONFIRMAR COLORES EN LA OP", "SELECCIÓN DE PINTURA SEGÚN MATERIAL", "MASILLAR", "LIJAR", "FONDEADO", "PROTECCIÓN PARA DIVISIÓN DE COLORES", "TERMINADO", "CUARTO DE SECADO", "PINTURA ELECTROESTÁTICA", "ENTREGA JEFE DE PRODUCCIÓN", "APLICACIÓN SELLADOR EN MADERA", "REPINTAR", "APLICACIÓN WASH PREMIER", "APLICACIÓN MONTO", "APLICACIÓN TINTE (MADERA)", "LIMPIEZA");
    $actividades_acrilicos = array("REVISIÓN OP", "REQUERIMIENTO DE MATERIALES", "REDISEÑO DE CORTES Y GRABADO", "DISEÑO DE MATRICES", "ENVÍO A MÁQUINAS (ROUTER/LASE)", "PULIDO DE MATERIAL", "TERMOFORMAR", "SOPLADO", "CORTE DE BASE DE LETRAS", "MDF PINTURA", "SILVATRIM", "SISTEMA ELÉCTRICO", "SELLADOR DE BORDES", "ANCLAJE A BASE", "ENTREGA JEFE DE PRODUCCIÓN", "LIMPIEZA PANERAS", "LIMPIEZA", "TENSADO LONA", "APLICACIÓN VINILOS", "ARMADO LETRAS", "CALADO DE LETRAS");
    $actividades_metal = array("REVISIÓN OP", "REVISIÓN DE MATERIAL", "SOLICITUD DE MATERIAL", "ENVÍO A BAROLAR", "CORTE EN TROZADORA", "DISEÑO EN AUTOCAD DE CORTE ESPECIAL (PLASMA)", "CORTE PLASMA", "CORTE CIZALLA", "PLANTILLA DE ARMADO", "DOBLADORA", "SUELDA MIC", "SUELDA TIC", "SUELDA ALUMINIO", "SUELDA ESTANIO", "PULIDO NORMAL", "PULIDO INOX", "COLOCACIÓN ITEMS ESPECIALES", "MOLDEO", "ENVÍO A PINTURA", "CORTE MANUAL", "LIMPIEZA");
    $actividades_carpinteria = array("RECIBEN OP", "REVISIÓN OP", "DESARROLLO DE MATRICES", "CONFIRMACIÓN DE MEDIDAS Y MATERIAL", "DESPIECE DE ELEMENTOS", "CORTE ESCUADRADORA (SOLO MELAMÍNICO)", "LAMINADORA (SOLO MELAMÍNICO)", "CIERRA DE BRAZO RADIAL", "CIERRA DE BANCO", "PREPARADO DE LOS ELEMENTOS PARA EL MUEBLE", "REMATE 1: LAMINAR MANUALMENTE", "REMATE 2: CORRECCIÓN DE FALLAS", "REMATE 3: PULIR", "LIMPIEZA", "ENTREGA JEFE DE PRODUCCIÓN", "ENTREGA PINTURA (SI LO REQUIERE EL PRODUCTO)", "ENTREGA ACRÍLICO (SI LO REQUIERE EL PRODUCTO)");
    $actividades_acm = array("REVISIÓN OP", "REDISEÑO DE ESTRUCTURAS", "SOLICITUD DE MATERIAL", "SOLICITAR ESTRUCTURAS A METALMECÁNICA", "SOLICITAR CORTE ROUTER", "RANURA PARA DOBLEZ", "TERMINADOS");
    $actividades_maquinas = array();
    // ... y así sucesivamente para cada área

    // Inicializa un array vacío para las actividades
    $actividades = [];

    switch ($area) {
        case 'PINTURA':
            $actividades = $actividades_pintura;
            break;
        case 'ACRÍLICOS Y ACABADOS':
            $actividades = $actividades_acrilicos;
            break;
        case 'CARPINTERÍA':
            $actividades = $actividades_carpinteria;
            break;
        case 'METALMECÁNICA': 
            $actividades = $actividades_metal;
            break;
        case 'ACM':
            $actividades = $actividades_acm;
            break;
        case 'MAQUINAS':
            $actividades = $actividades_maquinas;
            break;

    // Si el array de actividades no está vacío, devuélvelo como respuesta en formato JSON
    if (!empty($actividades)) {
        echo json_encode($actividades);
    } else {
        // Manejar el caso en que no se encontraron actividades para esa área
        echo "NO SE ENCONTRARON ACTIVIDADES PARA ESA ÁREA.";
    }
}
    
} else {
    // Si no se recibió ningún parámetro válido en la solicitud, devolver un mensaje de error
    echo json_encode(array('error' => 'No se recibió ningún parámetro válido'));
}
?>
