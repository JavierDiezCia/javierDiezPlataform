<?php
$dataUser = $conn->query("SELECT * FROM personas WHERE cedula = {$_SESSION["user"]["cedula"]} LIMIT 1");
$data = $dataUser->fetch(PDO::FETCH_ASSOC);

$totalNotificaciones = 0;

$notis = [];

$tiempoTranscurrido = new DateTime('2022-01-01 00:00:00');
$tiempoTranscurrido->modify('-1 day');

// Prepara la consulta SQL
$stmt = $conn->prepare("SELECT N.*, NV.* FROM notificaciones N
                        JOIN noti_visualizaciones NV ON N.noti_id = NV.noti_id
                        WHERE noti_destinatario = :destinatario AND notiVis_cedula = :cedula
                        ORDER BY noti_fecha DESC LIMIT 50");
$stmt->bindParam(':destinatario', $_SESSION['user']['usu_rol']);
$stmt->bindParam(':cedula', $_SESSION["user"]["cedula"]);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultado) {
  $notis = $resultado;
  $totalNotificaciones = 0;
  foreach ($notis as $noti) {
    if ($noti['notiVis_vista'] == 0) {
      $totalNotificaciones++;
    }
  }
}

date_default_timezone_set('America/Lima'); 
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- BOOTSTRAP LIBRARIES -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.3.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
  <script refer src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script refer src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script refer src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script refer src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Jquery -->
  <script refer src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
  $(document).ready(function(){
      // Actualizar el número de notificaciones cada 5 segundos
      setInterval(function(){
          $.ajax({
              url: 'get_notifications.php',
              type: 'GET',
              success: function(response) {
                  // Actualizar la interfaz de usuario con el número de notificaciones
                  $('#notification-count').text(response);
              }
          });
      }, 5000); // Actualizar cada 5 segundos

      // Manejar el clic en el enlace "Marcar como leída"
      $(".mark-as-read").click(function(e){
          e.preventDefault();

          var href = $(this).attr('href');
          var link = $(this); // Guardar una referencia al enlace

          $.ajax({
              url: href,
              type: 'GET',
              success: function(response) {
                  // Usar la referencia al enlace en la función de éxito
                  link.remove();

                  // Actualizar el número de notificaciones
                  $.ajax({
                      url: 'get_notifications.php',
                      type: 'GET',
                      success: function(response) {
                          // Actualizar la interfaz de usuario con el número de notificaciones
                          $('#notification-count').text(response);
                      }
                  });
              }
          });
      });
  });
  </script>

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="https://www.javierdiez.com/wp-content/uploads/2022/01/LOGO-JD-RED.jpeg" alt="LOGO">
        <span class="d-none d-lg-block">JavierDiez</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

      <li class="nav-item dropdown">
        <?php if(!empty($totalNotificaciones)) : ?>
            <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger badge-number"><?= $totalNotificaciones ?></span>
            </a><!-- End Notification Icon -->
        <?php else : ?>
            <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="badge badge-number"></span>
            </a><!-- End Notification Icon -->
        <?php endif ?>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" style='width: 300px;'>
            <?php if(empty($notis)) : ?>
            <?php endif ?>

            <li class="dropdown-header">
                Tienes <?= $totalNotificaciones ?> notificaciones sin ver.
            </li>
            
            <li class="text-center mb-1">
                <a href="historialNotis.php" class='btn btn-light'>Ver todas las notificaciones</a>
            </li>
            
            <li>
                <hr class="dropdown-divider">
            </li>

            <?php foreach($notis as $noti) : ?>
                <li class="notification-item <?= $noti['notiVis_vista'] == 0 ? 'not-viewed' : '' ?>">
                
                <div>
                    <h4><?= date('l j \d\e F \|\ H:i', strtotime($noti['noti_fecha'])) ?></h4>
                    <h4><?= $noti['noti_detalle'] ?></h4>
                    <?php if ($noti['notiVis_vista'] == 0) : ?>
                      <a href="validaciones/notificacionVisual.php?id=<?= $noti['noti_id'] ?>&dni=<?= $noti['notiVis_cedula'] ?>" class="btn btn-secondary mark-as-read">Marcar como vista</a>
                    <?php endif ?>
                </div>
                </li>
                <hr>
            <?php endforeach ?>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->
        

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="https://static.vecteezy.com/system/resources/previews/005/005/788/original/user-icon-in-trendy-flat-style-isolated-on-grey-background-user-symbol-for-your-web-site-design-logo-app-ui-illustration-eps10-free-vector.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?= $data["per_nombres"] ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?= $data["per_apellidos"] . " " . $data["per_nombres"] ?></h6>
              <span>
              <?php 
                $roles = [
                  1 => "Super Administrador",
                  2 => "Admi Diseño",
                  3 => "Diseñadores",
                  4 => "Admi Producción",
                  5 => "Producción",
                  6 => "Personal",
                  7 => "Presentacion"
                ];
                echo $roles[$_SESSION["user"]["usu_rol"]];
              ?>
              </span>
              <span><?= "| " . $data["per_areaTrabajo"] ?></span>
            </li> 
            <li>
              <hr class="dropdown-divider">
            </li>


            <li>
              <a class="dropdown-item d-flex align-items-center" href="./logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->