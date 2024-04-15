<?php
    // Obtener la fecha actual
    $fechaActual = new DateTime();

    // Si se proporciona una fecha de inicio en la URL, usar esa fecha en lugar de la fecha actual
    if (isset($_GET['fechaInicio'])) {
        $fechaActual = new DateTime($_GET['fechaInicio']);
    }

    // Establecer la fecha de inicio al lunes de la semana actual
    $fechaInicio = clone $fechaActual;
    $fechaInicio->setISODate($fechaInicio->format('Y'), $fechaInicio->format('W'));

    // Calcular la fecha de fin de la semana
    $fechaFin = clone $fechaInicio;
    $fechaFin->modify('+6 days 23 hours 59 minutes 59 seconds');

    // Consulta SQL para obtener las horas trabajadas por día
    $sql = "SELECT 
            R.rd_diseniador,
            P.per_nombres,
            P.per_apellidos,
            WEEKDAY(R.rd_hora_ini) AS dia_semana,
            SUM(TIME_TO_SEC(TIMEDIFF(R.rd_hora_fin, R.rd_hora_ini))) / 3600 AS total_horas
        FROM 
            registros_disenio R
            JOIN personas P ON R.rd_diseniador = P.cedula
            JOIN usuarios U ON P.cedula = U.cedula
        WHERE 
            U.usu_rol = 3 AND
            rd_delete = 0 AND
            R.rd_hora_ini >= :fechaInicio AND
            R.rd_hora_fin <= DATE_ADD(:fechaFin, INTERVAL '23:59:59' HOUR_SECOND)
        GROUP BY 
            R.rd_diseniador, P.per_nombres, P.per_apellidos, dia_semana;
        ";

    $consulta_horas_trabajadas = $conn->prepare($sql);
    $consulta_horas_trabajadas->execute([
        ':fechaInicio' => $fechaInicio->format('Y-m-d'),
        ':fechaFin' => $fechaFin->format('Y-m-d')
    ]);

    // Inicializar array multidimensional para almacenar las horas trabajadas por día
    $horas_trabajadas_por_dia = array();

    while ($row = $consulta_horas_trabajadas->fetch(PDO::FETCH_ASSOC)) {
        $diseniador = $row['per_nombres'] . ' ' . $row['per_apellidos'];
        $dia_semana = $row['dia_semana'];
        $total_horas = $row['total_horas'];

        // Si no existe un array para este diseñador, inicializarlo
        if (!isset($horas_trabajadas_por_dia[$diseniador])) {
            $horas_trabajadas_por_dia[$diseniador] = array_fill(0, 7, 0);
        }

        // Agregar las horas trabajadas al array del diseñador correspondiente
        $horas_trabajadas_por_dia[$diseniador][$dia_semana] = $total_horas;
    }

    // Consulta SQL para obtener el número de registros por día
    $sql = "SELECT 
            R.rd_diseniador,
            P.per_nombres,
            P.per_apellidos,
            WEEKDAY(R.rd_hora_ini) AS dia_semana,
            COUNT(*) AS num_registros
        FROM 
            registros_disenio R
            JOIN personas P ON R.rd_diseniador = P.cedula
            JOIN usuarios U ON P.cedula = U.cedula
        WHERE 
            U.usu_rol = 3 AND
            rd_delete = 0 AND
            R.rd_hora_ini >= :fechaInicio AND
            R.rd_hora_fin <= DATE_ADD(:fechaFin, INTERVAL '23:59:59' HOUR_SECOND)
        GROUP BY 
            R.rd_diseniador, P.per_nombres, P.per_apellidos, dia_semana;
        ";

    $consulta_registros_por_dia = $conn->prepare($sql);
    $consulta_registros_por_dia->execute([
        ':fechaInicio' => $fechaInicio->format('Y-m-d'),
        ':fechaFin' => $fechaFin->format('Y-m-d')
    ]);

    // Inicializar array multidimensional para almacenar el número de registros por día
    $registros_por_dia = array();

    while ($row = $consulta_registros_por_dia->fetch(PDO::FETCH_ASSOC)) {
        $diseniador = $row['per_nombres'] . ' ' . $row['per_apellidos'];
        $dia_semana = $row['dia_semana'];
        $num_registros = $row['num_registros'];

        // Si no existe un array para este diseñador, inicializarlo
        if (!isset($registros_por_dia[$diseniador])) {
            $registros_por_dia[$diseniador] = array_fill(0, 7, 0);
        }

        // Agregar el número de registros al array del diseñador correspondiente
        $registros_por_dia[$diseniador][$dia_semana] = $num_registros;
    }
?>



<div id="columnChart"></div>

<h4># REGISTROS</h4>
<!-- hacer otro chart por el numero de registros realizados en el dia -->
<div id="chartRegistros"></div>

<div>
    <div class="d-flex justify-content-between mt-3">
        <button id="semanaAnterior" class="btn btn-primary">Semana Anterior</button>
        <button id="semanaSiguiente" class="btn btn-primary">Semana Siguiente</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Obtener los botones de la semana anterior y la próxima semana
    var btnSemanaAnterior = document.querySelector("#semanaAnterior");
    var btnProximaSemana = document.querySelector("#semanaSiguiente");

    // Obtener la fecha de inicio de la semana actual
    var fechaInicio = new Date(<?php echo $fechaInicio->format('Y, m - 1, d'); ?>);
    // Obtener la fecha de fin de la semana actual
    var fechaFin = new Date(<?php echo $fechaFin->format('Y, m - 1, d'); ?>);

    // Función para actualizar la página con la nueva fecha de inicio
    function actualizarPagina(fecha) {
        window.location.search = '?fechaInicio=' + fecha.toISOString().split('T')[0];
    }

    // Agregar evento de clic al botón de la semana anterior
    btnSemanaAnterior.addEventListener("click", () => {
        // Restar una semana a la fecha de inicio
        fechaInicio.setDate(fechaInicio.getDate() - 7);

        // Actualizar la página con la nueva fecha de inicio
        actualizarPagina(fechaInicio);
    });

    // Agregar evento de clic al botón de la próxima semana
    btnProximaSemana.addEventListener("click", () => {
        // Sumar una semana a la fecha de inicio
        fechaInicio.setDate(fechaInicio.getDate() + 7);

        // Actualizar la página con la nueva fecha de inicio
        actualizarPagina(fechaInicio);
    });

    // Obtener los datos de las horas trabajadas por día
    var horasTrabajadas = <?php echo json_encode($horas_trabajadas_por_dia); ?>;

    // Inicializar los datos para el gráfico
    var seriesData = [];
    var diasSemana = ['Lun', 'Mar', 'Miér', 'Jue', 'Vie', 'Sáb', 'Dom'];

    // Convertir los totales por diseñador a series para el gráfico
    for (var diseniador in horasTrabajadas) {
        seriesData.push({
            name: diseniador,
            data: horasTrabajadas[diseniador]
        });
    }

    // Crear el gráfico con los datos obtenidos
    var chart = new ApexCharts(document.querySelector("#columnChart"), {
        series: seriesData,
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: diasSemana,
        },
        yaxis: {
            title: {
                text: 'SEMANA desde ' + fechaInicio.toLocaleDateString() + ' hasta ' + fechaFin.toLocaleDateString()
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " horas"
                }
            }
        }
    });

    // Renderizar el gráfico
    chart.render();

    // Obtener los datos de los registros por día
    var registrosPorDia = <?php echo json_encode($registros_por_dia); ?>;

    // Inicializar los datos para el gráfico
    var seriesDataRegistros = [];

    // Convertir los totales por diseñador a series para el gráfico
    for (var diseniador in registrosPorDia) {
        seriesDataRegistros.push({
            name: diseniador,
            data: registrosPorDia[diseniador]
        });
    }

    // Crear el gráfico con los datos obtenidos
    var chartRegistros = new ApexCharts(document.querySelector("#chartRegistros"), {
        series: seriesDataRegistros,
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: diasSemana,
        },
        yaxis: {
            title: {
                text: 'SEMANA desde ' + fechaInicio.toLocaleDateString() + ' hasta ' + fechaFin.toLocaleDateString()
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " Registros"
                }
            }
        }
    });

    // Renderizar el gráfico
    chartRegistros.render();

});
</script>



<!-- peticion al AJAX.PHP -->
<script>
    btnSemanaAnterior.addEventListener("click", function(event) {
        event.preventDefault();
        fechaInicio.setDate(fechaInicio.getDate() - 7);
        fechaFin.setDate(fechaFin.getDate() - 7);
        fetch(`Ajax.php?fechaInicio=${fechaInicio.toISOString().split('T')[0]}&fechaFin=${fechaFin.toISOString().split('T')[0]}`)
            .then(response => response.json())
            .then(data => {
                // Actualizar los datos del gráfico
                chart.updateSeries([{
                    name: 'Horas Trabajadas',
                    data: data.map(item => item.total_horas)
                }]);
            });
    });

    btnProximaSemana.addEventListener("click", function(event) {
        event.preventDefault();
        fechaInicio.setDate(fechaInicio.getDate() + 7);
        fechaFin.setDate(fechaFin.getDate() + 7);
        fetch(`Ajax.php?fechaInicio=${fechaInicio.toISOString().split('T')[0]}&fechaFin=${fechaFin.toISOString().split('T')[0]}`)
            .then(response => response.json())
            .then(data => {
                // Actualizar los datos del gráfico
                chart.updateSeries([{
                    name: 'Horas Trabajadas',
                    data: data.map(item => item.total_horas)
                }]);
            });
    });

    function actualizarDatos(event) {
        event.preventDefault();
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "Ajax.php?fechaInicio=" + fechaInicio.toISOString().split('T')[0] + "&fechaFin=" + fechaFin.toISOString().split('T')[0], true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var nuevosDatos = JSON.parse(xhr.responseText);
                // Actualizar los datos del gráfico
                chart.updateSeries([{
                    name: 'Horas trabajadas',
                    data: nuevosDatos
                }]);
            }
        };
        xhr.send();
    }
</script>
