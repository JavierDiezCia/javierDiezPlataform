<?php

if ($_SESSION["user"]["usu_rol"] != 3) {
  header('Location: ../index.php');
  return;
}

if ($_SESSION["user"]["usu_rol"] == 3) {
  // Si el rol es 3 (Diseñador), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de orden_disenio
  $registros = $conn->prepare("SELECT R.*, O.od_detalle, O.od_cliente, P.per_nombres, P.per_apellidos 
  FROM registros_disenio R 
  JOIN orden_disenio O ON R.od_id = O.od_id 
  JOIN personas P ON R.rd_diseniador = P.cedula
  JOIN usuarios U ON P.cedula = U.cedula
  WHERE U.usu_rol = 3 AND P.cedula = :cedula AND R.rd_delete = 0
  ORDER BY R.rd_id DESC
  LIMIT 10");
  $registros->execute([":cedula" => $_SESSION["user"]["cedula"]]);

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
              U.usu_rol = 3 AND P.cedula = :cedula
          GROUP BY 
              R.rd_diseniador, dia_semana;
          ";

  $consulta_horas_trabajadas = $conn->prepare($sql);
  $consulta_horas_trabajadas->execute([":cedula" => $_SESSION["user"]["cedula"]]);

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
  // Query the activities of design orders with od_estado set to "PROPUESTA"
  $consulta_actividades = $conn->prepare("SELECT A.*, O.od_cliente, O.od_detalle FROM od_actividades A
  JOIN orden_disenio O ON A.od_id = O.od_id
  WHERE O.od_estado = 'PROPUESTA' AND A.odAct_estado = 0");

  $consulta_actividades->execute();

  $actividades = array();
  while ($row = $consulta_actividades->fetch(PDO::FETCH_ASSOC)) {
    $actividad = array(
      'odAct_fechaEntrega' => $row['odAct_fechaEntrega'],
      'odAct_detalle' => $row['odAct_detalle'],
      'od_cliente' => $row['od_cliente'],
      'od_detalle' => $row['od_detalle']
    );
    $actividades[] = $actividad;
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

    


    <!-- rigth column -->
    <div class="col-lg-4">
      <div class="row">
        <div class="card">
          <div class="card-body">
            <div class="card-header">PROXIMAS ACTIVIDADES</div>
            <h5 class="col-md-4 mx-auto mb-3"></h5>
            <div>
              <?php
              setlocale(LC_TIME, 'es_ES');
              $month = date('m');
              $year = date('Y');
              $daysInMonth = date('t');
              $firstDayOfMonth = date('N', mktime(0, 0, 0, $month, 1, $year));
              $monthName = strftime('%B', mktime(0, 0, 0, $month, 1, $year));
              $monthNameSpanish = strftime('%B', mktime(0, 0, 0, $month, 1, $year));

              // Traduce el nombre del mes al español
              $monthNameSpanish = str_replace(
                ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'],
                $monthNameSpanish
              );

              echo "<h2>$monthNameSpanish <i class='bi bi-calendar2-event'></i></h2>";
              echo "<table style='border-collapse: collapse; text-align: center;'>";
              echo "<tr><th style='padding: 5px;'>Dom</th><th style='padding: 5px;'>Lun</th><th style='padding: 5px;'>Mar</th><th style='padding: 5px;'>Mié</th><th style='padding: 5px;'>Jue</th><th style='padding: 5px;'>Vie</th><th style='padding: 5px;'>Sáb</th></tr>";
              echo "<tr>";

              // Ajusta el primer día del mes según el día de la semana
              $firstDayOfMonth = ($firstDayOfMonth == 7) ? 0 : $firstDayOfMonth;

              // Imprime los días vacíos hasta el primer día del mes
              for ($i = 0; $i < $firstDayOfMonth; $i++) {
                echo "<td style='padding: 5px;'></td>";
              }

              // Imprime los días del mes
              for ($day = 1; $day <= $daysInMonth; $day++) {
                // Comprueba si estamos al principio de la semana
                if ($firstDayOfMonth == 0) {
                  echo "<tr style='text-align: center;'>";
                }

                // Comprueba si la fecha tiene una actividad
                $hasActivity = false;
                $activityDetails = '';
                foreach ($actividades as $actividad) {
                  $activityDate = date('Y-m-d', strtotime($actividad['odAct_fechaEntrega']));
                  if ($activityDate == date('Y-m-d', mktime(0, 0, 0, $month, $day, $year))) {
                    $hasActivity = true;
                    $activityDetails .= 'Cliente: ' . $actividad['od_cliente'] . ', Detalle: ' . $actividad['od_detalle'] . '\n';
                  }
                }

                // Imprime el día del mes con estilo si tiene actividad
                if ($hasActivity) {
                  if ($day == date('j') && $month == date('m') && $year == date('Y')) {
                    echo "<td style='padding: 5px; color: red; background-color: #ffa0a0;' title='$activityDetails' onmouseover='showActivityDetails(this)' onmouseout='hideActivityDetails(this)'>$day<span class='activity-details' style='display: none;'>$activityDetails</span></td>";
                  } else {
                    echo "<td style='padding: 5px; color: red;' title='$activityDetails' onmouseover='showActivityDetails(this)' onmouseout='hideActivityDetails(this)'>$day<span class='activity-details' style='display: none;'>$activityDetails</span></td>";
                  }
                } else {
                  if ($day == date('j') && $month == date('m') && $year == date('Y')) {
                    echo "<td style='padding: 5px; background-color: #ffa0a0;'>$day</td>";
                  } else {
                    echo "<td style='padding: 5px;'>$day</td>";
                  }
                }

                // Comprueba si estamos al final de la semana
                if ($firstDayOfMonth == 6) {
                  echo "</tr>";
                  $firstDayOfMonth = -1;
                }

                $firstDayOfMonth++;
              }

              // Imprime los días vacíos hasta el final de la semana
              for ($i = $firstDayOfMonth; $i < 6; $i++) {
                echo "<td style='padding: 5px;'></td>";
              }

              echo "</tr>";
              echo "</table>";
              ?>
            </div>

            <?php
            $proximasActividades = [];
            foreach ($actividades as $actividad) {
              $activityDate = date('Y-m-d', strtotime($actividad['odAct_fechaEntrega']));
              $today = date('Y-m-d');
              $diff = date_diff(date_create($today), date_create($activityDate))->format('%R%a');

              if ($diff >= 0) { // Exclude activities that have already passed
                $color = '';
                if ($diff == '+0') {
                  $color = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="red" class="bi bi-circle-fill" viewBox="0 0 16 16">
                              <circle cx="8" cy="8" r="8"/>
                            </svg>'; // hoy
                } elseif ($diff == '+1') {
                  $color = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="yellow" class="bi bi-circle-fill" viewBox="0 0 16 16">
                              <circle cx="8" cy="8" r="8"/>
                            </svg>'; // mañana
                } elseif ($diff > '+1') {
                  $color = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="green" class="bi bi-circle-fill" viewBox="0 0 16 16">
                              <circle cx="8" cy="8" r="8"/>
                            </svg>'; // más de dos días después
                }

                $proximasActividades[] = [
                  'od_cliente' => $actividad['od_cliente'],
                  'odAct_detalle' => $actividad['odAct_detalle'],
                  'odAct_fechaEntrega' => $actividad['odAct_fechaEntrega'],
                  'color' => $color
                ];
              }
            }

            if (!empty($proximasActividades)) {
              echo "<hr>";
              echo "<h3>Próximas actividades:</h3>";
              echo "<div>";
              foreach ($proximasActividades as $actividad) {
                $fechaEntrega = date('l j', strtotime($actividad['odAct_fechaEntrega']));
                $today = date('Y-m-d');
                $diff = date_diff(date_create($today), date_create($actividad['odAct_fechaEntrega']))->format('%R%a');

                if ($diff == '+0') {
                  $fechaEntrega = 'hoy';
                } elseif ($diff == '+1') {
                  $fechaEntrega = 'mañana';
                }

                echo "<p style='margin-bottom: 1px;'>{$actividad['color']} {$actividad['od_cliente']} - {$actividad['odAct_detalle']} | {$fechaEntrega}</p>";
              }
              echo "</div>";
              echo "<hr>";
            } else {
              echo "<p>No hay próximas actividades.</p>";
              echo "<hr>";
            }
            $actividadesPasadas = [];
            $currentMonth = date('m');
            foreach ($actividades as $actividad) {
              $activityDate = date('Y-m-d', strtotime($actividad['odAct_fechaEntrega']));
              $activityMonth = date('m', strtotime($actividad['odAct_fechaEntrega']));
              $today = date('Y-m-d');
              $diff = date_diff(date_create($today), date_create($activityDate))->format('%R%a');

              if ($diff < 0 && $activityMonth == $currentMonth) { // Include activities that have already passed and are from the current month
                $color = '';
                if ($diff == '-1') {
                  $color = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="gray" class="bi bi-circle-fill" viewBox="0 0 16 16">
                              <circle cx="8" cy="8" r="8"/>
                            </svg>'; // ayer
                } else {
                  $color = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="gray" class="bi bi-circle-fill" viewBox="0 0 16 16">
                              <circle cx="8" cy="8" r="8"/>
                            </svg>'; // más de un día antes
                }

                $actividadesPasadas[] = [
                  'od_cliente' => $actividad['od_cliente'],
                  'odAct_detalle' => $actividad['odAct_detalle'],
                  'odAct_fechaEntrega' => $actividad['odAct_fechaEntrega'],
                  'color' => $color
                ];
              }
            }

            if (!empty($actividadesPasadas)) {
              echo "<h3>Actividades pasadas:</h3>";
              echo "<div>";
              foreach ($actividadesPasadas as $actividad) {
                $fechaEntrega = date('l j', strtotime($actividad['odAct_fechaEntrega']));
                $today = date('Y-m-d');
                $diff = date_diff(date_create($today), date_create($actividad['odAct_fechaEntrega']))->format('%R%a');

                if ($diff == '-1') {
                  $fechaEntrega = 'ayer';
                }

                echo "<p style='margin-bottom: 1px;'>{$actividad['color']} {$actividad['od_cliente']} - {$actividad['odAct_detalle']} | {$fechaEntrega}</p>";
                
              }
              echo "</div>";
              echo "<hr>";
            } else {
              echo "<p>No hay actividades pasadas.</p>";
            }
            ?>
                    
          </div>
        </div>
      </div>
    </div> 
      
  </div>
</section>
