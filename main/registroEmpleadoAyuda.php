<?php
require "../sql/database.php";
require "./partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// declaramos la variable error
$error = null;

// Validar si el usuario es un empleado
if ($_SESSION["user"]["usu_rol"] == 6 || $_SESSION["user"]["usu_rol"] == 1) {
    // Obtener la cédula del empleado
    $empleado = $_SESSION["user"]["cedula"];

    $actividades = [];
    // Procesar el formulario cuando se envíe
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que se haya seleccionado un área de trabajo
        if (empty($_POST["area_trabajo"])) {
            $error = 'POR FAVOR SELECCIONAR UN ÁREA DE TRABAJO.';
        } else {
            // Obtener el área de trabajo seleccionada
            $area_trabajo_empleado = $_POST["area_trabajo"];

            // Obtener los arrays según el área de trabajo seleccionada
            
            if ($area_trabajo_empleado === "PINTURA") {
                $actividades = $actividades_pintura;
            } elseif ($area_trabajo_empleado === "ACRÍLICOS Y ACABADOS") {
                $actividades = $actividades_acrilicos;
            } elseif ($area_trabajo_empleado === "METALMECÁNICA") {
                $actividades = $actividades_metal;
            } elseif ($area_trabajo_empleado === "CARPINTERÍA") {
                $actividades = $actividades_carpinteria;
            } elseif ($area_trabajo_empleado === "ACM") {
                $actividades = $actividades_acm;
            } elseif ($area_trabajo_empleado === "MÁQUINAS") {
                $actividades = $actividades_maquinas;
            }

            sort($actividades);

            // Buscar las OP disponibles para el área de trabajo seleccionada
            $opQuery = $conn->prepare("SELECT DISTINCT op.op_id 
                                        FROM op 
                                        INNER JOIN planos p ON op.op_id = p.op_id 
                                        INNER JOIN produccion pro ON p.pla_id = pro.pla_id 
                                        INNER JOIN pro_areas pa ON pro.pro_id = pa.pro_id 
                                        WHERE pa.proAre_detalle = :area_trabajo 
                                        AND pro.pro_id IS NOT NULL 
                                        AND pa.proAre_porcentaje < 100");
            $opQuery->execute(array(':area_trabajo' => $area_trabajo_empleado));
            $ops = $opQuery->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} else {
    // Redirigir a la página principal o a donde desees si el usuario no tiene permisos adecuados
    header("Location: pages-error-404.html");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;

?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="card p-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Registro de Ayuda</h2>
                        <form action="registroEmpleadoAyuda.php" method="POST">
                            <div class="form-group">
                                <label for="area_trabajo">Área de Trabajo</label>
                                <select class="form-control" name="area_trabajo" id="area_trabajo">
                                    <option value="">Seleccionar Área de Trabajo</option>
                                    <option value="PINTURA">PINTURA</option>
                                    <option value="ACRÍLICOS Y ACABADOS">ACRÍLICOS Y ACABADOS</option>
                                    <option value="METALMECÁNICA">METALMECÁNICA</option>
                                    <option value="CARPINTERÍA">CARPINTERÍA</option>
                                    <option value="ACM">ACM</option>
                                    <option value="MÁQUINAS">MÁQUINAS</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </form>
                        <hr>
                        <?php if ($error) : ?>
                            <p class="text-danger"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <?php if (isset($ops) && count($ops) > 0) : ?>
                            <h3>Ordenes de Producción Disponibles</h3>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>OP</th>
                                        <th>Cliente</th>
                                        <th>Fecha de Entrega</th>
                                        <th>Área de Trabajo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ops as $op) : ?>
                                        <?php
                                        // Obtener la información de la OP
                                        $opQuery = $conn->prepare("SELECT op.op_id, c.cli_nombre, op.op_fecha_entrega, pa.proAre_detalle 
                                                                    FROM op 
                                                                    INNER JOIN planos p ON op.op_id = p.op_id 
                                                                    INNER JOIN produccion pro ON p.pla_id = pro.pla_id 
                                                                    INNER JOIN pro_areas pa ON pro.pro_id = pa.pro_id 
                                                                    INNER JOIN cliente c ON op.cli_id = c.cli_id 
                                                                    WHERE op.op_id = :op_id 
                                                                    AND pa.proAre_detalle = :area_trabajo 
                                                                    AND pro.pro_id IS NOT NULL 
                                                                    AND pa.proAre_porcentaje < 100");
                                        $opQuery->execute(array(':op_id' => $op['op_id'], ':area_trabajo' => $area_trabajo_empleado));
                                        $opInfo = $opQuery->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <tr>
                                            <td><?php echo $opInfo['op_id']; ?></td>
                                            <td><?php echo $opInfo['cli_nombre']; ?></td>
                                            <td><?php echo $opInfo['op_fecha_entrega']; ?></td>
                                            <td><?php echo $opInfo['proAre_detalle']; ?></td>
                                            <td>
                                                <a href="registroEmpleadoAyudaDetalle.php?op_id=<?php echo $opInfo['op_id']; ?>&area_trabajo=<?php echo $opInfo['proAre_detalle']; ?>" class="btn btn-primary">Registrar Ayuda</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
