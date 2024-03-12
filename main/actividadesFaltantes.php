<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 

// Verificar si la sesión no existe, redirigir al login.php y detener la ejecución del script
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

// Declarar variables
$error = null;
$id = $_GET["id"] ?? null;

// Verificar el rol del usuario
if ($_SESSION["user"]["usu_rol"] && in_array($_SESSION["user"]["usu_rol"], [1, 2, 3])) {
    // Obtener la información de la orden de diseño según el ID proporcionado
    $statement = $conn->prepare("SELECT * FROM orden_disenio WHERE od_id = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $orden = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la orden de diseño existe
    if (!$orden) {
        header("Location: ./pages-error-404.html");
        exit;
    }

    // Obtener todas las actividades relacionadas con la orden de diseño
    $actividades = $conn->prepare("SELECT * FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0 ORDER BY odAct_id DESC");
    $actividades->bindParam(":od_id", $id);
    $actividades->execute();

    //VERIFICAR SI HAY REGISTROS SIN ACTIVIDADES
    $detallesSinRegistro = $conn->prepare("SELECT odAct_detalle FROM od_actividades WHERE od_id = :id AND odAct_estado = 0 AND odAct_detalle NOT IN (SELECT rd_detalle FROM registros_disenio WHERE od_id = :id AND rd_hora_fin IS NOT NULL)");
    $detallesSinRegistro->execute([":id" => $orden["od_id"]]);
    $detallesSinRegistro = $detallesSinRegistro->fetchAll(PDO::FETCH_ASSOC);

}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">Información de la Orden de Diseño</h2>
            <a href="./od.php" class="btn btn-secondary"><i class="bi bi-arrow-90deg-left"></i></a>
        </div>
        <p class="card-text">#OD: <?= $orden["od_id"] ?></p>
        <p class="card-text">DETALLE: <?= $orden["od_detalle"] ?></p>
        <p class="card-text">FECHA DE REGISTRO: <?= $orden["od_fechaRegistro"] ?></p>
        <p class="card-text">ESTADO: <?= $orden["od_estado"] ?></p>
        <p class="card-text">CLIENTE: <?= $orden["od_cliente"] ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Actividades</h5>
        <table id="activitiesTable" class="table datatable">
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Registros</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actividades as $actividad) : ?>
                    <tr>
                        <td><?= $actividad["odAct_detalle"] ?></td>
                        <?php
                        $registros = $conn->prepare("SELECT COUNT(*) AS total_registros FROM registros_disenio WHERE od_id = :od_id AND rd_detalle = :detalle");
                        $registros->bindParam(":od_id", $id);
                        $registros->bindParam(":detalle", $actividad["odAct_detalle"]);
                        $registros->execute();
                        $totalRegistros = $registros->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <td><?= $totalRegistros["total_registros"] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- <script>
    $(document).ready(function() {
        $('#activitiesTable').DataTable();
    });
</script> -->

<?php require "./partials/footer.php"; ?>
