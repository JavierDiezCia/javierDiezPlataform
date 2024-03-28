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
        <div class="filter">
            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
                <h6>Filter</h6>
            </li>

            <li><a class="dropdown-item" href="#">Today</a></li>
            <li><a class="dropdown-item" href="#">This Month</a></li>
            <li><a class="dropdown-item" href="#">This Year</a></li>
            </ul>
        </div>

        <div class="card-body">
            <h5 class="card-title">ACTIVIDAD RECIENTE <span>| TODAY</span></h5>

            <div class="activity">
            
            <?php foreach($kardex as $kar) : ?>
                <?php
                // PARA CALCULAR EL TIEMPO DE CADA ACCION
                $fechaMovimiento = new DateTime($kar["kar_fecha"]);
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
                ?>
            <div class="activity-item d-flex">
                <div class="activite-label"><?= $tiempoTranscurrido ?></div>
                <i class='bi bi-circle-fill activity-badge align-self-start 
                <?php if ($kar["kar_accion"] == "ELIMINÓ") :?>
                text-danger
                <?php elseif ($kar["kar_accion"] == "CREÓ") : ?>
                text-success
                <?php elseif ($kar["kar_accion"] == "EDITÓ") : ?>
                text-warning
                <?php elseif ($kar["kar_accion"] == "RESTAURÓ") : ?>
                text-primary
                <?php else : ?>
                text-muted
                <?php endif ?>
                '></i>
                <div class="activity-content">
                <?= $kar["kar_cedula"]?> <b><?= $kar["kar_accion"]?></b> UN REGISTRO DE LA TABLA <b><?= $kar["kar_tabla"]?></b><br>
                DATO : <?= $kar["kar_idRow"]?><br>
                FECHA: <?= $kar["kar_fecha"]?>
                </div>
            </div><!-- End activity item-->

            <?php endforeach ?>

            </div>

        </div>
        </div><!-- End Recent Activity -->

        </div>
        </div><!-- End News & Updates -->

    </div><!-- End Right side columns -->

    </div>
</section>