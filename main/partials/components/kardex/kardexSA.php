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
        <style>
            .activity {
                max-height: 300px; /* Establece la altura máxima para mostrar el scroll */
                overflow-y: auto; /* Agrega scroll vertical si el contenido excede la altura máxima */
                padding-right: 15px; /* Ajusta el padding derecho para evitar que el contenido se superponga al scroll */
            }
        </style>
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
</div>
