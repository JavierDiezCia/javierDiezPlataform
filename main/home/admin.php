<?php

if ($_SESSION["user"]["usu_rol"] != 1) {
    header('Location: ../index.php');
    return;
}

$totalFilas = $conn->query("SELECT COUNT(*) AS total_filas FROM personas WHERE per_estado = 1")->fetchColumn();
$kardex = $conn->query("SELECT * FROM kardex ORDER BY kar_id DESC LIMIT 10");


?>



<div class="pagetitle">
    <h1>DASHBOARD</h1>
    <nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
        <li class="breadcrumb-item active">DASHBOARD</li>
    </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section dashboard">
    <div class="row">

    <!-- Left side columns -->
    <div class="col-lg-8">
        <div class="row">

        
        <!-- Customers Card -->
        <div class="col-xxl-4 col-xl-12">

            <div class="card info-card customers-card">

            

            <div class="card-body">
                <h5 class="card-title">TRABAJADORES</h5>

                <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-people"></i>
                </div>
                <div class="ps-3">
                    <h6><?= $totalFilas ?></h6>
                    <span class="text-danger small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1">PERSONAS</span>

                </div>
                </div>

            </div>
            </div>

        </div><!-- End Customers Card -->

        <!-- Recent Sales -->
        <div class="col-12">
            <div class="card recent-sales overflow-auto">

            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                    <h6>FILTER</h6>
                </li>

                <li><a class="dropdown-item selected" href="?filter=hoy">HOY</a></li>
                <li><a class="dropdown-item" href="?filter=semana">ESTA SEMANA</a></li>
                <li><a class="dropdown-item" href="?filter=mes">ESTE MES</a></li>
                </ul>
                </div>

                <div class="card-body">
                    <h5 class="card-title">ÓRDENES DE PRODUCCIÓN RECIENTES.</h5>

                    <!-- poner un label para saber de acuerdo a que estoy filtrando -->
                    <?php
                    if (isset($_GET['filter'])) {
                        $filter = $_GET['filter'];

                        switch ($filter) {
                            case 'hoy':
                                echo "<span class='badge bg-primary'>HOY</span>";
                                break;
                            case 'semana':
                                echo "<span class='badge bg-primary'>ESTA SEMANA</span>";
                                break;
                            case 'mes':
                                echo "<span class='badge bg-primary'>ESTE MES</span>";
                                break;
                            default:
                                echo "<span class='badge bg-primary'>HOY</span>";
                                break;
                        }
                    } else {
                        echo "<span class='badge bg-primary'>HOY</span>";
                    }
                    ?>

                    <?php
                    if (isset($_GET['filter'])) {
                        $filter = $_GET['filter'];

                        switch ($filter) {
                            case 'hoy':
                                require "./partials/components/filterOP/odHoy.php";
                                break;
                            case 'semana':
                                require "./partials/components/filterOP/odSemana.php";
                                break;
                            case 'mes':
                                require "./partials/components/filterOP/odMes.php";
                                break;
                            default:
                                require "./partials/components/filterOP/odHoy.php";
                                break;
                        }
                    } else {
                        require "./partials/components/filterOP/odHoy.php";
                    }
                    ?>
                </div>

            </div>
        </div><!-- End Recent Sales -->

        

        </div>
    </div><!-- End Left side columns -->

            <!-- Right side columns -->
            <div class="col-lg-4">
            <!-- Recent Activity -->
            <div class="card">
            <div class="card body">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item filter-option" href="?filtro=HOY" data-filter="HOY">HOY</a></li>
                        <li><a class="dropdown-item filter-option" href="?filtro=ESTA_SEMANA" data-filter="ESTA SEMANA">ESTA SEMANA</a></li>
                        <li><a class="dropdown-item filter-option" href="?filtro=ESTE_MES" data-filter="ESTE MES">ESTE MES</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <h5 class="card-title">ACTIVIDAD RECIENTE <span id="filter-label">| HOY</span></h5>
                    <div class="activity">
                        <?php
                        try {
                            // Consulta SQL para obtener los datos del kardex
                            $stmt = $conn->query("SELECT * FROM kardex");
                            $kardex = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Definir los valores de filtro disponibles
                            $filtrosDisponibles = ['HOY', 'ESTA SEMANA', 'ESTE MES'];

                            // Establecer el filtro automáticamente según la selección o el valor predeterminado
                            $filtro = isset($_GET['filtro']) && in_array(strtoupper($_GET['filtro']), $filtrosDisponibles) ? strtoupper($_GET['filtro']) : "HOY";
                            // Guardar el filtro seleccionado en una variable JavaScript para mantenerlo al cargar la página
                            echo '<script>const selectedFilter = "' . $filtro . '";</script>';
                            // Filtrar kardex según el filtro seleccionado o el predeterminado
                            switch ($filtro) {
                                case 'HOY':
                                    $kardex_filtrado = array_filter($kardex, function($item) {
                                        $fechaItem = new DateTime($item['kar_fecha']);
                                        $fechaActual = new DateTime();
                                        return $fechaItem->format('Y-m-d') === $fechaActual->format('Y-m-d');
                                    });
                                    break;
                                case 'ESTA SEMANA':
                                    $today = new DateTime();
                                    // Obtener el primer día de la semana actual
                                    $startOfWeek = clone $today;
                                    $startOfWeek->modify('this week');
                                    // Obtener el último día de la semana actual
                                    $endOfWeek = clone $startOfWeek;
                                    $endOfWeek->modify('next week');
                                    // Filtrar los artículos que están dentro de la semana actual
                                    $kardex_filtrado = array_filter($kardex, function($item) use ($startOfWeek, $endOfWeek) {
                                        $fechaItem = new DateTime($item['kar_fecha']);
                                        return $fechaItem >= $startOfWeek && $fechaItem < $endOfWeek;
                                    });
                                    break;
                                case 'ESTE MES':
                                    $kardex_filtrado = array_filter($kardex, function($item) {
                                        $fechaItem = new DateTime($item['kar_fecha']);
                                        $fechaActual = new DateTime();
                                        // Comparar solo el año y el mes
                                        return $fechaItem->format('Y-m') === $fechaActual->format('Y-m');
                                    });
                                    break;
                            default:
                                    // Si el filtro no coincide con ninguno de los casos anteriores, mostrar un mensaje de error
                                    echo "Filtro no válido";
                                    exit(); // Salir del script
                            }

                            // Mostrar resultados si no está vacío
                            if (!empty($kardex_filtrado)) {
                                foreach ($kardex_filtrado as $item) {
                                    $fechaMovimiento = new DateTime($item["kar_fecha"]);
                                    $fechaActual = new DateTime();

                                    // Calcula la diferencia entre las dos fechas
                                    $diferencia = $fechaActual->diff($fechaMovimiento);

                                    // Accede a los componentes de la diferencia
                                    $horas = $diferencia->h;
                                    $minutos = $diferencia->i;

                                    // Formatea el resultado
                                    $tiempoTranscurrido = '';
                                    if ($horas > 0) {
                                        $tiempoTranscurrido .= $horas . ' h ';
                                    }
                                    $tiempoTranscurrido .= $minutos . ' min';

                                    echo "<div class='activity-item d-flex'>";
                                    echo "<div class='activite-label'>$tiempoTranscurrido</div>";
                                    echo "<i class='bi bi-circle-fill activity-badge align-self-start";
                                    if ($item["kar_accion"] == "ELIMINÓ") {
                                        echo " text-danger";
                                    } elseif ($item["kar_accion"] == "CREÓ") {
                                        echo " text-success";
                                    } elseif ($item["kar_accion"] == "EDITÓ") {
                                        echo " text-warning";
                                    } elseif ($item["kar_accion"] == "RESTAURÓ") {
                                        echo " text-primary";
                                    } else {
                                        echo " text-muted";
                                    }
                                    echo "'></i>";
                                    echo "<div class='activity-content'>";
                                    echo $item["kar_cedula"] . "</b><br><b>" . $item["kar_accion"] . "</b> UN REGISTRO DE LA TABLA <b>" . $item["kar_tabla"] . "</b><br>";
                                    echo "DATO : " . $item["kar_idRow"] . "<br>";
                                    echo "FECHA: " . $item["kar_fecha"];
                                    echo "</div></div>"; // End activity item
                                }
                            } else {
                                // Mostrar un mensaje de que no hay actividades para el filtro seleccionado
                                switch ($filtro) {
                                    case 'HOY':
                                        echo "No hay actividades registradas para hoy.";
                                        break;
                                    case 'ESTA SEMANA':
                                        echo "No hay actividades registradas para esta semana.";
                                        break;
                                    case 'ESTE MES':
                                        echo "No hay actividades registradas para este mes.";
                                        break;
                                    default:
                                        echo "Filtro no válido";
                                        break;
                                }
                            }
                        } catch(PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>
                    </div>   
                </div> 
            </div>
            </div><!-- End Recent Activity -->

            <script>
                // Obtener el elemento de filtro
                const filterOptionLinks = document.querySelectorAll('.filter-option');

                // Iterar sobre cada enlace de opción de filtro
                filterOptionLinks.forEach(function(link) {
                    // Agregar un controlador de eventos de clic
                    link.addEventListener('click', function(event) {
                        // Evitar el comportamiento predeterminado del enlace
                        event.preventDefault();

                        // Obtener el texto de la opción de filtro seleccionada
                        const selectedFilter = this.getAttribute('data-filter');

                        // Actualizar el texto del título
                        document.getElementById('filter-label').textContent = '| ' + selectedFilter;

                        // Obtener la URL actual
                        let currentUrl = window.location.href;

                        // Eliminar cualquier parámetro 'filtro' existente de la URL
                        currentUrl = currentUrl.replace(/[?&]filtro=[^&#]*/g, '');

                        // Verificar si ya existe un parámetro en la URL
                        if (currentUrl.indexOf('?') === -1) {
                            // Si no hay ningún parámetro, agregar uno
                            currentUrl += '?filtro=' + encodeURIComponent(selectedFilter);
                        } else {
                            // Si ya existe un parámetro, agregar el filtro al parámetro existente
                            currentUrl += '&filtro=' + encodeURIComponent(selectedFilter);
                        }

                        // Redireccionar a la URL actualizada
                        window.location.href = currentUrl;
                    });
                });

                // Actualizar el texto del título con el filtro seleccionado al cargar la página
                window.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('filter-label').textContent = '| ' + selectedFilter;
                });
            </script>
        </div>
        </div>
        </div><!-- End News & Updates -->

        </div><!-- End Right side columns -->


    </div>
</section>