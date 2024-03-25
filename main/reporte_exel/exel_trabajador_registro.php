<?php
require "../../sql/database.php"; // Incluir archivo de configuración de la base de datos
require "../partials/kardex.php"; // Incluir otros archivos necesarios
require "../../exel/vendor/autoload.php"; // Incluir la biblioteca PhpSpreadsheet


// Iniciar sesión
session_start();

// Si la sesión no existe, redirigir al formulario de inicio de sesión y salir del script
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet; // Importar la clase Spreadsheet
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Importar la clase Xlsx para escribir en formato Excel
use PhpOffice\PhpSpreadsheet\IOFactory; // Importar la clase IOFactory para manejar la entrada y salida
if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["ROL"]) || ($_SESSION["user"]["ROL"] == 1)) {
    if (isset($_GET['year']) && isset($_GET['month']) && isset($_GET['area'])) {
        date_default_timezone_set('America/Lima');
        // Obtén los valores de los parámetros GET
        $year = $_GET['year'];
        $month = $_GET['month'];
        $area = $_GET['area'];
        //consulta en la base de datos
        $sql = "SELECT r.*, pe.per_nombres AS nombre,
                                        pe.per_apellidos AS apellido,
                                        pl.pla_numero,
                                        re.reg_areaTrabajo,
                                        re.reg_fechaFin,
                                        pe.per_areaTrabajo
                                        FROM registro AS r
                                        JOIN registro_empleado AS re ON r.reg_id = re.reg_id
                                        JOIN personas AS pe ON r.reg_cedula = pe.cedula
                                        JOIN planos AS pl ON r.pla_id = pl.pla_id
                                        WHERE MONTH(r.reg_fecha) = :month 
                                        AND YEAR(r.reg_fecha) = :year
                                        AND re.reg_areaTrabajo = :area_trabajo";
        //preparar la consulta po prarmetros
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":month", $month);
        $stmt->bindParam(":year", $year);
        $stmt->bindParam(":area_trabajo", $area);
        // Execute the query
        $stmt->execute();
        // Verificar si la consulta se ejecutó correctamente
        if (!$stmt) {
            die("Error en la consulta: " . $conn->errorInfo()[2]); // Mostrar mensaje de error y terminar el script
        }

        // Crear una instancia de PhpSpreadsheet
        $excel = new Spreadsheet();
        //CARGAR IMAGEN
        $imgPath = '../../exel/logo_icon.jpeg'; //ruta de la Imagen
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($imgPath);
        $drawing->setHeight(120); // Establecer la altura de la imagen
        $drawing->setWidth(120); // Establecer el ancho de la imagen

        // Añadir la imagen al archivo de Excel
        $drawing->setWorksheet($excel->getActiveSheet());

        // Seleccionar la hoja activa y establecer su título
        $hojaActiva = $excel->getActiveSheet();
        $hojaActiva->setTitle("Registros del area " . $area);
        $hojaActiva->setCellValue('C3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaActiva->setCellValue('C2', 'REPORTE GENERADO POR');
        $hojaActiva->setCellValue('C4', 'EL REPORTE ES DE LA FECHA');
        $hojaActiva->setCellValue('E4', $year . ' - ' . $month);
        $hojaActiva->getStyle('C2:E4')->getFont()->setBold(true)->setSize(13);

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
            $hojaActiva->setCellValue('E2', $nombresUsuario . ' ' . $apellidosUsuario);
        } else {
            // En caso de no encontrar resultados, mostrar un mensaje alternativo
            $hojaActiva->setCellValue('E2', 'Usuario no encontrado');
        }

        // Obtener la fecha y hora actual
        $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

        // Añadir la fecha y hora actual en la celda D4
        $hojaActiva->setCellValue('E3', $fechaHoraActual);
        //ESTABLECER LOS ENCABEZADOS DE LA TABLA
        $hojaActiva->setCellValue('A6', 'N0.');
        $hojaActiva->setCellValue('B6', 'TRABAJADOR');
        $hojaActiva->setCellValue('C6', 'OP.');
        $hojaActiva->setCellValue('D6', 'PLANO.');
        $hojaActiva->setCellValue('E6', 'AREA.');
        $hojaActiva->setCellValue('F6', 'FECHA HORA INICIO.');
        $hojaActiva->setCellValue('G6', 'FECHA  HORA FINAL.');
        $hojaActiva->setCellValue('H6', 'TIEMPO.');
        $hojaActiva->setCellValue('I6', 'OBSERVACION.');
        // Obtener el número de filas inicial para los datos
        $fila = 7;
        // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hojaActiva->setCellValue('B' . $fila, $rows['nombre'] . ' ' . $rows['apellido']);
            $hojaActiva->setCellValue('C' . $fila, $rows['op_id']);
            $hojaActiva->setCellValue('D' . $fila, $rows['pla_numero']);
            $hojaActiva->setCellValue('E' . $fila, $rows['per_areaTrabajo'] === $rows['reg_areaTrabajo'] ? 'Pertenece' : 'Apoyo');
            $hojaActiva->setCellValue('F' . $fila, $rows['reg_fecha']);
            $hojaActiva->setCellValue('G' . $fila, $rows['reg_fechaFin']);

            // Calcular la diferencia de tiempo entre la fecha de inicio y la fecha final
            $inicio = strtotime($rows['reg_fecha']);
            $fin = strtotime($rows['reg_fechaFin']);
            $diferencia = $fin - $inicio;
            $horas = floor($diferencia / 3600);
            $minutos = floor(($diferencia % 3600) / 60);
            $segundos = $diferencia % 60;
            // Construir el tiempo en un formato legible
            $tiempo = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

            // Asignar el tiempo a la columna correspondiente
            $hojaActiva->setCellValue('H' . $fila, $tiempo);

            $hojaActiva->setCellValue('I' . $fila, $rows['reg_observacion']);
            // Establecer estilos de la fila 6
            $hojaActiva->getStyle('A6:I6')->applyFromArray([
                'font' => [
                    'bold' => true, // Negrita
                    'size' => 14,   // Tamaño de letra 14
                    'color' => ['rgb' => 'FFFFFF'], // Color de fuente blanco
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '000000'], // Color de relleno negro
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
        // Finalmente, ajusta el índice de la hoja activa
        $excel->setActiveSheetIndex(0); // Puedes ajustar el índice según sea necesario

        // Guardar el archivo de Excel y enviarlo como descarga
        $writer = new Xlsx($excel);

        // Establecer las cabeceras para forzar la descarga del archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_DE_LOS_REGISTROS_DE_TRABAJADORES.xlsx"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo en la salida (output)
        $writer->save('php://output');

        // Registrar el movimiento en el kardex
        registrarEnKardex($_SESSION["user"]["cedula"], "Se a generado un reporte", 'REGISTROS TRABAJADORES', "Reporte");

        exit;
    } else {
        // Si no se proporcionan los parámetros GET, redirigir al usuario
        header("Location:../index.php");
        return;
    }
} else {
    // Si el usuario no tiene permisos para generar el reporte, redirigirlo
    header("Location:../index.php");
    return;
}
