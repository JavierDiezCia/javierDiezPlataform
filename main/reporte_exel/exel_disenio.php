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
    // Verificar si se enviaron los parámetros del año y el mes
    if (isset($_POST['selectYear']) && isset($_POST['selectMonth'])) {
        $year = $_POST['selectYear'];
        $month = $_POST['selectMonth'];
        date_default_timezone_set('America/Lima');

        // Consulta SQL para obtener datos de la base de datos con filtro por año y mes

        $sql = "SELECT Regi.*,O.od_detalle, O.od_cliente,
                CEDULA.per_nombres AS CEDULA_NOMBRES, 
                CEDULA.per_apellidos AS CEDULA_APELLIDOS
        FROM registros_disenio AS Regi 
        LEFT JOIN orden_disenio AS O ON Regi.od_id = O.od_id
        LEFT JOIN personas AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
        WHERE YEAR(Regi.rd_hora_ini) = :year AND MONTH(Regi.rd_hora_fin) = :month";

        // Preparar y ejecutar la consulta con parámetros
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
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
        $drawing->setHeight(70); // Establecer la altura de la imagen
        $drawing->setWidth(70); // Establecer el ancho de la imagen

        // Añadir la imagen al archivo de Excel
        $drawing->setWorksheet($excel->getActiveSheet());

        // Seleccionar la hoja activa y establecer su título
        $hojaActiva = $excel->getActiveSheet();
        $hojaActiva->setTitle("Reporte de las Op");
        $hojaActiva->setCellValue('C3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaActiva->setCellValue('C2', 'REPORTE GENERADO POR');
        $hojaActiva->setCellValue('C4', 'EL REPORTE ES DE LA FECHA');
        $hojaActiva->setCellValue('D4', $year . ' - ' . $month);
        $hojaActiva->getStyle('C2:C4')->getFont()->setBold(true)->setSize(13);
        
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

        // Establecer encabezados de columnas
        $hojaActiva->setCellValue('A6', 'N0.');
        $hojaActiva->setCellValue('B6', 'ORDEN DE DISEÑO');
        $hojaActiva->setCellValue('C6', 'DISEÑADOR.');
        $hojaActiva->setCellValue('D6', 'CLIENTE.');
        $hojaActiva->setCellValue('E6', 'DETALLE.');
        $hojaActiva->setCellValue('F6', 'FECHA HORA INICIO.');
        $hojaActiva->setCellValue('G6', 'FECHA  HORA FINAL.');
        $hojaActiva->setCellValue('H6', 'TIEMPO.');
        $hojaActiva->setCellValue('I6', 'OBSERVACION.');

        // Obtener el número de filas inicial para los datos
        $fila = 7;

        // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hojaActiva->setCellValue('A' . $fila, $rows['rd_id']);
            $hojaActiva->setCellValue('B' . $fila, $rows['od_id']);
            $hojaActiva->setCellValue('C' . $fila, $rows['CEDULA_NOMBRES'] . ' ' . $rows['CEDULA_APELLIDOS']);
            $hojaActiva->setCellValue('D' . $fila, $rows['od_cliente']);
            $hojaActiva->setCellValue('E' . $fila, $rows['od_detalle']);
            $hojaActiva->setCellValue('F' . $fila, $rows['rd_hora_ini']);
            $hojaActiva->setCellValue('G' . $fila, $rows['rd_hora_fin']);

            // Calcular la diferencia entre la hora inicial y la hora final
            $horaInicio = strtotime($rows['rd_hora_ini']);
            $horaFinal = strtotime($rows['rd_hora_fin']);
            $diferencia = $horaFinal - $horaInicio;

            // Formatear la diferencia en horas, minutos y segundos
            $horas = floor($diferencia / 3600);
            $minutos = floor(($diferencia % 3600) / 60);
            $segundos = $diferencia % 60;

            // Construir el tiempo en un formato legible
            $tiempo = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

            // Asignar el tiempo a la columna correspondiente
            $hojaActiva->setCellValue('H' . $fila, $tiempo);

            $hojaActiva->setCellValue('I' . $fila, $rows['rd_observaciones']);

            // Establecer estilos de la fila 6
            $hojaActiva->getStyle('A6:I6')->applyFromArray([
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
        $hojaActiva->getStyle('A6:I' . $fila)->getAlignment()->setWrapText(true); // Activar el ajuste de texto en las celdas
        $hojaActiva->getStyle('A6:I' . $fila)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Centrar verticalmente el texto en las celdas

        // Ajustar automáticamente el tamaño de las columnas y filas
        foreach (range('A', 'I') as $columnID) {
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
            $drawingNuevaHoja->setHeight(100); // Establecer la altura de la imagen
            $drawingNuevaHoja->setWidth(100); // Establecer el ancho de la imagen
            $drawingNuevaHoja->setCoordinates($coordenada); // Establecer la coordenada de la celda
            $drawingNuevaHoja->setWorksheet($nuevaHoja);
        }

        // Establecer los datos en las celdas especificadas
        $nuevaHoja->setCellValue('C3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('L3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('V3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('AF3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('AP3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('BA3', 'FECHA DE GENERACION DEL REPORTE');
        $nuevaHoja->setCellValue('C2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('L2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('V2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('AF2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('AP2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('BA2', 'REPORTE GENERADO POR');
        $nuevaHoja->setCellValue('C4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('D4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('L4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('M4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('V4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('W4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('AF4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('AG4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('AQ4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('D4', $year . ' - ' . $month);
        $nuevaHoja->setCellValue('BA4', 'EL REPORTE ES DE LA FECHA');
        $nuevaHoja->setCellValue('BB4', $year . ' - ' . $month);

        // Verificar si se encontraron resultados
        if ($usuario) {
            // Obtener nombres y apellidos del usuario
            $nombresUsuario = $usuario['per_nombres'];
            $apellidosUsuario = $usuario['per_apellidos'];

            // Mostrar los nombres y apellidos del usuario en la celda H6
            $nuevaHoja->setCellValue('D2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('M2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('W2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('AG2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('AQ2', $nombresUsuario . ' ' . $apellidosUsuario);
            $nuevaHoja->setCellValue('BB2', $nombresUsuario . ' ' . $apellidosUsuario);
        } else {
            // En caso de no encontrar resultados, mostrar un mensaje alternativo
            $nuevaHoja->setCellValue('D2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('M2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('W2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('AG2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('AQ2', 'Usuario no encontrado');
            $nuevaHoja->setCellValue('BB2', 'Usuario no encontrado');
        }

        // Obtener la fecha y hora actual
        $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

        // Añadir la fecha y hora actual en las celdas C3, H3 y Z3
        $nuevaHoja->setCellValue('D3', $fechaHoraActual);
        $nuevaHoja->setCellValue('M3', $fechaHoraActual);
        $nuevaHoja->setCellValue('W3', $fechaHoraActual);
        $nuevaHoja->setCellValue('AG3', $fechaHoraActual);
        $nuevaHoja->setCellValue('AQ3', $fechaHoraActual);
        $nuevaHoja->setCellValue('BB3', $fechaHoraActual);
        // Definir los estilos una vez para reutilización
        $styles = [
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        // Aplicar los estilos a los diferentes rangos de celdas
        $nuevaHoja->getStyle('C2:H3')->applyFromArray($styles);
        $nuevaHoja->getStyle('L2:M3')->applyFromArray($styles);
        $nuevaHoja->getStyle('V2:W3')->applyFromArray($styles);
        $nuevaHoja->getStyle('AF2:AG3')->applyFromArray($styles);
        $nuevaHoja->getStyle('AP2:AQ3')->applyFromArray($styles);
        $nuevaHoja->getStyle('BA2:BB3')->applyFromArray($styles);

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


        // Consulta SQL para obtener los diseñadores y la cantidad de registros que tienen cada uno por día de la semana en la primera semana del mes
        $sqlFecha1 = "SELECT 
                    CEDULA.per_nombres AS CEDULA_NOMBRES, 
                    CEDULA.per_apellidos AS CEDULA_APELLIDOS,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 2 THEN 1 ELSE 0 END) AS LUNES,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 3 THEN 1 ELSE 0 END) AS MARTES,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 4 THEN 1 ELSE 0 END) AS MIERCOLES,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 5 THEN 1 ELSE 0 END) AS JUEVES,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 6 THEN 1 ELSE 0 END) AS VIERNES,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 7 THEN 1 ELSE 0 END) AS SABADO,
                    SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 1 THEN 1 ELSE 0 END) AS DOMINGO
                FROM 
                registros_disenio AS Regi
                    LEFT JOIN PERSONAS AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
                WHERE 
                Regi.rd_hora_ini >= :fecha_limite AND Regi.rd_hora_ini < DATE_ADD(:fecha_limite, INTERVAL 7 DAY)
                GROUP BY 
                Regi.rd_diseniador";
        // Preparar y ejecutar la consulta con parámetros para la primera semana
        $stmPrimeraSemana = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana->bindParam(':fecha_limite', $fecha_limite);
        $stmPrimeraSemana->execute();
        $stmPrimeraSemana1 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana1->bindParam(':fecha_limite', $fecha_limite1);
        $stmPrimeraSemana1->execute();
        $stmPrimeraSemana2 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana2->bindParam(':fecha_limite', $fecha_limite2);
        $stmPrimeraSemana2->execute();
        $stmPrimeraSemana3 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana3->bindParam(':fecha_limite', $fecha_limite3);
        $stmPrimeraSemana3->execute();
        /*  $stmPrimeraSemana4 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana4->bindParam(':fecha_limite', $fecha_limite4);
        $stmPrimeraSemana4->execute();*/
        // Calcular el último día del mes específico
        $ultimoDiaMes = date('Y-m-t', strtotime($year . '-' . $month . '-01'));

        // Calcular los últimos tres días del mes específico
        $fecha_limite_29 = date('Y-m-d', strtotime($year . '-' . $month . '-29'));
        $fecha_limite_30 = date('Y-m-d', strtotime($year . '-' . $month . '-30'));
        $fecha_limite_31 = date('Y-m-d', strtotime($year . '-' . $month . '-31'));

        // Determinar en qué columna colocar los datos según el último día del mes
        $ultimoDia = date('j', strtotime($ultimoDiaMes)); // Obtener el día del mes
        $columna_29 = 'BA';
        $columna_30 = 'BB';
        $columna_31 = 'BC';

        if ($ultimoDia == 31) {
            $columna_29 = 'BA';
            $columna_30 = 'BB';
            $columna_31 = 'BC';
        } elseif ($ultimoDia == 30) {
            $columna_29 = 'BA';
            $columna_30 = 'BB';
        } elseif ($ultimoDia == 29) {
            $columna_29 = 'BA';
        }

        // Consulta SQL para obtener el contador de registros para los últimos tres días del mes y año específicos, por diseñador
        $sqlNueva1 = "SELECT 
               CEDULA.per_nombres AS CEDULA_NOMBRES, 
               CEDULA.per_apellidos AS CEDULA_APELLIDOS,
               SUM(CASE WHEN DATE(rd_hora_ini) = :fecha_limite_29 THEN 1 ELSE 0 END) AS registros_29,
               SUM(CASE WHEN DATE(rd_hora_ini) = :fecha_limite_30 THEN 1 ELSE 0 END) AS registros_30,
               SUM(CASE WHEN DATE(rd_hora_ini) = :fecha_limite_31 THEN 1 ELSE 0 END) AS registros_31
           FROM registros_disenio AS Regi
           LEFT JOIN personas AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
           WHERE 
               DATE(rd_hora_ini) IN (:fecha_limite_29, :fecha_limite_30, :fecha_limite_31)
               AND YEAR(rd_hora_ini) = :anio
               AND MONTH(rd_hora_ini) = :mes
           GROUP BY rd_diseniador";

        // Preparar y ejecutar la consulta
        $stmPrimeraSemana4 = $conn->prepare($sqlNueva1);
        $stmPrimeraSemana4->bindParam(':fecha_limite_29', $fecha_limite_29);
        $stmPrimeraSemana4->bindParam(':fecha_limite_30', $fecha_limite_30);
        $stmPrimeraSemana4->bindParam(':fecha_limite_31', $fecha_limite_31);
        $stmPrimeraSemana4->bindParam(':anio', $year); // Año específico
        $stmPrimeraSemana4->bindParam(':mes', $month); // Mes específico
        $stmPrimeraSemana4->execute();


        // Consulta SQL para obtener los diseñadores y la cantidad de registros que tienen cada uno por día de la semana
        $sqlNuevaHoja = "SELECT 
                            CEDULA.per_nombres AS CEDULA_NOMBRES, 
                            CEDULA.per_apellidos AS CEDULA_APELLIDOS,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 2 THEN 1 ELSE 0 END) AS LUNES,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 3 THEN 1 ELSE 0 END) AS MARTES,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 4 THEN 1 ELSE 0 END) AS MIERCOLES,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 5 THEN 1 ELSE 0 END) AS JUEVES,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 6 THEN 1 ELSE 0 END) AS VIERNES,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 7 THEN 1 ELSE 0 END) AS SABADO,
                            SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 1 THEN 1 ELSE 0 END) AS DOMINGO
                        FROM 
                        registros_disenio AS Regi
                            LEFT JOIN PERSONAS AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
                        WHERE 
                            YEAR(Regi.rd_hora_ini) = :year 
                            AND MONTH(Regi.rd_hora_ini) = :month
                        GROUP BY 
                        Regi.rd_diseniador";

        // Preparar y ejecutar la consulta con parámetros para la nueva hoja
        $stmtNuevaHoja = $conn->prepare($sqlNuevaHoja);
        $stmtNuevaHoja->bindParam(':year', $year);
        $stmtNuevaHoja->bindParam(':month', $month);
        $stmtNuevaHoja->execute();



        // Establecer encabezados de columnas en la nueva hoja
        $nuevaHoja->setCellValue('A6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('B6', 'LUNES');
        $nuevaHoja->setCellValue('C6', 'MARTES');
        $nuevaHoja->setCellValue('D6', 'MIERCOLES');
        $nuevaHoja->setCellValue('E6', 'JUEVES');
        $nuevaHoja->setCellValue('F6', 'VIERNES');
        $nuevaHoja->setCellValue('G6', 'SABADO');
        $nuevaHoja->setCellValue('H6', 'DOMINGO');



        // Crear un array para almacenar las fechas de la primera semana
        $fechas_primera_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la primera semana
            $fecha = date('Y-m-d', strtotime($fecha_limite . " +$i days"));
            // Agregar la fecha al array
            $fechas_primera_semana[] = $fecha;
        }

        // Array de los nombres de los días de la semana en español
        $dias_semana_espanol = array(
            'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
        );

        // Crear un array para almacenar los encabezados de la primera semana
        $encabezados_primera_semana = array();
        foreach ($fechas_primera_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            // Agregar el encabezado al array
            $encabezados_primera_semana[] = $encabezado;
        }

        // de la primera semana
        $nuevaHoja->setCellValue('K6', 'DISEÑADOR');
        $columna = 'L';
        foreach ($encabezados_primera_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna . '6', $encabezado);
            $columna++;
        }


        // Crear un array para almacenar las fechas de la segunda semana
        $fechas_segunda_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la segunda semana
            $fecha_segunda_semana = date('Y-m-d', strtotime($fecha_limite1 . " +$i days"));
            // Agregar la fecha al array de la segunda semana
            $fechas_segunda_semana[] = $fecha_segunda_semana;
        }

        // Crear un array para almacenar los encabezados de la segunda semana
        $encabezados_segunda_semana = array();
        foreach ($fechas_segunda_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia1 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado1 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia1;
            // Agregar el encabezado al array
            $encabezados_segunda_semana[] = $encabezado1;
        }

        // SEGUNDA SEMANA
        $nuevaHoja->setCellValue('U6', 'DISEÑADOR');
        $columna1 = 'V';
        foreach ($encabezados_segunda_semana as $encabezado1) {
            $nuevaHoja->setCellValue($columna1 . '6', $encabezado1);
            $columna1++;
        }
        //TERCERA semana

        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_tercera_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_tercera_semana = date('Y-m-d', strtotime($fecha_limite2 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_tercera_semana[] = $fecha_tercera_semana;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_tercera_semana = array();
        foreach ($fechas_tercera_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia2 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado2 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia2;
            // Agregar el encabezado al array
            $encabezados_tercera_semana[] = $encabezado2;
        }

        // Mostrar los encabezados en la tercera semana
        $nuevaHoja->setCellValue('AE6', 'DISEÑADOR');
        $columna2 = 'AF';
        foreach ($encabezados_tercera_semana as $encabezado2) {
            $nuevaHoja->setCellValue($columna2 . '6', $encabezado2);
            $columna2++;
        }

        //CUARTA semana
        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_cuarta_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_cuarta_semana = date('Y-m-d', strtotime($fecha_limite3 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_cuarta_semana[] = $fecha_cuarta_semana;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_cuarta_semana = array();
        foreach ($fechas_cuarta_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia3 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado3 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia3;
            // Agregar el encabezado al array
            $encabezados_cuarta_semana[] = $encabezado3;
        }

        // Mostrar los encabezados en la tercera semana
        $nuevaHoja->setCellValue('AO6', 'DISEÑADOR');
        $columna3 = 'AP';
        foreach ($encabezados_cuarta_semana as $encabezado3) {
            $nuevaHoja->setCellValue($columna3 . '6', $encabezado3);
            $columna3++;
        }


        //QUINTA semna
        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_quinta_semana = array();
        for ($i = 0; $i < 3; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_quinta_semana = date('Y-m-d', strtotime($fecha_limite4 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_quinta_semana[] = $fecha_quinta_semana;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_quinta_semana = array();
        foreach ($fechas_quinta_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia4 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado4 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia4;
            // Agregar el encabezado al array
            $encabezados_quinta_semana[] = $encabezado4;
        }

        // Mostrar los encabezados en la tercera semana
        $nuevaHoja->setCellValue('AZ6', 'DISEÑADOR');
        $columna4 = 'BA';
        foreach ($encabezados_quinta_semana as $encabezado4) {
            $nuevaHoja->setCellValue($columna4 . '6', $encabezado4);
            $columna4++;
        }


        // Obtener el número de filas inicial para los datos de la hoja nueva
        $filaNuevaHoja = 7;
        // Obtener el número de filas necesario para ambas consultas
        $totalFilas = max($stmPrimeraSemana->rowCount(), $stmPrimeraSemana1->rowCount(), $stmPrimeraSemana2->rowCount(), $stmPrimeraSemana3->rowCount(),  $stmPrimeraSemana4->rowCount(), $stmtNuevaHoja->rowCount());

        // Iterar sobre las filas y escribir los datos en la hoja de Excel
        for ($i = 0; $i < $totalFilas; $i++) {
            // Obtener los datos de la primera consulta
            $rowPrimeraSemana = $stmPrimeraSemana->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana1 = $stmPrimeraSemana1->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana2 = $stmPrimeraSemana2->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana3 = $stmPrimeraSemana3->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana4 = $stmPrimeraSemana4->fetch(PDO::FETCH_ASSOC);
            // Obtener los datos de la segunda consulta
            $rowNuevaHoja = $stmtNuevaHoja->fetch(PDO::FETCH_ASSOC);   // Mostrar los datos de la primera consulta si están disponibles
            if ($rowNuevaHoja) {
                // Mostrar el diseñador en la columna A
                $nuevaHoja->setCellValue('A' . $filaNuevaHoja, $rowNuevaHoja['CEDULA_NOMBRES'] . ' ' . $rowNuevaHoja['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('B' . $filaNuevaHoja, $rowNuevaHoja['LUNES']);
                $nuevaHoja->setCellValue('C' . $filaNuevaHoja, $rowNuevaHoja['MARTES']);
                $nuevaHoja->setCellValue('D' . $filaNuevaHoja, $rowNuevaHoja['MIERCOLES']);
                $nuevaHoja->setCellValue('E' . $filaNuevaHoja, $rowNuevaHoja['JUEVES']);
                $nuevaHoja->setCellValue('F' . $filaNuevaHoja, $rowNuevaHoja['VIERNES']);
                $nuevaHoja->setCellValue('G' . $filaNuevaHoja, $rowNuevaHoja['SABADO']);
                $nuevaHoja->setCellValue('H' . $filaNuevaHoja, $rowNuevaHoja['DOMINGO']);
            }

            // Mostrar los datos de la primera consulta si están disponibles
            if ($rowPrimeraSemana) {
                // Mostrar el diseñador en la columna K
                $nuevaHoja->setCellValue('K' . $filaNuevaHoja, $rowPrimeraSemana['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana['CEDULA_APELLIDOS']);
                $columna = 'L';
                // Inicializar la primera columna donde se colocarán los valores de la consulta
                foreach ($encabezados_primera_semana as $encabezado) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia = substr($encabezado, strpos($encabezado, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia) {
                        case 'Lunes':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['LUNES']);
                            break;
                        case 'Martes':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['MARTES']);
                            break;
                        case 'Miércoles':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['JUEVES']);
                            break;
                        case 'Viernes':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['VIERNES']);
                            break;
                        case 'Sábado':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['SABADO']);
                            break;
                        case 'Domingo':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna++;
                }
            }

            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana1) {
                // Mostrar el diseñador en la columna U
                $nuevaHoja->setCellValue('U' . $filaNuevaHoja, $rowPrimeraSemana1['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana1['CEDULA_APELLIDOS']);

                // Inicializar la primera columna donde se colocarán los valores de la consulta
                $columna1 = 'V';

                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_segunda_semana as $encabezado1) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia1 = substr($encabezado1, strpos($encabezado1, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia1) {
                        case 'Lunes':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['LUNES']);
                            break;
                        case 'Martes':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['MARTES']);
                            break;
                        case 'Miércoles':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['JUEVES']);
                            break;
                        case 'Viernes':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['VIERNES']);
                            break;
                        case 'Sábado':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['SABADO']);
                            break;
                        case 'Domingo':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna1++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana2) {
                // Mostrar el diseñador en la columna AE
                $nuevaHoja->setCellValue('AE' . $filaNuevaHoja, $rowPrimeraSemana2['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana2['CEDULA_APELLIDOS']);
                $columna2 = 'AF';
                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_tercera_semana as $encabezado2) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia2 = substr($encabezado2, strpos($encabezado2, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia2) {
                        case 'Lunes':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['LUNES']);
                            break;
                        case 'Martes':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['MARTES']);
                            break;
                        case 'Miércoles':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['JUEVES']);
                            break;
                        case 'Viernes':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['VIERNES']);
                            break;
                        case 'Sábado':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['SABADO']);
                            break;
                        case 'Domingo':
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, $rowPrimeraSemana2['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $nuevaHoja->setCellValue($columna2 . $filaNuevaHoja, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna2++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana3) {
                // Mostrar el diseñador en la columna AO
                $nuevaHoja->setCellValue('AO' . $filaNuevaHoja, $rowPrimeraSemana3['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana3['CEDULA_APELLIDOS']);
                $columna3 = 'AP';
                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_cuarta_semana as $encabezado3) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia3 = substr($encabezado3, strpos($encabezado3, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia3) {
                        case 'Lunes':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['LUNES']);
                            break;
                        case 'Martes':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['MARTES']);
                            break;
                        case 'Miércoles':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['JUEVES']);
                            break;
                        case 'Viernes':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['VIERNES']);
                            break;
                        case 'Sábado':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['SABADO']);
                            break;
                        case 'Domingo':
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, $rowPrimeraSemana3['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $nuevaHoja->setCellValue($columna3 . $filaNuevaHoja, '');
                    }
                    $columna3++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana4) {
                // Mostrar el diseñador en la columna AZ

                $nuevaHoja->setCellValue('AZ' . $filaNuevaHoja, $rowPrimeraSemana4['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana4['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue($columna_29 . $filaNuevaHoja, $rowPrimeraSemana4['registros_29']);
                $nuevaHoja->setCellValue($columna_30 . $filaNuevaHoja, $rowPrimeraSemana4['registros_30']);
                $nuevaHoja->setCellValue($columna_31 . $filaNuevaHoja, $rowPrimeraSemana4['registros_31']);
            }
            $filaNuevaHoja++;
        }

        foreach (range('A', 'Z') as $columnID) {
            $nuevaHoja->getColumnDimension($columnID)->setAutoSize(true);
        }



        // Establecer un ancho específico para las columnas AE, AO y AZ
        /*$nuevaHoja->getColumnDimension('AE')->setWidth(40);
        $nuevaHoja->getColumnDimension('AO')->setWidth(40);
        $nuevaHoja->getColumnDimension('AZ')->setWidth(40);*/

        $nuevaHoja->getColumnDimension('AA')->setWidth(25);
        $nuevaHoja->getColumnDimension('AB')->setWidth(25);


        function applyCommonStylesToRow6($sheet, $startColumn, $endColumn)
        {
            $columnRange = $startColumn . '6:' . $endColumn . '6';

            $sheet->getStyle($columnRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0000FF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Ajustar automáticamente el ancho de las columnas al contenido
            for ($col = $startColumn; $col <= $endColumn; $col++) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }



        // Llamar a la función para cada rango de columnas
        applyCommonStylesToRow6($nuevaHoja, 'A', 'H');
        applyCommonStylesToRow6($nuevaHoja, 'K', 'R');
        applyCommonStylesToRow6($nuevaHoja, 'AE', 'AL');
        applyCommonStylesToRow6($nuevaHoja, 'U', 'AB');
        applyCommonStylesToRow6($nuevaHoja, 'AO', 'AV');
        applyCommonStylesToRow6($nuevaHoja, 'AZ', 'BC');

        //ALICAR  EL BORDE 
        $nuevaHoja->getStyle('A6:H' . $filaNuevaHoja)->applyFromArray($styleArray);
        $nuevaHoja->getStyle('K6:R' . $filaNuevaHoja)->applyFromArray($styleArray);
        $nuevaHoja->getStyle('AE6:AL' . $filaNuevaHoja)->applyFromArray($styleArray);
        $nuevaHoja->getStyle('U6:AB' . $filaNuevaHoja)->applyFromArray($styleArray);
        $nuevaHoja->getStyle('AO6:AV' . $filaNuevaHoja)->applyFromArray($styleArray);
        $nuevaHoja->getStyle('AZ6:BC' . $filaNuevaHoja)->applyFromArray($styleArray);


        // Establecer el alto de la fila 6
        $nuevaHoja->getRowDimension('6')->setRowHeight(70);
        $hojaHora = $excel->createSheet()->setTitle('REPORTE POR TIEMPO DE REGITROS');
        // Añadir la imagen al archivo de Excel
        $imgPath = '../../exel/logo_icon.jpeg'; // Ruta de la imagen

        // Crear una nueva instancia de Drawing para cada ubicación de la imagen
        $coordenadasImagenes1 = ['A1', 'K1', 'U1', 'AE1', 'AO1', 'AZ1'];

        foreach ($coordenadasImagenes1 as $coordenada1) {
            $drawingHojaHora = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawingHojaHora->setName('Logo');
            $drawingHojaHora->setDescription('Logo');
            $drawingHojaHora->setPath($imgPath); // Ruta de la imagen
            $drawingHojaHora->setHeight(100); // Establecer la altura de la imagen
            $drawingHojaHora->setWidth(100); // Establecer el ancho de la imagen
            $drawingHojaHora->setCoordinates($coordenada1); // Establecer la coordenada de la celda
            $drawingHojaHora->setWorksheet($hojaHora);
        }
        // Establecer los datos en las celdas especificadas
        $hojaHora->setCellValue('C3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaHora->setCellValue('L3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaHora->setCellValue('V3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaHora->setCellValue('AF3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaHora->setCellValue('AP3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaHora->setCellValue('BA3', 'FECHA DE GENERACION DEL REPORTE');
        $hojaHora->setCellValue('C2', 'REPORTE GENERADO POR');
        $hojaHora->setCellValue('L2', 'REPORTE GENERADO POR');
        $hojaHora->setCellValue('V2', 'REPORTE GENERADO POR');
        $hojaHora->setCellValue('AF2', 'REPORTE GENERADO POR');
        $hojaHora->setCellValue('AP2', 'REPORTE GENERADO POR');
        $hojaHora->setCellValue('BA2', 'REPORTE GENERADO POR');
        $hojaHora->setCellValue('C4', 'EL REPORTE ES DE LA FECHA');
        $hojaHora->setCellValue('D4', $year . ' - ' . $month);
        $hojaHora->setCellValue('L4', 'EL REPORTE ES DE LA FECHA');
        $hojaHora->setCellValue('M4', $year . ' - ' . $month);
        $hojaHora->setCellValue('V4', 'EL REPORTE ES DE LA FECHA');
        $hojaHora->setCellValue('W4', $year . ' - ' . $month);
        $hojaHora->setCellValue('AF4', 'EL REPORTE ES DE LA FECHA');
        $hojaHora->setCellValue('AG4', $year . ' - ' . $month);
        $hojaHora->setCellValue('AQ4', 'EL REPORTE ES DE LA FECHA');
        $hojaHora->setCellValue('D4', $year . ' - ' . $month);
        $hojaHora->setCellValue('BA4', 'EL REPORTE ES DE LA FECHA');
        $hojaHora->setCellValue('BB4', $year . ' - ' . $month);
        // Verificar si se encontraron resultados
        if ($usuario) {
            // Obtener nombres y apellidos del usuario
            $nombresUsuario = $usuario['per_nombres'];
            $apellidosUsuario = $usuario['per_apellidos'];

            // Mostrar los nombres y apellidos del usuario en la celda H6
            $hojaHora->setCellValue('D2', $nombresUsuario . ' ' . $apellidosUsuario);
            $hojaHora->setCellValue('M2', $nombresUsuario . ' ' . $apellidosUsuario);
            $hojaHora->setCellValue('W2', $nombresUsuario . ' ' . $apellidosUsuario);
            $hojaHora->setCellValue('AG2', $nombresUsuario . ' ' . $apellidosUsuario);
            $hojaHora->setCellValue('AQ2', $nombresUsuario . ' ' . $apellidosUsuario);
            $hojaHora->setCellValue('BB2', $nombresUsuario . ' ' . $apellidosUsuario);
        } else {
            // En caso de no encontrar resultados, mostrar un mensaje alternativo
            $hojaHora->setCellValue('D2', 'Usuario no encontrado');
            $hojaHora->setCellValue('M2', 'Usuario no encontrado');
            $hojaHora->setCellValue('W2', 'Usuario no encontrado');
            $hojaHora->setCellValue('AG2', 'Usuario no encontrado');
            $hojaHora->setCellValue('AQ2', 'Usuario no encontrado');
            $hojaHora->setCellValue('BB2', 'Usuario no encontrado');
        }

        // Obtener la fecha y hora actual
        $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

        // Añadir la fecha y hora actual en las celdas C3, H3 y Z3
        $hojaHora->setCellValue('D3', $fechaHoraActual);
        $hojaHora->setCellValue('M3', $fechaHoraActual);
        $hojaHora->setCellValue('W3', $fechaHoraActual);
        $hojaHora->setCellValue('AG3', $fechaHoraActual);
        $hojaHora->setCellValue('AQ3', $fechaHoraActual);
        $hojaHora->setCellValue('BB3', $fechaHoraActual);
        // Definir los estilos una vez para reutilización


        // Aplicar los estilos a los diferentes rangos de celdas
        $hojaHora->getStyle('C2:H3')->applyFromArray($styles);
        $hojaHora->getStyle('L2:M3')->applyFromArray($styles);
        $hojaHora->getStyle('V2:W3')->applyFromArray($styles);
        $hojaHora->getStyle('AF2:AG3')->applyFromArray($styles);
        $hojaHora->getStyle('AP2:AQ3')->applyFromArray($styles);
        $hojaHora->getStyle('BA2:BB3')->applyFromArray($styles);

        // Ajustar el alto de la fila 1 después de haber insertado todas las imágenes
        $hojaHora->getRowDimension('1')->setRowHeight(20); // Establecer el alto de la fila 1

        // Ajustar el alto de la fila 1 después de haber insertado todas las imágenes
        $hojaHora->getRowDimension('1')->setRowHeight(20); // Establecer el alto de la fila 1

        $sqlHora = "SELECT 
                        CEDULA.per_nombres AS CEDULA_NOMBRES, 
                        CEDULA.per_apellidos AS CEDULA_APELLIDOS,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 2 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS LUNES,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 3 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS MARTES,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 4 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS MIERCOLES,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 5 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS JUEVES,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 6 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS VIERNES,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 7 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS SABADO,
                        SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 1 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS DOMINGO
                    FROM  registros_disenio AS Regi
                    LEFT JOIN PERSONAS AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
                    WHERE 
                            YEAR(Regi.rd_hora_ini) = :year 
                            AND MONTH(Regi.rd_hora_ini) = :month
                    GROUP BY 
                            Regi.rd_diseniador";

        $sqlSemana = "SELECT 
                            CEDULA.per_nombres AS CEDULA_NOMBRES, 
                            CEDULA.per_apellidos AS CEDULA_APELLIDOS,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 2 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS LUNES,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 3 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS MARTES,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 4 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS MIERCOLES,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 5 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS JUEVES,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 6 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS VIERNES,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 7 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS SABADO,
                            SEC_TO_TIME(SUM(CASE WHEN DAYOFWEEK(Regi.rd_hora_ini) = 1 THEN TIME_TO_SEC(TIMEDIFF(Regi.rd_hora_fin, Regi.rd_hora_ini)) ELSE 0 END)) AS DOMINGO
                        FROM registros_disenio AS Regi
                        LEFT JOIN PERSONAS AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
                        WHERE 
                            Regi.rd_hora_ini >= :fecha_limite 
                            AND Regi.rd_hora_ini < DATE_ADD(:fecha_limite, INTERVAL 7 DAY)
                        GROUP BY 
                            Regi.rd_diseniador";
        $sqlHoraFinal = "SELECT 
                            CEDULA.per_nombres AS CEDULA_NOMBRES, 
                            CEDULA.per_apellidos AS CEDULA_APELLIDOS,
                            SUM(CASE WHEN DATE(rd_hora_ini) = :fecha_limite_29 THEN TIMESTAMPDIFF(SECOND, rd_hora_ini, rd_hora_fin) ELSE 0 END) AS tiempo_29,
                            SUM(CASE WHEN DATE(rd_hora_ini) = :fecha_limite_30 THEN TIMESTAMPDIFF(SECOND, rd_hora_ini, rd_hora_fin) ELSE 0 END) AS tiempo_30,
                            SUM(CASE WHEN DATE(rd_hora_ini) = :fecha_limite_31 THEN TIMESTAMPDIFF(SECOND, rd_hora_ini, rd_hora_fin) ELSE 0 END) AS tiempo_31
                        FROM registros_disenio AS Regi
                        LEFT JOIN personas AS CEDULA ON Regi.rd_diseniador = CEDULA.CEDULA
                        WHERE 
                        DATE(rd_hora_ini) IN (:fecha_limite_29, :fecha_limite_30, :fecha_limite_31)
                        AND YEAR(rd_hora_ini) = :anio
                        AND MONTH(rd_hora_ini) = :mes
                        GROUP BY rd_diseniador";
        // Preparar y ejecutar la consulta con parámetros para la primera semana
        $stmPrimeraSemHora = $conn->prepare($sqlSemana);
        $stmPrimeraSemHora->bindParam(':fecha_limite', $fecha_limite);
        $stmPrimeraSemHora->execute();
        $stmPrimeraSemHora1 = $conn->prepare($sqlSemana);
        $stmPrimeraSemHora1->bindParam(':fecha_limite', $fecha_limite1);
        $stmPrimeraSemHora1->execute();
        $stmPrimeraSemHora2 = $conn->prepare($sqlSemana);
        $stmPrimeraSemHora2->bindParam(':fecha_limite', $fecha_limite2);
        $stmPrimeraSemHora2->execute();
        $stmPrimeraSemHora3 = $conn->prepare($sqlSemana);
        $stmPrimeraSemHora3->bindParam(':fecha_limite', $fecha_limite3);
        $stmPrimeraSemHora3->execute();
        // Preparar y ejecutar la consulta
        $stmPrimeraSemHora4 = $conn->prepare($sqlHoraFinal);
        $stmPrimeraSemHora4->bindParam(':fecha_limite_29', $fecha_limite_29);
        $stmPrimeraSemHora4->bindParam(':fecha_limite_30', $fecha_limite_30);
        $stmPrimeraSemHora4->bindParam(':fecha_limite_31', $fecha_limite_31);
        $stmPrimeraSemHora4->bindParam(':anio', $year); // Año específico
        $stmPrimeraSemHora4->bindParam(':mes', $month); // Mes específico
        $stmPrimeraSemHora4->execute();
        // Preparar y ejecutar la consulta con parámetros para la nueva hoja
        $stmtHora = $conn->prepare($sqlHora);
        $stmtHora->bindParam(':year', $year);
        $stmtHora->bindParam(':month', $month);
        $stmtHora->execute();

        // Establecer encabezados de columnas en la nueva hoja
        $hojaHora->setCellValue('A6', 'DISEÑADOR');
        $hojaHora->setCellValue('B6', 'LUNES');
        $hojaHora->setCellValue('C6', 'MARTES');
        $hojaHora->setCellValue('D6', 'MIERCOLES');
        $hojaHora->setCellValue('E6', 'JUEVES');
        $hojaHora->setCellValue('F6', 'VIERNES');
        $hojaHora->setCellValue('G6', 'SABADO');
        $hojaHora->setCellValue('H6', 'DOMINGO');

        // Crear un array para almacenar las fechas de la primera semana
        $fechas_primera_semanaHora = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la primera semana
            $fecha = date('Y-m-d', strtotime($fecha_limite . " +$i days"));
            // Agregar la fecha al array
            $fechas_primera_semanaHora[] = $fecha;
        }



        // Crear un array para almacenar los encabezados de la primera semana
        $encabezados_primera_semanaHora = array();
        foreach ($fechas_primera_semanaHora as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            // Agregar el encabezado al array
            $encabezados_primera_semanaHora[] = $encabezado;
        }

        // de la primera semana
        $hojaHora->setCellValue('K6', 'DISEÑADOR');
        $columna = 'L';
        foreach ($encabezados_primera_semanaHora as $encabezado) {
            $hojaHora->setCellValue($columna . '6', $encabezado);
            $columna++;
        }


        // Crear un array para almacenar las fechas de la segunda semana
        $fechas_segunda_semanaHora = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la segunda semana
            $fecha_segunda_semanaHora = date('Y-m-d', strtotime($fecha_limite1 . " +$i days"));
            // Agregar la fecha al array de la segunda semana
            $fechas_segunda_semanaHora[] = $fecha_segunda_semanaHora;
        }

        // Crear un array para almacenar los encabezados de la segunda semana
        $encabezados_segunda_semanaHora = array();
        foreach ($fechas_segunda_semanaHora as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia1 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado1 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia1;
            // Agregar el encabezado al array
            $encabezados_segunda_semanaHora[] = $encabezado1;
        }

        // SEGUNDA SEMANA
        $hojaHora->setCellValue('U6', 'DISEÑADOR');
        $columna1 = 'V';
        foreach ($encabezados_segunda_semanaHora as $encabezado1) {
            $hojaHora->setCellValue($columna1 . '6', $encabezado1);
            $columna1++;
        }
        //TERCERA semana

        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_tercera_semanaHora = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_tercera_semanaHora = date('Y-m-d', strtotime($fecha_limite2 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_tercera_semanaHora[] = $fecha_tercera_semanaHora;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_tercera_semanaHora = array();
        foreach ($fechas_tercera_semanaHora as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia2 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado2 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia2;
            // Agregar el encabezado al array
            $encabezados_tercera_semanaHora[] = $encabezado2;
        }

        // Mostrar los encabezados en la tercera semana
        $hojaHora->setCellValue('AE6', 'DISEÑADOR');
        $columna2 = 'AF';
        foreach ($encabezados_tercera_semanaHora as $encabezado2) {
            $hojaHora->setCellValue($columna2 . '6', $encabezado2);
            $columna2++;
        }

        //CUARTA semana
        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_cuarta_semanaHora = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_cuarta_semanaHora = date('Y-m-d', strtotime($fecha_limite3 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_cuarta_semanaHora[] = $fecha_cuarta_semanaHora;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_cuarta_semanaHora = array();
        foreach ($fechas_cuarta_semanaHora as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia3 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado3 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia3;
            // Agregar el encabezado al array
            $encabezados_cuarta_semanaHora[] = $encabezado3;
        }

        // Mostrar los encabezados en la tercera semana
        $hojaHora->setCellValue('AO6', 'DISEÑADOR');
        $columna3 = 'AP';
        foreach ($encabezados_cuarta_semanaHora as $encabezado3) {
            $hojaHora->setCellValue($columna3 . '6', $encabezado3);
            $columna3++;
        }


        //QUINTA semna
        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_quinta_semanaHora = array();
        for ($i = 0; $i < 3; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_quinta_semanaHora = date('Y-m-d', strtotime($fecha_limite4 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_quinta_semanaHora[] = $fecha_quinta_semanaHora;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_quinta_semanaHora = array();
        foreach ($fechas_quinta_semanaHora as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia4 = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado4 = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia4;
            // Agregar el encabezado al array
            $encabezados_quinta_semanaHora[] = $encabezado4;
        }

        // Mostrar los encabezados en la tercera semana
        $hojaHora->setCellValue('AZ6', 'DISEÑADOR');
        $columna4 = 'BA';
        foreach ($encabezados_quinta_semanaHora as $encabezado4) {
            $hojaHora->setCellValue($columna4 . '6', $encabezado4);
            $columna4++;
        }

        // Obtener el número de filas inicial para los datos de la hoja nueva
        $filahojaHora = 7;
        // Obtener el número de filas necesario para ambas consultas
        $totalFilasHora = max($stmPrimeraSemHora->rowCount(), $stmPrimeraSemHora1->rowCount(), $stmPrimeraSemHora2->rowCount(), $stmPrimeraSemHora3->rowCount(),  $stmPrimeraSemHora4->rowCount(), $stmtHora->rowCount());

        // Iterar sobre las filas y escribir los datos en la hoja de Excel
        for ($i = 0; $i < $totalFilasHora; $i++) {
            // Obtener los datos de la primera consulta
            $rowPrimeraSemHora = $stmPrimeraSemHora->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemHora1 = $stmPrimeraSemHora1->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemHora2 = $stmPrimeraSemHora2->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemHora3 = $stmPrimeraSemHora3->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemHora4 = $stmPrimeraSemHora4->fetch(PDO::FETCH_ASSOC);
            // Obtener los datos de la segunda consulta
            $rowHojaHora = $stmtHora->fetch(PDO::FETCH_ASSOC);   // Mostrar los datos de la primera consulta si están disponibles
            if ($rowHojaHora) {
                // Mostrar el diseñador en la columna A
                $hojaHora->setCellValue('A' . $filahojaHora, $rowHojaHora['CEDULA_NOMBRES'] . ' ' . $rowHojaHora['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $hojaHora->setCellValue('B' . $filahojaHora, $rowHojaHora['LUNES']);
                $hojaHora->setCellValue('C' . $filahojaHora, $rowHojaHora['MARTES']);
                $hojaHora->setCellValue('D' . $filahojaHora, $rowHojaHora['MIERCOLES']);
                $hojaHora->setCellValue('E' . $filahojaHora, $rowHojaHora['JUEVES']);
                $hojaHora->setCellValue('F' . $filahojaHora, $rowHojaHora['VIERNES']);
                $hojaHora->setCellValue('G' . $filahojaHora, $rowHojaHora['SABADO']);
                $hojaHora->setCellValue('H' . $filahojaHora, $rowHojaHora['DOMINGO']);
            }

            // Mostrar los datos de la primera consulta si están disponibles
            if ($rowPrimeraSemHora) {
                // Mostrar el diseñador en la columna K
                $hojaHora->setCellValue('K' . $filahojaHora, $rowPrimeraSemHora['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemHora['CEDULA_APELLIDOS']);
                $columna = 'L';
                // Inicializar la primera columna donde se colocarán los valores de la consulta
                foreach ($encabezados_primera_semana as $encabezado) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia = substr($encabezado, strpos($encabezado, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia) {
                        case 'Lunes':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['LUNES']);
                            break;
                        case 'Martes':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['MARTES']);
                            break;
                        case 'Miércoles':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['JUEVES']);
                            break;
                        case 'Viernes':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['VIERNES']);
                            break;
                        case 'Sábado':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['SABADO']);
                            break;
                        case 'Domingo':
                            $hojaHora->setCellValue($columna . $filahojaHora, $rowPrimeraSemHora['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $hojaHora->setCellValue($columna . $filahojaHora, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna++;
                }
            }

            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemHora1) {
                // Mostrar el diseñador en la columna U
                $hojaHora->setCellValue('U' . $filahojaHora, $rowPrimeraSemHora1['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemHora1['CEDULA_APELLIDOS']);

                // Inicializar la primera columna donde se colocarán los valores de la consulta
                $columna1 = 'V';

                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_segunda_semana as $encabezado1) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia1 = substr($encabezado1, strpos($encabezado1, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia1) {
                        case 'Lunes':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['LUNES']);
                            break;
                        case 'Martes':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['MARTES']);
                            break;
                        case 'Miércoles':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['JUEVES']);
                            break;
                        case 'Viernes':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['VIERNES']);
                            break;
                        case 'Sábado':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['SABADO']);
                            break;
                        case 'Domingo':
                            $hojaHora->setCellValue($columna1 . $filahojaHora, $rowPrimeraSemHora1['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $hojaHora->setCellValue($columna1 . $filahojaHora, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna1++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemHora2) {
                // Mostrar el diseñador en la columna AE
                $hojaHora->setCellValue('AE' . $filahojaHora, $rowPrimeraSemHora2['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemHora2['CEDULA_APELLIDOS']);
                $columna2 = 'AF';
                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_tercera_semana as $encabezado2) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia2 = substr($encabezado2, strpos($encabezado2, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia2) {
                        case 'Lunes':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['LUNES']);
                            break;
                        case 'Martes':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['MARTES']);
                            break;
                        case 'Miércoles':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['JUEVES']);
                            break;
                        case 'Viernes':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['VIERNES']);
                            break;
                        case 'Sábado':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['SABADO']);
                            break;
                        case 'Domingo':
                            $hojaHora->setCellValue($columna2 . $filahojaHora, $rowPrimeraSemHora2['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $hojaHora->setCellValue($columna2 . $filahojaHora, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna2++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemHora3) {
                // Mostrar el diseñador en la columna AO
                $hojaHora->setCellValue('AO' . $filahojaHora, $rowPrimeraSemHora3['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemHora3['CEDULA_APELLIDOS']);
                $columna3 = 'AP';
                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_cuarta_semana as $encabezado3) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia3 = substr($encabezado3, strpos($encabezado3, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia3) {
                        case 'Lunes':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['LUNES']);
                            break;
                        case 'Martes':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['MARTES']);
                            break;
                        case 'Miércoles':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['JUEVES']);
                            break;
                        case 'Viernes':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['VIERNES']);
                            break;
                        case 'Sábado':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['SABADO']);
                            break;
                        case 'Domingo':
                            $hojaHora->setCellValue($columna3 . $filahojaHora, $rowPrimeraSemHora3['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $hojaHora->setCellValue($columna3 . $filahojaHora, '');
                    }
                    $columna3++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemHora4) {
                // Mostrar el diseñador en la columna AZ

                $hojaHora->setCellValue('AZ' . $filahojaHora, $rowPrimeraSemHora4['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemHora4['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $hojaHora->setCellValue($columna_29 . $filahojaHora, $rowPrimeraSemHora4['registros_29']);
                $hojaHora->setCellValue($columna_30 . $filahojaHora, $rowPrimeraSemHora4['registros_30']);
                $hojaHora->setCellValue($columna_31 . $filahojaHora, $rowPrimeraSemHora4['registros_31']);
            }
            $filahojaHora++;
        }
        foreach (range('A', 'Z') as $columnID) {
            $hojaHora->getColumnDimension($columnID)->setAutoSize(true);
        }
        $hojaHora->getColumnDimension('AA')->setWidth(25);
        $hojaHora->getColumnDimension('AB')->setWidth(25);
        // Llamar a la función para cada rango de columnas
        applyCommonStylesToRow6($hojaHora, 'A', 'H');
        applyCommonStylesToRow6($hojaHora, 'K', 'R');
        applyCommonStylesToRow6($hojaHora, 'AE', 'AL');
        applyCommonStylesToRow6($hojaHora, 'U', 'AB');
        applyCommonStylesToRow6($hojaHora, 'AO', 'AV');
        applyCommonStylesToRow6($hojaHora, 'AZ', 'BC');
        //ALICAR  EL BORDE 
        $hojaHora->getStyle('A6:H' . $filahojaHora)->applyFromArray($styleArray);
        $hojaHora->getStyle('K6:R' . $filahojaHora)->applyFromArray($styleArray);
        $hojaHora->getStyle('AE6:AL' . $filahojaHora)->applyFromArray($styleArray);
        $hojaHora->getStyle('U6:AB' . $filahojaHora)->applyFromArray($styleArray);
        $hojaHora->getStyle('AO6:AV' . $filahojaHora)->applyFromArray($styleArray);
        $hojaHora->getStyle('AZ6:BC' . $filahojaHora)->applyFromArray($styleArray);


        // Establecer el alto de la fila 6
        $hojaHora->getRowDimension('6')->setRowHeight(70);



        // Finalmente, ajusta el índice de la hoja activa
        $excel->setActiveSheetIndex(0); // Puedes ajustar el índice según sea necesario

        // Guardar el archivo de Excel y enviarlo como descarga
        $writer = new Xlsx($excel);

        // Establecer las cabeceras para forzar la descarga del archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_DE_LOS_REGISTROS_DE_DISEÑADOR.xlsx"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo en la salida (output)
        $writer->save('php://output');

        // Registrar el movimiento en el kardex
        registrarEnKardex($_SESSION["user"]["cedula"], "Se a generado un reporte", 'REGISTROS DISEÑO', "Reporte");

        exit;
    } else {
        // Si no se enviaron los parámetros esperados, redirigir al usuario
        header("Location:./index.php");
        return;
    }
} else {
    // Si el usuario no tiene permisos para generar el reporte, redirigirlo
    header("Location:../index.php");
    return;
}
