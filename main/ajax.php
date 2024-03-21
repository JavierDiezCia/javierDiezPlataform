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
} elseif (isset($_GET['area'])) {
    $area = $_GET['area'];

    // Aquí es donde defines tus arrays de actividades
    $actividades = array(
        "ACM" => array("REVISIÓN OP", "REDISEÑO DE ESTRUCTURAS", "SOLICITUD DE MATERIAL", "SOLICITAR ESTRUCTURAS A METALMECÁNICA", "SOLICITAR CORTE ROUTER", "RANURA PARA DOBLEZ", "TERMINADOS"),
        "ACRÍLICOS Y ACABADOS" => array("REVISIÓN OP", "REQUERIMIENTO DE MATERIALES", "REDISEÑO DE CORTES Y GRABADO", "DISEÑO DE MATRICES", "ENVÍO A MÁQUINAS (ROUTER/LASE)", "PULIDO DE MATERIAL", "TERMOFORMAR", "SOPLADO", "CORTE DE BASE DE LETRAS", "MDF PINTURA", "SILVATRIM", "SISTEMA ELÉCTRICO", "SELLADOR DE BORDES", "ANCLAJE A BASE", "ENTREGA JEFE DE PRODUCCIÓN", "LIMPIEZA PANERAS", "LIMPIEZA", "TENSADO LONA", "APLICACIÓN VINILOS", "ARMADO LETRAS", "CALADO DE LETRAS"),
        "CARPINTERÍA" => array("RECIBEN OP", "REVISIÓN OP", "DESARROLLO DE MATRICES", "CONFIRMACIÓN DE MEDIDAS Y MATERIAL", "DESPIECE DE ELEMENTOS", "CORTE ESCUADRADORA (SOLO MELAMÍNICO)", "LAMINADORA (SOLO MELAMÍNICO)", "CIERRA DE BRAZO RADIAL", "CIERRA DE BANCO", "PREPARADO DE LOS ELEMENTOS PARA EL MUEBLE", "REMATE 1: LAMINAR MANUALMENTE", "REMATE 2: CORRECCIÓN DE FALLAS", "REMATE 3: PULIR", "LIMPIEZA", "ENTREGA JEFE DE PRODUCCIÓN", "ENTREGA PINTURA (SI LO REQUIERE EL PRODUCTO)", "ENTREGA ACRÍLICO (SI LO REQUIERE EL PRODUCTO)"),
        "MAQUINAS" => array(),
        "METALMECÁNICA" => array("REVISIÓN OP", "REVISIÓN DE MATERIAL", "SOLICITUD DE MATERIAL", "ENVÍO A BAROLAR", "CORTE EN TROZADORA", "DISEÑO EN AUTOCAD DE CORTE ESPECIAL (PLASMA)", "CORTE PLASMA", "CORTE CIZALLA", "PLANTILLA DE ARMADO", "DOBLADORA", "SUELDA MIC", "SUELDA TIC", "SUELDA ALUMINIO", "SUELDA ESTANIO", "PULIDO NORMAL", "PULIDO INOX", "COLOCACIÓN ITEMS ESPECIALES", "MOLDEO", "ENVÍO A PINTURA", "CORTE MANUAL", "LIMPIEZA"),
        "PINTURA" => array("REVISIÓN OP", "CONFIRMAR COLORES EN LA OP", "SELECCIÓN DE PINTURA SEGÚN MATERIAL", "MASILLAR", "LIJAR", "FONDEADO", "PROTECCIÓN PARA DIVISIÓN DE COLORES", "TERMINADO", "CUARTO DE SECADO", "PINTURA ELECTROESTÁTICA", "ENTREGA JEFE DE PRODUCCIÓN", "APLICACIÓN SELLADOR EN MADERA", "REPINTAR", "APLICACIÓN WASH PREMIER", "APLICACIÓN MONTO", "APLICACIÓN TINTE (MADERA)", "LIMPIEZA")
    );

    if (isset($actividades[$area])) {
        foreach ($actividades[$area] as $actividad) {
            $id = strtolower(str_replace(" ", "_", $actividad));
            echo '<div class="form-check">';
            echo '<input class="form-check-input" type="checkbox" id="' . $id . '" name="actividades[]" value="' . $actividad . '">';
            echo '<label class="form-check-label" for="' . $id . '">' . $actividad . '</label>';
            echo '</div>';
        }
    }
} elseif (isset($_GET['areaOP'])) {
    $area = $_GET['areaOP'];
    $opQuery = $conn->prepare("SELECT DISTINCT op.op_id 
    FROM op 
    INNER JOIN planos p ON op.op_id = p.op_id 
    INNER JOIN produccion pro ON p.pla_id = pro.pla_id 
    INNER JOIN pro_areas pa ON pro.pro_id = pa.pro_id 
    WHERE pa.proAre_detalle = :area_trabajo
    AND pro.pro_id IS NOT NULL 
    AND pa.proAre_porcentaje < 100
    AND op.op_estado = 'EN PRODUCCION'");

    // Ejecuta la consulta con el parámetro :area_trabajo
    $opQuery->execute(array(':area_trabajo' => $area));

    // Obtiene el número de filas afectadas por la consulta
    $rowCount = $opQuery->rowCount();

    // Si la consulta devuelve al menos una fila, genera el HTML del select con las opciones
    if ($rowCount > 0) {
        echo '<select id="op_id">';
        echo '<option selected disabled value="">Seleccione una orden de producción</option>';
        while ($row = $opQuery->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' . $row['op_id'] . '">' . $row['op_id'] . '</option>';
        }
        echo '</select>';
    } else {
        // Si la consulta no devuelve filas, genera un mensaje indicando que no hay órdenes de producción disponibles
        echo '<select id="op_id">';
        echo '<option selected disabled value="">No hay órdenes de producción disponibles</option>';
        echo '</select>';
    }
} elseif (isset($_GET['areaPlano'])) {
    $area = $_GET['areaPlano'];
    $op_id = $_GET['op_idPlanos']; // Asegúrate de obtener el valor de op_id
    // Consulta SQL para obtener los datos de los planos asociados al área de trabajo del empleado
    $plaQuery = $conn->prepare("SELECT p.pla_id, p.pla_numero, pro.pro_id 
                FROM planos p 
                INNER JOIN produccion pro ON p.pla_id = pro.pla_id 
                INNER JOIN pro_areas pa ON pro.pro_id = pa.pro_id
                WHERE pa.proAre_detalle = :area_trabajo 
                AND pro.pro_id IS NOT NULL 
                AND pa.proAre_porcentaje < 100 
                AND p.pla_estado = 'ACTIVO'
                AND p.op_id = :op_id"); // Agregando la condición para op_id
    // Preparar la consulta
    $plaQuery->execute(array(':area_trabajo' => $area, ':op_id' => $op_id)); // Pasando el valor de op_id

    // Obtener los resultados de la consulta
    $rowCount1 = $plaQuery->rowCount();
    // Si la consulta devuelve al menos una fila, genera el HTML del select con las opciones
    // Si la consulta no devuelve filas, genera un mensaje indicando que no hay órdenes de producción disponibles
    if ($rowCount1 > 0) {
        echo '<select id="pla_id">';
        echo '<option selected disabled value="">Seleccione una orden de producción</option>';
        while ($row1 = $plaQuery->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' . $row1['pla_id'] . '">' . $row1['pla_numero'] . '</option>';
        }
        echo '</select>';
    } else {
        // Si la consulta no devuelve filas, genera un mensaje indicando que no hay órdenes de producción disponibles
        echo '<select id="pla_id">';
        echo '<option selected disabled value="">No hay órdenes de producción disponibles</option>';
        echo '</select>';
    }
} else {
    // Si no se recibió ningún parámetro válido en la solicitud, devolver un mensaje de error
    //echo json_encode(array('error' => 'No se recibió ningún parámetro válido'));
}
