<?php

if ($_SESSION["user"]["usu_rol"] != 1) {
    header('Location: ../../index.php');
    return;
}

?>


<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i><span>Usuarios</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="personas.php">
                <i class="bi bi-circle"></i><span>Personas</span>
            </a>
        </li>
        <li>
            <a href="usuarios.php">
                <i class="bi bi-circle"></i><span>Usuarios</span>
            </a>
        </li>
        <li>
            <a href="personasEliminadas.php">
                <i class="bi bi-circle"></i><span>Personas Eliminadas</span>
            </a>
        </li>
    </ul>
</li><!-- End Components Nav -->

<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav-op" data-bs-toggle="collapse" href="#">
        <i class="bi bi-box-seam"></i><span>OP's</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav-op" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="ciudades.php">
                <i class="bi bi-circle"></i><span>Ciudad de Producción</span>
            </a>
        </li>
        <li>
            <a href="op.php">
                <i class="bi bi-circle"></i><span>Registro de OP</span>
            </a>
        </li>
        <li>
            <a href="planosAddtest.php">
                <i class="bi bi-circle"></i><span>Añadir Planos</span>
            </a>
        </li>
        <li>
            <a href="opcionesOp.php">
                <i class="bi bi-circle"></i><span>Estados de las Op</span>
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
    </ul>
</li>

<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-truck"></i><span>Logística</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="logistica.php">
                <i class="bi bi-circle"></i><span>Formulario Logística</span>
            </a>
        </li>
        <li>
            <a href="registroFormulario.php">
                <i class="bi bi-circle"></i><span>Registro de Formularios de Logística</span>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-journal-text"></i><span>Diseño</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="historialRegistros.php">
                <i class="bi bi-circle"></i><span>Historial de registros</span>
            </a>
        </li>
        <li>
            <a href="historialOd.php">
                <i class="bi bi-circle"></i><span>Ordenes de Diseño</span>
            </a>
        </li>
        <li>
            <a href="validarOd.php">
                <i class="bi bi-circle"></i><span>Aprobar ordenes de diseño</span>
            </a>
        </li>
        <li>
            <a href="opcionesOP.php">
                <i class="bi bi-circle"></i><span>Estados de OP</span>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav-disenio" data-bs-toggle="collapse" href="#">
        <i class="bi bi-journal-text"></i><span>Registros</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav-disenio" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="registroOd.php">
                <i class="bi bi-circle"></i><span>Nuevo Registro</span>
            </a>
        </li>
        <li>
            <a href="historialRegistros.php">
                <i class="bi bi-circle"></i><span>Historial de mis Registros</span>
            </a>
        </li>
        <li>
            <a href="od.php">
                <i class="bi bi-circle"></i><span>Crear una nueva Orden de Diseño</span>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav-persona" data-bs-toggle="collapse" href="#">
        <i class="bi bi-clipboard"></i><span>Registros de Empleados</span><i class="bi bi-chevron-down ms-auto "></i>
    </a>
    <ul id="forms-nav-persona" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="registroEmpleado.php">
                <i class="bi bi-circle"></i><span>Nuevo Registro</span>
            </a>
        </li>
        <li>
            <a href="registroEmpleadoAyuda.php">
                <i class="bi bi-circle"></i><span>Ayuda</span>
            </a>
        </li>
    </ul>
</li>