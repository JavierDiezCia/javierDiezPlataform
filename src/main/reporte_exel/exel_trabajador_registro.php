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
        $hojaActiva->setCellValue('F4', $year . ' - ' . $month);
        $hojaActiva->getStyle('C2:F4')->getFont()->setBold(true)->setSize(13);

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
            $hojaActiva->setCellValue('F2', $nombresUsuario . ' ' . $apellidosUsuario);
        } else {
            // En caso de no encontrar resultados, mostrar un mensaje alternativo
            $hojaActiva->setCellValue('F2', 'Usuario no encontrado');
        }

        // Obtener la fecha y hora actual
        $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

        // Añadir la fecha y hora actual en la celda D4
        $hojaActiva->setCellValue('F3', $fechaHoraActual);
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
        $contador = 1; // Inicializar el contador
        // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hojaActiva->setCellValue('A' . $fila, $contador); // Utilizar el contador en lugar de ++$fila
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
            $contador++; // Incrementar el contador
        }
        // Establecer estilos y ajustes de tamaño de celdas
        $hojaActiva->getStyle('A6:I' . $fila)->getAlignment()->setWrapText(true); // Activar el ajuste de texto en las celdas
        $hojaActiva->getStyle('A6:I' . $fila)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Centrar verticalmente el texto en las celdas

        // Centrar horizontalmente el texto en las filas 2 y 3
        $hojaActiva->getStyle('2:4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaActiva->getStyle('2:4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


        $styleArray2 = [
            'font' => [
                'bold' => false,
            ],
        ];
        $hojaActiva->getStyle('D2:E4')->applyFromArray($styleArray2);
        // Establecer el alto de las filas 2 y 3
        $hojaActiva->getRowDimension('2')->setRowHeight(30);
        $hojaActiva->getRowDimension('3')->setRowHeight(30);
        $hojaActiva->getRowDimension('4')->setRowHeight(30);
        $hojaActiva->getColumnDimension('B')->setWidth(30);
        $hojaActiva->getColumnDimension('C')->setWidth(10);
        $hojaActiva->getColumnDimension('D')->setWidth(10);
        $hojaActiva->getColumnDimension('E')->setWidth(11);
        $hojaActiva->getColumnDimension('F')->setWidth(16);
        $hojaActiva->getColumnDimension('G')->setWidth(16);
        $hojaActiva->getColumnDimension('H')->setWidth(13);
        $hojaActiva->getColumnDimension('I')->setWidth(30);
        // Agregar bordes a las celdas
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // Color del borde (en este caso, negro)
                ],
            ],
        ];
        //GENERA BORDES EN LA TABLA
        $hojaActiva->getStyle('A6:I' . $fila)->applyFromArray($styleArray);
        // Crear una nueva hoja en el archivo de Excel y establecer su título
        $nuevaHoja = $excel->createSheet()->setTitle('REPORTE POR NUMERO DE REGITROS');

        // Añadir la imagen al archivo de Excel
        $imgPath = '../../exel/logo_icon.jpeg'; // Ruta de la imagen

        // Crear una nueva instancia de Drawing para cada ubicación de la imagen
        $coordenadasImagenes = ['A1', 'K1', 'U1', 'AE1', 'AO1', 'AZ1'];

        foreach ($coordenadasImagenes as $coordenada) {
            $drawingNuevaHoja = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawingNuevaHoja->setName('Logo');
            $drawingNuevaHoja->setDescription('Logo');
            $drawingNuevaHoja->setPath($imgPath); // Ruta de la imagen
            $drawingNuevaHoja->setHeight(120); // Establecer la altura de la imagen
            $drawingNuevaHoja->setWidth(120); // Establecer el ancho de la imagen
            $drawingNuevaHoja->setCoordinates($coordenada); // Establecer la coordenada de la celda
            $drawingNuevaHoja->setWorksheet($nuevaHoja);
        }

        // Establecer los datos en las celdas especificadas
        $nuevaHoja->setCellValue('C3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('M3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('W3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('AG3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('AQ3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('BB3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('C2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('M2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('W2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('AG2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('AQ2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('BB2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('C4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('E4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('M4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('O4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('W4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('Y4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('AG4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('AI4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('AQ4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('AS4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('BB4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('BD4', $year . ' - ' . $month);

        // Verificar si se encontraron resultados
        if ($usuario) {
            // Obtener nombres y apellidos del usuario
            $nombresUsuario = $usuario['per_nombres'];
            $apellidosUsuario = $usuario['per_apellidos'];

            // Mostrar los nombres y apellidos del usuario en la celda H6
            $nuevaHoja->setCellValue('E2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('O2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('Y2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('AI2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('AS2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('BD2', $nombresUsuario . ' ' . $apellidosUsuario);
        } else {
            // En caso de no encontrar resultados, mostrar un mensaje alternativo
            $nuevaHoja->setCellValue('E2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('O2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('Y2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('AI2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('AS2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('BD2', 'Usuario no encontrado');
        }

        // Obtener la fecha y hora actual
        $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

        // Añadir la fecha y hora actual en las celdas C3, H3 y Z3
        $nuevaHoja->setCellValue('E3', $fechaHoraActual);
        $nuevaHoja->setCellValue('O3', $fechaHoraActual);
        $nuevaHoja->setCellValue('Y3', $fechaHoraActual);
        $nuevaHoja->setCellValue('AI3', $fechaHoraActual);
        $nuevaHoja->setCellValue('AS3', $fechaHoraActual);
        $nuevaHoja->setCellValue('BD3', $fechaHoraActual);
        // Definir los estilos una vez para reutilización
        $styles = [
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        // Aplicar los estilos a los diferentes rangos de celdas
        $nuevaHoja->getStyle('C2:H6')->applyFromArray($styles);
        $nuevaHoja->getStyle('L2:O6')->applyFromArray($styles);
        $nuevaHoja->getStyle('V2:Y6')->applyFromArray($styles);
        $nuevaHoja->getStyle('AF2:AI6')->applyFromArray($styles);
        $nuevaHoja->getStyle('AP2:AS6')->applyFromArray($styles);
        $nuevaHoja->getStyle('BA2:BD6')->applyFromArray($styles);
        // Ajustar el alto de la fila 1 después de haber insertado todas las imágenes
        $nuevaHoja->getRowDimension('1')->setRowHeight(20); // Establecer el alto de la fila 1
        // Ajustar el alto de la fila 1 después de haber insertado todas las imágenes
        $nuevaHoja->getRowDimension('1')->setRowHeight(20); // Establecer el alto de la fila 1
        // Definir la fecha límite para la primera semana del mes
        $fecha_limite = date('Y-m-01', strtotime($year . '-' . $month));
        $fecha_limite1 = date('Y-m-08', strtotime($year . '-' . $month));
        $fecha_limite2 = date('Y-m-15', strtotime($year . '-' . $month));
        $fecha_limite3 = date('Y-m-22', strtotime($year . '-' . $month));
        $fecha_limite4 = date('Y-m-29', strtotime($year . '-' . $month));
        //calcular los ultimos dia del mes especifico
        $ultimoDiaMes = date('Y-m-d', strtotime($year . '-' . $moth . '-01'));
        //calcular los ultimos tres dias del mes epecifico
        $fecha_limite_29 = date('Y-m-d', strtotime($year . '-' . $moth . '-29'));
        $fecha_limite_30 = date('Y-m-d', strtotime($year . '-' . $moth . '-30'));
        $fecha_limite_31 = date('Y-m-d', strtotime($year . '-' . $moth . '-31'));
        //CONSULTA DEL NUMERO DE REGISTROS POR MES
        $sqlMes = "SELECT r.*, pe.per_nombres AS nombre,
                                        pe.per_apellidos AS apellido,
                                        pl.pla_numero,
                                        re.reg_areaTrabajo,
                                        re.reg_fechaFin,
                                        pe.per_areaTrabajo,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 1 THEN 1 ELSE 0 END) AS DOMINGO,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 2 THEN 1 ELSE 0 END) AS LUNES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 3 THEN 1 ELSE 0 END) AS MARTES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 4 THEN 1 ELSE 0 END) AS MIERCOLES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 5 THEN 1 ELSE 0 END) AS JUEVES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 6 THEN 1 ELSE 0 END) AS VIERNES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 7 THEN 1 ELSE 0 END) AS SABADO
                                        FROM registro AS r
                                        JOIN registro_empleado AS re ON r.reg_id = re.reg_id
                                        JOIN personas AS pe ON r.reg_cedula = pe.cedula
                                        JOIN planos AS pl ON r.pla_id = pl.pla_id
                                        WHERE MONTH(r.reg_fecha) = :month
                                        AND YEAR(r.reg_fecha) = :year
                                        AND re.reg_areaTrabajo = :area_trabajo";
        //PREPARAR LA CONSULTA POR PARAMETROS
        $stmtMes = $conn->prepare($sqlMes);
        $stmtMes->bindParam(":month", $month);
        $stmtMes->bindParam(":year", $year);
        $stmtMes->bindParam(":area_trabajo", $area);
        $stmtMes->execute();
        // CONSULTA PORNUMERO DE REGISTRSO POR SEMANA DEL MES
        $sqlMesSemana = "SELECT r.*, pe.per_nombres AS nombre,
                                        pe.per_apellidos AS apellido,
                                        pl.pla_numero,
                                        re.reg_areaTrabajo,
                                        re.reg_fechaFin,
                                        pe.per_areaTrabajo,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 1 THEN 1 ELSE 0 END) AS DOMINGO,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 2 THEN 1 ELSE 0 END) AS LUNES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 3 THEN 1 ELSE 0 END) AS MARTES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 4 THEN 1 ELSE 0 END) AS MIERCOLES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 5 THEN 1 ELSE 0 END) AS JUEVES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 6 THEN 1 ELSE 0 END) AS VIERNES,
                                        SUM(CASE WHEN DAYOFWEEK(r.reg_fecha) = 7 THEN 1 ELSE 0 END) AS SABADO
                                        FROM registro AS r
                                        JOIN registro_empleado AS re ON r.reg_id = re.reg_id
                                        JOIN personas AS pe ON r.reg_cedula = pe.cedula
                                        JOIN planos AS pl ON r.pla_id = pl.pla_id
                                        WHERE r.reg_fecha >= :fecha_limite
                                        AND r.reg_fecha < DATE_ADD(:fecha_limite, INTERVAL 7 DAY)
                                        AND re.reg_areaTrabajo = :area_trabajo
                                        GROUP BY r.reg_cedula";
        //PREPARAR LA CONSULTA POR PARAMETROS
        $stmtMesSemana = $conn->prepare($sqlMesSemana);
        $stmtMesSemana->bindParam(":fecha_limite", $fecha_limite);
        $stmtMesSemana->bindParam(":area_trabajo", $area);
        $stmtMesSemana->execute();
        $stmtMesSemana1 = $conn->prepare($sqlMesSemana);
        $stmtMesSemana1->bindParam(":fecha_limite", $fecha_limite1);
        $stmtMesSemana1->bindParam(":area_trabajo", $area);
        $stmtMesSemana1->execute();
        $stmtMesSemana2 = $conn->prepare($sqlMesSemana);
        $stmtMesSemana2->bindParam(":fecha_limite", $fecha_limite2);
        $stmtMesSemana2->bindParam(":area_trabajo", $area);
        $stmtMesSemana2->execute();
        $stmtMesSemana3 = $conn->prepare($sqlMesSemana);
        $stmtMesSemana3->bindParam(":fecha_limite", $fecha_limite3);
        $stmtMesSemana3->bindParam(":area_trabajo", $area);
        $stmtMesSemana3->execute();
        //consulta de los ultimos dias del mes
        $sqlMesFin = "SELECT r.*, pe.per_nombres AS nombre,
                                        pe.per_apellidos AS apellido,
                                        pl.pla_numero,
                                        re.reg_areaTrabajo,
                                        re.reg_fechaFin,
                                        pe.per_areaTrabajo,
                                        SUM(CASE WHEN DATE(r.reg_fecha) = :fecha_limite_29 THEN 1 ELSE 0 END) AS registros_29,
                                        SUM(CASE WHEN DATE(r.reg_fecha) = :fecha_limite_30 THEN 1 ELSE 0 END) AS registros_30,
                                        SUM(CASE WHEN DATE(r.reg_fecha) = :fecha_limite_31 THEN 1 ELSE 0 END) AS registros_31
                                        FROM registro AS r
                                        JOIN registro_empleado AS re ON r.reg_id = re.reg_id
                                        JOIN personas AS pe ON r.reg_cedula = pe.cedula
                                        JOIN planos AS pl ON r.pla_id = pl.pla_id
                                        WHERE
                                            DATE(r.reg_fecha) IN (:fecha_limite_29, :fecha_limite_30, :fecha_limite_31)
                                            AND YEAR(r.reg_fecha) = :year
                                            AND MONTH(r.reg_fecha) = :month
                                            AND re.reg_areaTrabajo = :area_trabajo
                                        GROUP BY r.reg_cedula";
        //PREPARAR LA CONSULTA POR PARAMETROS
        $stmtMesFin = $conn->prepare($sqlMesFin);
        $stmtMesFin->bindParam(":fecha_limite_29", $fecha_limite_29);
        $stmtMesFin->bindParam(":fecha_limite_30", $fecha_limite_30);
        $stmtMesFin->bindParam(":fecha_limite_31", $fecha_limite_31);
        $stmtMesFin->bindParam(":year", $year);
        $stmtMesFin->bindParam(":month", $month);
        $stmtMesFin->bindParam(":area_trabajo", $area);
        $stmtMesFin->execute();
        //ESTABLECER LOS ENCABEZADOS DE LA COLUMNA EN LA SEGUNDA HOJA
        $nuevaHoja->setCellValue('D6', 'REPORTE DEL MES');
        $nuevaHoja->setCellValue('A7', 'DISEÑADOR');
        $nuevaHoja->setCellValue('B7', 'LUNES');
        $nuevaHoja->setCellValue('C7', 'MARTES');
        $nuevaHoja->setCellValue('D7', 'MIERCOLES');
        $nuevaHoja->setCellValue('E7', 'JUEVES');
        $nuevaHoja->setCellValue('F7', 'VIERNES');
        $nuevaHoja->setCellValue('G7', 'SABADO');
        $nuevaHoja->setCellValue('H7', 'DOMINGO');
        //ARRAY DE LOS NOMBRE DE LOS DIAS DE LA SEMANA
        $dias_semana = array(
            'LUNES',
            'MARTES',
            'MIERCOLES',
            'JUEVES',
            'VIERNES',
            'SABADO',
            'DOMINGO'
        );
        //ESTABLECER ENCABEZADOS PARA LA TABLA DE LA PRIMERA SEMNA DEL MES
        //CREAR UN ARRAY PARA ALMACENAR LAS FEHAS DE LA PRIMERA SEMANA
        $fechas_primera_semana = array();
        for ($i = 0; $i < 7; $i++) {
            //OBTENER LA FECHA PARA CADA DIA DE LA PRIMERA SEMANA
            $fecha = date('Y-m-d', strtotime($fecha_limite . " +$i days"));
            //AGREGAR LA FECHA AL ARRAY
            $fechas_primera_semana[] = $fecha;
        }
        //CREAR UN ARRAY PARA ALMACENAR LOS ENCABEZADOS DE LA PRIMERA SEMANA
        $encabezados_primera_semana = array();
        foreach ($fechas_primera_semana as $fecha) {
            //obtner los dias de la semana
            $nombre_dia = $dias_semana[date('N', strtotime($fecha)) - 1];
            //formato de la fecha como "dd/mm" y añadir el nombre del dia
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            //AGREGAR EL ENCABEZADO AL ARRAY
            $encabezados_primera_semana[] = $encabezado;
        }
        //PRIMERA SEMANA
        $nuevaHoja->setCellValue('N6', 'REPORTE DE LA PRIMERA SEMANA');
        $nuevaHoja->setCellValue('K7', 'DISEÑADOR');
        $columna_primera_semana = 'L';
        foreach ($encabezados_primera_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna_primera_semana . '7', $encabezado);
            $columna_primera_semana++;
        }
        //CREAR UN ARRAY PARA ALMACENAR LAS FECHAS DE LA SEGUNDA SEMANA
        $fechas_segunda_semana = array();
        for ($i = 0; $i < 7; $i++) {
            //OBTENER LA FECHA PARA CADA DIA DE LA SEGUNDA SEMANA
            $fecha_segunda_semana = date('Y-m-d', strtotime($fecha_limite1 . " +$i days"));
            //AGREGAR LA FECHA AL ARRAY DE LA SEGUNDA SEMANA
            $fechas_segunda_semana[] = $fecha_segunda_semana;
        }
        //CREAR UN ARRAY PARA ALMACENAR LOS ENCABEZADOS DE LA SEGUNDA SEMANA
        $encabezados_segunda_semana = array();
        foreach ($fechas_segunda_semana as $fecha) {
            //OBTENER LOS DIAS DE LA SEMANA
            $nombre_dia = $dias_semana[date('N', strtotime($fecha)) - 1];
            //FORMATO DE LA FECHA COMO "dd/mm" Y AÑADIR EL NOMBRE DEL DIA
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            //AGREGAR EL ENCABEZADO AL ARRAY
            $encabezados_segunda_semana[] = $encabezado;
        }
        //SEGUNDA SEMANA
        $nuevaHoja->setCellValue('X6', 'REPORTE DE LA SEGUNDA SEMANA');
        $nuevaHoja->setCellValue('U7', 'DISEÑADOR');
        $columna_segunda_semana = 'V';
        foreach ($encabezados_segunda_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna_segunda_semana . '7', $encabezado);
            $columna_segunda_semana++;
        }
        //CREAR UN ARRAY PARA ALMACENAR LAS FECHAS DE LA TERCERA SEMANA
        $fechas_tercera_semana = array();
        for ($i = 0; $i < 7; $i++) {
            //OBTENER LA FECHA PARA CADA DIA DE LA TERCERA SEMANA
            $fecha_tercera_semana = date('Y-m-d', strtotime($fecha_limite2 . " +$i days"));
            //AGREGAR LA FECHA AL ARRAY DE LA TERCERA SEMANA
            $fechas_tercera_semana[] = $fecha_tercera_semana;
        }
        //CREAR UN ARRAY PARA ALMACENAR LOS ENCABEZADOS DE LA TERCERA SEMANA
        $encabezados_tercera_semana = array();
        foreach ($fechas_tercera_semana as $fecha) {
            //OBTENER LOS DIAS DE LA SEMANA
            $nombre_dia = $dias_semana[date('N', strtotime($fecha)) - 1];
            //FORMATO DE LA FECHA COMO "dd/mm" Y AÑADIR EL NOMBRE DEL DIA
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            //AGREGAR EL ENCABEZADO AL ARRAY
            $encabezados_tercera_semana[] = $encabezado;
        }
        //TERCERA SEMANA
        $nuevaHoja->setCellValue('AH6', 'REPORTE DE LA TERCERA SEMANA');
        $nuevaHoja->setCellValue('AE7', 'DISEÑADOR');
        $columna_tercera_semana = 'AF';
        foreach ($encabezados_tercera_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna_tercera_semana . '7', $encabezado);
            $columna_tercera_semana++;
        }
        //CREAR UN ARRAY PARA ALMACENAR LAS FECHAS DE LA CUARTA SEMANA
        $fechas_cuarta_semana = array();
        for ($i = 0; $i < 7; $i++) {
            //OBTENER LA FECHA PARA CADA DIA DE LA CUARTA SEMANA
            $fecha_cuarta_semana = date('Y-m-d', strtotime($fecha_limite3 . " +$i days"));
            //AGREGAR LA FECHA AL ARRAY DE LA CUARTA SEMANA
            $fechas_cuarta_semana[] = $fecha_cuarta_semana;
        }
        //CREAR UN ARRAY PARA ALMACENAR LOS ENCABEZADOS DE LA CUARTA SEMANA
        $encabezados_cuarta_semana = array();
        foreach ($fechas_cuarta_semana as $fecha) {
            //OBTENER LOS DIAS DE LA SEMANA
            $nombre_dia = $dias_semana[date('N', strtotime($fecha)) - 1];
            //FORMATO DE LA FECHA COMO "dd/mm" Y AÑADIR EL NOMBRE DEL DIA
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            //AGREGAR EL ENCABEZADO AL ARRAY
            $encabezados_cuarta_semana[] = $encabezado;
        }
        //CUARTA SEMANA
        $nuevaHoja->setCellValue('AR6', 'REPORTE DE LA CUARTA SEMANA');
        $nuevaHoja->setCellValue('AO7', 'DISEÑADOR');
        $columna_cuarta_semana = 'AP';
        foreach ($encabezados_cuarta_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna_cuarta_semana . '7', $encabezado);
            $columna_cuarta_semana++;
        }
        //CREAR UN ARRAY PARA ALMACENAR LAS FECHAS DE LA QUINTA SEMANA
        $fechas_quinta_semana = array();
        for ($i = 0; $i < 3; $i++) {
            //OBTENER LA FECHA PARA CADA DIA DE LA QUINTA SEMANA
            $fecha_quinta_semana = date('Y-m-d', strtotime($fecha_limite4 . " +$i days"));
            //AGREGAR LA FECHA AL ARRAY DE LA QUINTA SEMANA
            $fechas_quinta_semana[] = $fecha_quinta_semana;
        }
        //CREAR UN ARRAY PARA ALMACENAR LOS ENCABEZADOS DE LA QUINTA SEMANA
        $encabezados_quinta_semana = array();
        foreach ($fechas_quinta_semana as $fecha) {
            //OBTENER LOS DIAS DE LA SEMANA
            $nombre_dia = $dias_semana[date('N', strtotime($fecha)) - 1];
            //FORMATO DE LA FECHA COMO "dd/mm" Y AÑADIR EL NOMBRE DEL DIA
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            //AGREGAR EL ENCABEZADO AL ARRAY
            $encabezados_quinta_semana[] = $encabezado;
        }
        //QUINTA SEMANA
        $nuevaHoja->setCellValue('BC6', 'REPORTE DE LA QUINTA SEMANA');
        $nuevaHoja->setCellValue('AZ7', 'DISEÑADOR');
        $columna_quinta_semana = 'BA';
        foreach ($encabezados_quinta_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna_quinta_semana . '7', $encabezado);
            $columna_quinta_semana++;
        }
        //obtner el numero de fila inicial para los datos del segundo libro
        $filaNuevaHoja = 8;
        $totalFilas = max($stmtMesSemana->rowCount(), $stmtMesSemana1->rowCount(), $stmtMesSemana2->rowCount(), $stmtMesSemana3->rowCount(), $stmtMesFin->rowCount(), $stmtMes->rowCount());
        //ITERAR SOBRE LOS RESULTADOS DE LA CONSULTA Y AGREGAR DATOS A LA HOJA DE CALCULO
        for ($i = 0 ; $i < $totalFilas; $i++){
            //OBTENER LOS RESULTADOS DE LA CONSULTA
            $rowsPrimeraSemana = $stmtMesSemana->fetch(PDO::FETCH_ASSOC);
            $rowsSegundaSemana = $stmtMesSemana1->fetch(PDO::FETCH_ASSOC);
            $rowsTerceraSemana = $stmtMesSemana2->fetch(PDO::FETCH_ASSOC);
            $rowsCuartaSemana = $stmtMesSemana3->fetch(PDO::FETCH_ASSOC);
            $rowsQuintaSemana = $stmtMesFin->fetch(PDO::FETCH_ASSOC);
            $rowsMes = $stmtMes->fetch(PDO::FETCH_ASSOC);
            if($rowsMes){
                //OBTENER LOS DATOS DE LA CONSULTA
                $nuevaHoja->setCellValue('A' . $filaNuevaHoja, $rowsMes['nombre'] . ' ' . $rowsMes['apellido']);
                $nuevaHoja->setCellValue('B' . $filaNuevaHoja, $rowsMes['LUNES']);
                $nuevaHoja->setCellValue('C' . $filaNuevaHoja, $rowsMes['MARTES']);
                $nuevaHoja->setCellValue('D' . $filaNuevaHoja, $rowsMes['MIERCOLES']);
                $nuevaHoja->setCellValue('E' . $filaNuevaHoja, $rowsMes['JUEVES']);
                $nuevaHoja->setCellValue('F' . $filaNuevaHoja, $rowsMes['VIERNES']);
                $nuevaHoja->setCellValue('G' . $filaNuevaHoja, $rowsMes['SABADO']);
                $nuevaHoja->setCellValue('H' . $filaNuevaHoja, $rowsMes['DOMINGO']);
            }
            if($rowsPrimeraSemana){
                //OBTENER LOS DATOS DE LA CONSULTA
                $nuevaHoja->setCellValue('K' . $filaNuevaHoja, $rowsPrimeraSemana['nombre'] . ' ' . $rowsPrimeraSemana['apellido']);
                $columna_primera_semana = 'L';
            }
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
