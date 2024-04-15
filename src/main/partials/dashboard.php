<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <!-- mostrar el siguiente nav para las secciones existentes-->
  <?php if ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 4 || $_SESSION["user"]["usu_rol"] == 5 || $_SESSION["user"]["usu_rol"] == 6) : ?>
    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <!-- si existe una sesion iniciada pon los siguientes hipervinculos  -->
      <?php if ($_SESSION["user"]["usu_rol"] == 1) : ?>
        
        <?php require 'partials/dashboard/admin.php'; ?>
        
      <?php elseif ($_SESSION["user"]["usu_rol"] == 2) : ?>

        <?php require 'partials/dashboard/adminDisenio.php'; ?>

      <?php elseif ($_SESSION["user"]["usu_rol"] == 3) : ?>

        <?php require 'partials/dashboard/diseniador.php'; ?>
        
      <?php elseif ($_SESSION["user"]["usu_rol"] == 4) : ?>

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
            <i class="bi bi-journal-text"></i><span>Registros</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="registro.php">
                <i class="bi bi-circle"></i><span>Ingresar Registro</span>
              </a>
            </li>
            <li>
              <a href="#">
                <i class="bi bi-circle"></i><span>Historial de mis registros</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#charts-nav-design" data-bs-toggle="collapse" href="#">
            <i class="bi bi-bar-chart"></i><span>Produccion</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="charts-nav-design" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="planos.php">
                <i class="bi bi-circle"></i><span>Planos</span>
              </a>
            </li>
            <li>
              <a href="produccion.php">
                <i class="bi bi-circle"></i><span>Producción</span>
              </a>
            </li>
            <li>
              <a href="planosError.php">
                <i class="bi bi-circle"></i><span>Planos Con errores</span>
              </a>
            </li>
        <!-- si existe una sesion iniciada pon los siguientes hipervinculos  -->
      <?php elseif ($_SESSION["user"]["usu_rol"] == 5) : ?>

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
            <i class="bi bi-journal-text"></i><span>Registros</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="registro.php">
                <i class="bi bi-circle"></i><span>Ingresar Registro</span>
              </a>
            </li>
            <li>
              <a href="#">
                <i class="bi bi-circle"></i><span>Historial de mis registros</span>
              </a>
            </li>
          </ul>
        </li>

        <!-- si existe una sesion iniciada pon los siguientes hipervinculos  -->
      <?php elseif ($_SESSION["user"]["usu_rol"] == 6) : ?>

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
            <i class="bi bi-journal-text"></i><span>Registros</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="registro.php">
                <i class="bi bi-circle"></i><span>Ingresar Registro</span>
              </a>
            </li>
            <li>
              <a href="#">
                <i class="bi bi-circle"></i><span>Historial de mis registros</span>
              </a>
            </li>
          </ul>
        </li>
      <?php endif ?>

    </ul>
  <?php else : ?>
  <?php endif ?>
</aside><!-- End Sidebar-->

<main id="main" class="main">