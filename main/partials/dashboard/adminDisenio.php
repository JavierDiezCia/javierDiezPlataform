<?php

if ($_SESSION["user"]["usu_rol"] != 2) {
    header('Location: ../../index.php');
    return;
}

?>



<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-journal-text"></i><span>Diseño</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="historialOd.php">
                <i class="bi bi-circle"></i><span>Ordenes de Diseño</span>
            </a>
        </li>
        <li>
            <a href="validarOd.php">
                <i class="bi bi-circle"></i><span>MATERIALIDAD</span>
            </a>
        </li>
        <li>
            <a href="historialRegistros.php">
                <i class="bi bi-circle"></i><span>Historial de registros</span>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-op" data-bs-toggle="collapse" href="#">
        <i class="bi bi-box-seam"></i><span>OP</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-op" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="opcionesOp.php">
                <i class="bi bi-circle"></i><span>ÓRDENES DE PRODUCCIÓN</span>
            </a>
        </li>
        <li>
            <a href="op.php">
                <i class="bi bi-circle"></i><span>REGISTRAR UNA OP</span>
            </a>
        </li>
        <li>
            <a href="planosAddtest.php">
                <i class="bi bi-circle"></i><span>AÑADIR PLANOS</span>
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