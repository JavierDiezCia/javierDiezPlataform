 
<?php 

require "../sql/database.php";
require "./partials/session_handler.php"; 

//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
  header("Location: ../login-form/login.php");
  return;
}

$totalFilas = $conn->query("SELECT COUNT(*) AS total_filas FROM personas WHERE per_estado = 1")->fetchColumn();
$kardex = $conn->query("SELECT * FROM kardex ORDER BY kar_id DESC LIMIT 10");
$op = $conn->query("SELECT op.*, 
                  od.od_comercial AS vendedor, 
                  od.od_detalle AS detalle,
                  od.od_cliente AS cliente,
                  cedula.per_nombres AS cedula_nombres, 
                  cedula.per_apellidos AS cedula_apellidos,
                  COUNT(planos.pla_id) AS numero_planos
                  FROM op
                  LEFT JOIN orden_disenio AS od ON op.od_id = od.od_id
                  LEFT JOIN personas AS cedula ON od.od_responsable = cedula.cedula
                  LEFT JOIN personas AS op_vendedor ON od.od_comercial = op_vendedor.cedula
                  LEFT JOIN planos ON op.op_id = planos.op_id
                  GROUP BY op.op_id 
                  ORDER BY op.op_id DESC;
                  "
);

date_default_timezone_set('America/Lima'); 

if ($_SESSION["user"]["usu_rol"] == 2) {
  // Definir los nombres de los días de la semana
  $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

  // Si el rol es 2 (Diseñador ADMIN), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de orden_disenio
  $registros = $conn->prepare("SELECT R.*, O.od_detalle, O.od_cliente, P.per_nombres, P.per_apellidos 
  FROM registros_disenio R 
  JOIN orden_disenio O ON R.od_id = O.od_id 
  JOIN personas P ON R.rd_diseniador = P.cedula
  JOIN usuarios U ON P.cedula = U.cedula
  WHERE U.usu_rol = 3
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

} elseif ($_SESSION["user"]["usu_rol"] == 3) {
  # code...
}

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>



  <?php if ($_SESSION["user"]["usu_rol"] == 1) : ?>
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
                  <h5 class="card-title">TRABAJADORES <span>|</span></h5>

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

                    <li><a class="dropdown-item" href="#">TODAY</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">ÓRDENES DE PRODUCCIÓN RECIENTES. <span>| </span></h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th scope="col"># OP</th>
                        <th scope="col">CLIENTE</th>
                        <th scope="col">DETALLE</th>
                        <th scope="col">PLANOS</th>
                        <th scope="col">ESTADO</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($op as $op) : ?>
                        <tr>
                          <th scope="row"><a href="#"><?= $op["op_id"] ?> </a></th>
                          <td><?= $op["op_cliente"] ?></td>
                          <td><a href="#" class="text-primary"><?= $op["op_detalle"] ?></a></td>
                          <td><?= $op["numero_planos"] ?></td>
                          <td><?= $op["op_estado"] ?></td>
                        </tr>
                      <?php endforeach ?>
                    </tbody>
                  </table>

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


  <?php elseif ($_SESSION["user"]["usu_rol"] == 2) : ?>

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

  <?php endif ?>

  <?php require "./partials/footer.php"; ?>
