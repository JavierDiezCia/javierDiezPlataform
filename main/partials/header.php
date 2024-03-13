<?php
$dataUser = $conn->query("SELECT * FROM personas WHERE cedula = {$_SESSION["user"]["cedula"]} LIMIT 1");
$data = $dataUser->fetch(PDO::FETCH_ASSOC);
$totalNotificaciones = NULL; 


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

    <!--<div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div> End Search Bar -->

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

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <?php if(empty($notis)) : ?>
            <?php endif ?>

            <li class="dropdown-header">
              Tienes <?= $totalNotificaciones ?> notificaciones
            </li>
            
            <li>
              <hr class="dropdown-divider">
            </li>

            <?php foreach($notis as $noti) : ?>
              <?php
              // Obtén la fecha de la notificación
              $fechaNotificacion = new DateTime($noti["PLAFECHANOTI"]);

              // Calcula la diferencia entre la fecha actual y la fecha de la notificación
              $tiempoTranscurrido = $fechaNotificacion->diff(new DateTime());

              // utilizar $tiempoTranscurrido para mostrar el tiempo transcurrido 
              ?>
              <li class="notification-item">
                <i class="bi bi-x-circle text-danger"></i>
                <div>
                  <h4>Contactarse con Producción</h4>
                  <p>Error en la OP # <?= $noti["IDOP"] ?> <br> Plano # <?= $noti["PLANNUMERO"] ?></p>
                  <p><?= $tiempoTranscurrido->format('%h hrs. %i mins. ago') ?></p>
                </div>
              </li>
            <?php endforeach ?>


            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

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
              <?php if( $_SESSION ["user"]["usu_rol"] == 1): ?>
                                        Super Administrador
                                    <?php elseif( $_SESSION ["user"]["usu_rol"] == 2): ?>
                                        Admi Diseño
                                    <?php elseif( $_SESSION ["user"]["usu_rol"] == 3): ?>
                                        Diseñadores
                                    <?php elseif( $_SESSION ["user"]["usu_rol"] == 4): ?>
                                        Admi Producción
                                    <?php elseif( $_SESSION ["user"]["usu_rol"] == 5): ?>
                                        Producción
                                    <?php elseif( $_SESSION ["user"]["usu_rol"] == 6): ?>
                                        Personal
                                    <?php elseif( $_SESSION ["user"]["usu_rol"] == 7): ?>
                                        Presentacion
                                    <?php endif ?>
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