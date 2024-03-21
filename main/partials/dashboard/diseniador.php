<?php

if ($_SESSION["user"]["usu_rol"] != 3) {
    header('Location: ../../index.php');
    return;
}

?>


<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-journal-text"></i><span>ÓRDENES DE DISEÑO</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="od.php">
                <i class="bi bi-circle"></i><span>CREAR ORDEN DE DISEÑO</span>
            </a>
        </li>
        <li>
            <a href="registroOd.php">
                <i class="bi bi-circle"></i><span>NUEVO REGISTRO</span>
            </a>
        </li>
        <li>
            <a href="historialRegistros.php">
                <i class="bi bi-circle"></i><span>HISTORIAL DE MIS REGISTROS</span>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-ORDEN" data-bs-toggle="collapse" href="#">
        <i class="bi bi-box-seam"></i><span>ÓRDENES DE PRODUCCIÓN</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-ORDEN" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="op.php">
                <i class="bi bi-circle"></i><span>REGISTRO DE OP</span>
            </a>
        </li>
        <li>
            <a href="planosAddtest.php">
                <i class="bi bi-circle"></i><span>AÑADIR PLANOS</span>
            </a>
        </li>
    </ul>
</li>