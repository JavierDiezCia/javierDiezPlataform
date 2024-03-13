<?php
require "../../sql/database.php"; // Incluir archivo de configuración de la base de datos
require "../partials/kardex.php"; // Incluir otros archivos necesarios
require "../../exel/vendor/autoload.php"; // Incluir la biblioteca PhpSpreadsheet
require "../partials/session_handler.php"; 

 // Iniciar sesión

// Si la sesión no existe, redirigir al formulario de inicio de sesión y salir del script
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet; // Importar la clase Spreadsheet
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Importar la clase Xlsx para escribir en formato Excel
use PhpOffice\PhpSpreadsheet\IOFactory; // Importar la clase IOFactory para manejar la entrada y salida

if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["ROL"]) || ($_SESSION["user"]["ROL"] == 1 || $_SESSION["user"]["ROL"] == 2)) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    // Consulta SQL para obtener datos de la base de datos
    $sql = "SELECT op.*, 
                    orden.od_responsable,
                    responsable.per_nombres AS responsable_nombres,
                    responsable.per_apellidos AS responsable_apellidos,
                    orden.od_comercial,
                    comercial.per_nombres AS comercial_nombres,
                    comercial.per_apellidos AS comercial_apellidos,
                    orden.od_detalle,
                    orden.od_cliente
            FROM op
            LEFT JOIN orden_disenio AS orden ON op.od_id = orden.od_id
            LEFT JOIN personas AS responsable ON orden.od_responsable = responsable.cedula
            LEFT JOIN personas AS comercial ON orden.od_comercial = comercial.cedula";

    // Ejecutar la consulta y obtener el resultado
    $resultado = $conn->query($sql);

    // Verificar si la consulta se ejecutó correctamente
    if (!$resultado) {
        die("Error en la consulta: " . $conn->errorInfo()[2]); // Mostrar mensaje de error y terminar el script
    }
    date_default_timezone_set('America/Lima');
    // Crear una instancia de PhpSpreadsheet
    $excel = new Spreadsheet();
    //CARGAR IMAGENE
    $imgPath = '../../exel/logo_icon.jpeg'; //ruta de la Imagen
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath($imgPath);
    $drawing->setHeight(70); // Establecer la altura de la imagen
    $drawing->setWidth(70); // Establecer el ancho de la imagen

    // Añadir la imagen al archivo de Excel
    $drawing->setWorksheet($excel->getActiveSheet());

    // Seleccionar la hoja activa y establecer su título
    $hojaActiva = $excel->getActiveSheet();
    $hojaActiva->setTitle("Reporte de las Op");
    $hojaActiva->setCellValue('C3', 'FECHA DEL REPORTE');
    $hojaActiva->setCellValue('C2', 'REPORTE GENERADO POR');
    $hojaActiva->getStyle('C2:C3')->getFont()->setBold(true)->setSize(13);
    // Obtener la cédula del usuario actualmente logueado
    $cedulaUsuario = $_SESSION["user"]["cedula"];

    // Consultar la base de datos para obtener los nombres y apellidos asociados a la cédula
    $sqlUsuario = "SELECT per_nombres, per_apellidos FROM personas WHERE cedula = :cedulaUsuario";
    $stmt = $conn->prepare($sqlUsuario);
    $stmt->bindParam(':cedulaUsuario', $cedulaUsuario);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si se encontraron resultados
    if ($usuario) {
        // Obtener nombres y apellidos del usuario
        $nombresUsuario = $usuario['per_nombres'];
        $apellidosUsuario = $usuario['per_apellidos'];

        // Mostrar los nombres y apellidos del usuario en la celda D3
        $hojaActiva->setCellValue('D2', $nombresUsuario . ' ' . $apellidosUsuario);
    } else {
        // En caso de no encontrar resultados, mostrar un mensaje alternativo
        $hojaActiva->setCellValue('D2', 'Usuario no encontrado');
    }
    // Obtener la fecha y hora actual
    $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

    // Añadir la fecha y hora actual en la celda D4
    $hojaActiva->setCellValue('D3', $fechaHoraActual);
    $hojaActiva->getStyle('D2:D3')->getFont()->setBold(true)->setSize(13);
    // Establecer encabezados de columnas
    $hojaActiva->setCellValue('A6', 'OP');
    $hojaActiva->setCellValue('B6', 'CLIENTE');
    $hojaActiva->setCellValue('C6', 'CIUDAD');
    $hojaActiva->setCellValue('D6', 'DETALLE');
    $hojaActiva->setCellValue('E6', 'FECHA REGISTRO');
    $hojaActiva->setCellValue('F6', 'FECHA NOTIFICACION POR CORREO');
    $hojaActiva->setCellValue('G6', 'DISEÑADOR');
    $hojaActiva->setCellValue('H6', 'VENDEDOR');
    $hojaActiva->setCellValue('I6', 'DIRRECION DEL LOCAL');
    $hojaActiva->setCellValue('J6', 'PERSONA DE CONTACTO');
    $hojaActiva->setCellValue('K6', 'TELEFONO');
    $hojaActiva->setCellValue('L6', 'REPROSESO');
    $hojaActiva->setCellValue('M6', 'ESTADO');
    $hojaActiva->setCellValue('N6', 'FECHA DE FINALIZACION');

    // Obtener el número de filas inicial para los datos
    $fila = 7;

    // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
    while ($rows = $resultado->fetch(PDO::FETCH_ASSOC)) {
        // Convertir el número del estado a texto según diferentes casos
        //CONVERTIR DE NUMERO A LETRAS EN REPROSESO
        switch ($rows['op_reproceso']) {
            case 0:
                $reproseso = '';
                break;
            case 1:
                $reproseso = 'ES UN REPROSESO';
                break;
            default:
                $reproseso = 'REPROSEOS DESCONOCIDO';
        }

        // Obtener el estado
        $estado = $rows['op_estado'];

        // Agregar datos a las celdas
        $hojaActiva->setCellValue('A' . $fila, $rows['op_id']);
        $hojaActiva->setCellValue('B' . $fila, $rows['od_cliente']);
        $hojaActiva->setCellValue('C' . $fila, $rows['op_ciudad']);
        $hojaActiva->setCellValue('D' . $fila, $rows['od_detalle']);
        $hojaActiva->setCellValue('E' . $fila, $rows['op_registro']);
        $hojaActiva->setCellValue('F' . $fila, $rows['op_notiProFecha']);
        $hojaActiva->setCellValue('G' . $fila, $rows['responsable_nombres'] . ' ' . $rows['responsable_apellidos']);
        $hojaActiva->setCellValue('H' . $fila, $rows['comercial_nombres'] . ' ' . $rows['comercial_apellidos']);
        $hojaActiva->setCellValue('I' . $fila, $rows['op_direccionLocal']);
        $hojaActiva->setCellValue('J' . $fila, $rows['op_personaContacto']);
        $hojaActiva->setCellValue('K' . $fila, $rows['op_telefono']);
        $hojaActiva->setCellValue('L' . $fila, $reproseso);
        $hojaActiva->setCellValue('M' . $fila, $estado);
        $hojaActiva->setCellValue('N' . $fila, $rows['op_fechaFinalizacion']);

        // Establecer el estilo para toda la fila con estado "OP ANULADA"
        if ($rows['op_estado'] == 'OP ANULADA') {
            $range = 'A' . $fila . ':N' . $fila; // Rango de celdas para la fila actual
            $hojaActiva->getStyle($range)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF0000'], // Color rojo
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'], // Color de fuente blanco
                ],
            ]);
        }

        // Establecer estilos de la fila 6
        $hojaActiva->getStyle('A6:N6')->applyFromArray([
            'font' => [
                'bold' => true, // Negrita
                'size' => 14,   // Tamaño de letra 14
                'color' => ['rgb' => 'FFFFFF'], // Color de fuente blanco
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0000FF'], // Color de relleno azul
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Centrado horizontal
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Centrado vertical
            ],
        ]);

        // Establecer el alto de la fila 6
        $hojaActiva->getRowDimension('6')->setRowHeight(70);

        $fila++;
    }

    // Establecer estilos y ajustes de tamaño de celdas
    $hojaActiva->getStyle('A6:N' . $fila)->getAlignment()->setWrapText(true); // Activar el ajuste de texto en las celdas
    $hojaActiva->getStyle('A6:N' . $fila)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Centrar verticalmente el texto en las celdas

    // Ajustar automáticamente el tamaño de las columnas y filas
    foreach (range('A', 'N') as $columnID) {
        $hojaActiva->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Agregar bordes a las celdas
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'], // Color del borde (en este caso, negro)
            ],
        ],
    ];

    $hojaActiva->getStyle('A6:N' . $fila)->applyFromArray($styleArray);


    // Crear un objeto Writer para Xlsx
    $writer = new Xlsx($excel);

    // Establecer las cabeceras para forzar la descarga del archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporteOP.xlsx"');
    header('Cache-Control: max-age=0');

    // Guardar el archivo en la salida (output)
    $writer->save('php://output');
    // Registrar el movimiento en el kardex
    registrarEnKardex($_SESSION["user"]["cedula"], "Se a generado un reporte", 'OP', "Reporte");

    exit;
} else {
    header("Location: ../index.php");
    return;
}
