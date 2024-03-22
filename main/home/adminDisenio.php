<?php

if ($_SESSION["user"]["usu_rol"] != 2) {
    header('Location: ../index.php');
    return;
}

if ($_SESSION["user"]["usu_rol"] == 2) {
    // Definir los nombres de los días de la semana
    $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    // Si el rol es 2 (Diseñador ADMIN), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de orden_disenio
    $registros = $conn->prepare("SELECT R.od_id, R.rd_hora_ini, R.rd_hora_fin, R.rd_detalle, O.od_detalle, O.od_cliente, P.per_nombres, P.per_apellidos 
    FROM registros_disenio R 
    JOIN orden_disenio O ON R.od_id = O.od_id 
    JOIN personas P ON R.rd_diseniador = P.cedula
    JOIN usuarios U ON P.cedula = U.cedula
    WHERE R.rd_delete = 0
    ORDER BY R.rd_id DESC
    LIMIT 6");
    $registros->execute();

    // Obtenemos los nombres de todos los usuarios con el rol 3
    $usuarios_rol_3 = $conn->prepare("SELECT P.per_nombres, P.per_apellidos
        FROM personas P 
        JOIN usuarios U ON P.cedula = U.cedula
        WHERE U.usu_rol = 3");
    $usuarios_rol_3->execute();

    // Creamos un array para almacenar los nombres de los usuarios con rol 3
    $nombres_usuarios_rol_3 = [];
    while ($row = $usuarios_rol_3->fetch(PDO::FETCH_ASSOC)) {
        $nombres_usuarios_rol_3[] = $row["per_nombres"] . " " . $row["per_apellidos"];
    }


    // Consulta SQL para obtener las horas trabajadas por día
    $sql = "SELECT 
                R.rd_diseniador,
                DAYOFWEEK(R.rd_hora_ini) AS dia_semana,
                SUM(TIME_TO_SEC(TIMEDIFF(R.rd_hora_fin, R.rd_hora_ini))) AS total_segundos
            FROM 
                registros_disenio R
                JOIN personas P ON R.rd_diseniador = P.cedula
                JOIN usuarios U ON P.cedula = U.cedula
            WHERE 
                U.usu_rol = 3
            GROUP BY 
                R.rd_diseniador, dia_semana;
            ";

    $consulta_horas_trabajadas = $conn->prepare($sql);
    $consulta_horas_trabajadas->execute();

    // Inicializar array multidimensional para almacenar las horas trabajadas por día
    $horas_trabajadas_por_dia = array(
        1 => array(),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array(),
        7 => array()
    );

    while ($row = $consulta_horas_trabajadas->fetch(PDO::FETCH_ASSOC)) {
        $dia_semana = $row['dia_semana'];
        $total_segundos = $row['total_segundos'];

        // Agregar los segundos trabajados al array del día correspondiente
        $horas_trabajadas_por_dia[$dia_semana][] = $total_segundos;
    }

}

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

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        <div class="card-header">ULTIMOS REGISTROS</div>
                            <h5 class="col-md-4 mx-auto mb-3"></h5>

                            <?php if ($registros->rowCount() == 0) : ?>
                                <div class="col-md-4 mx-auto mb-3">
                                    <div class="card card-body text-center">
                                        <p>NO HAY REGISTROS AÚN</p>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th># OD</th>
                                            <th>DISEÑADOR</th>
                                            <th>DETALLE</th>
                                            <th>CLIENTE</th>
                                            <th>ACTIVIDAD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registros as $registros) : ?>

                                            <tr>
                                                <th><?= $registros["od_id"] ?></th>
                                                <th><?= $registros["per_nombres"] . " " . $registros["per_apellidos"] ?></th>
                                                <th><?= $registros["od_detalle"] ?></th>
                                                <th><?= $registros["od_cliente"] ?></th>
                                                <th><?= $registros["rd_detalle"] ?></th>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        

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