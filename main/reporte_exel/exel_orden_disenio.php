<?php
require "../../sql/database.php"; // Incluir archivo de configuración de la base de datos
require "../partials/kardex.php"; // Incluir otros archivos necesarios
require "../../exel/vendor/autoload.php"; // Incluir la biblioteca PhpSpreadsheet
require "../partials/session_handler.php"; 

session_start(); // Iniciar sesión

// Si la sesión no existe, redirigir al formulario de inicio de sesión y salir del script
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet; // Importar la clase Spreadsheet
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Importar la clase Xlsx para escribir en formato Excel
use PhpOffice\PhpSpreadsheet\IOFactory; // Importar la clase IOFactory para manejar la entrada y salida

// Verificar el rol del usuario y si tiene permiso para generar el reporte
if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["ROL"]) || ($_SESSION["user"]["ROL"] == 1 || $_SESSION["user"]["ROL"] == 2)) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    // Obtener el rd_id de la orden de diseño desde la URL
    if (isset($_GET["id"])) {
        date_default_timezone_set('America/Lima');

        // Crear una instancia de PhpSpreadsheet
        $excel = new Spreadsheet();
        //CARGAR IMAGENE
        $imgPath = '../../exel/logo_icon.jpeg'; //ruta de la Imagen
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($imgPath);
        $drawing->setHeight(110); // Establecer la altura de la imagen
        $drawing->setWidth(110); // Establecer el ancho de la imagen

        // Añadir la imagen al archivo de Excel
        $drawing->setWorksheet($excel->getActiveSheet());

        // Seleccionar la hoja activa y establecer su título
        $hojaActiva = $excel->getActiveSheet();
        // Seleccionar la hoja activa y establecer su título
        $hojaActiva = $excel->getActiveSheet();
        $hojaActiva->setTitle("Reporte de la Orden de Diseño");
        $hojaActiva->setCellValue('C3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaActiva->setCellValue('C2', 'REPORTE GENERADO POR');
        $hojaActiva->getStyle('C2:C3')->getFont()->setBold(true)->setSize(13);

        // Obtener la cédula del usuario actualmente logueado
        $cedulaUsuario = $_SESSION["user"]["cedula"];

        // Consultar la base de datos para obtener los nombres y apellidos asociados a la cédula
        $sqlUsuario = "SELECT per_nombres, per_apellidos FROM PERSONAS WHERE CEDULA = :cedulaUsuario";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bindParam(':cedulaUsuario', $cedulaUsuario);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

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

        // Obtener el rd_id de la orden de diseño desde la URL
        $id_orden_disenio = $_GET["id"];

        // Consultar la base de datos para obtener la información de la orden de diseño
        $sql = "SELECT od.*, P.per_nombres, P.per_apellidos 
                FROM orden_disenio  AS od 
                JOIN personas AS P ON od.od_responsable = P.cedula
                WHERE od_id = :idorden";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idorden', $id_orden_disenio);
        $stmt->execute();
        $orden_disenio = $stmt->fetch(PDO::FETCH_ASSOC);

        // Añadir datos de la orden de diseño a la hoja de cálculo
        $hojaActiva->setCellValue('C6', 'NÚMERO DE ORDEN DE DISEÑO:');
        $hojaActiva->setCellValue('C7', 'DETALLE');
        $hojaActiva->setCellValue('C8', 'RESPONSABLE');
        $hojaActiva->setCellValue('C9', 'CLIENTE');
        $hojaActiva->setCellValue('C10', 'ESTADO');
        

        $hojaActiva->setCellValue('D6', $orden_disenio['od_id']);
        $hojaActiva->setCellValue('D7', $orden_disenio['od_detalle']);
        $hojaActiva->setCellValue('D8', $orden_disenio['per_nombres'] . ' ' . $orden_disenio['per_apellidos']);
        $hojaActiva->setCellValue('D9', $orden_disenio['od_cliente']);
       
        $hojaActiva->setCellValue('D10', $orden_disenio['od_estado']);

        $registro = "SELECT R.*, O.od_cliente, P.per_nombres, P.per_apellidos
                                FROM registros_disenio R 
                                JOIN orden_disenio O ON R.od_id = O.od_id 
                                JOIN personas P ON R.rd_diseniador = P.cedula
                                WHERE R.od_id = :id
                                ORDER BY R.rd_id DESC";
        $stmtRegi = $conn->prepare($registro);
        $stmtRegi->bindParam(':id', $id_orden_disenio);
        $stmtRegi->execute();
        // Establecer encabezados de columnas
        $hojaActiva->setCellValue('A13', 'N0');
        $hojaActiva->setCellValue('B13', 'DISEÑADOR');
        $hojaActiva->setCellValue('C13', 'FECHA HORA INICIO.');
        $hojaActiva->setCellValue('D13', 'FECHA  HORA FINAL.');
        $hojaActiva->setCellValue('E13', 'ACTIVIDAD');
        $hojaActiva->setCellValue('F13', 'OBSERVACION');
        $hojaActiva->setCellValue('G13', 'ESTADO');
        // Obtener el número de filas inicial para los datos
        $fila = 14;
        $contador = 1; // Inicializar el contador

        // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
        while ($rows = $stmtRegi->fetch(PDO::FETCH_ASSOC)) {
            $hojaActiva->setCellValue('A' . $fila, $contador); // Utilizar el contador en lugar de ++$fila
            $hojaActiva->setCellValue('B' . $fila, $rows['per_nombres'] . ' ' . $rows['per_apellidos']);
            $hojaActiva->setCellValue('C' . $fila, $rows['rd_hora_ini']);
            $hojaActiva->setCellValue('D' . $fila, $rows['rd_hora_fin']);
            $hojaActiva->setCellValue('E' . $fila, $rows['rd_detalle']);
            $hojaActiva->setCellValue('F' . $fila, $rows['rd_observaciones']);
            $hojaActiva->setCellValue('G' . $fila, ($rows["rd_diseniador"] == $orden_disenio["od_responsable"]) ? 'RESPONSABLE' : 'COLABORADOR');
            // Establecer estilos de la fila 6
            $hojaActiva->getStyle('A13:G13')->applyFromArray([
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
            $hojaActiva->getRowDimension('13')->setRowHeight(70);

            $fila++;
            $contador++; // Incrementar el contador
        }

        // Ajustar automáticamente el tamaño de las columnas y filas
        foreach (range('A', 'G') as $columnID) {
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

        $hojaActiva->getStyle('A13:G' . $fila)->applyFromArray($styleArray);
        $hojaActiva->getStyle('C2:D3')->applyFromArray($styleArray);
        $hojaActiva->getStyle('C6:D10')->applyFromArray($styleArray);
        // Guardar el archivo de Excel y enviarlo como descarga
        $writer = new Xlsx($excel);

        // Establecer las cabeceras para forzar la descarga del archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_DE_LA_ORDEN_DE_DISEÑO.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');

        // Registrar el movimiento en el kardex
        registrarEnKardex($_SESSION["user"]["cedula"], "Se ha generado un reporte de la orden de diseño", 'ORDEN DE DISEÑO', "Reporte");

        exit;
    } else {
        // Si no se proporciona un ID de orden de diseño, redirigir al usuario
        header("Location:../index.php");
        return;
    }
} else {
    // Si el usuario no tiene permisos para generar el reporte, redirigirlo
    header("Location:../index.php");
    return;
}
