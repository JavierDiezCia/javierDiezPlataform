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
        <?php require_once "./partials/components/kardex/kardexSA.php"; ?>
    </div><!-- End Right side columns -->
    
    </div>
</section>