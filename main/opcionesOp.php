<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php";

// Verificar si la sesión no existe, redirigir al login.php y detener la ejecución
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

// Declarar la variable $error para mostrar errores
$error = null;
$idop = $_GET["idop"] ?? null;
$opInfo = null;
$opPlanos = null;

if ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2) {
    $opQuery = "SELECT op.*, 
                        orden.od_responsable,
                        responsable.per_nombres AS responsable_nombres,
                        responsable.per_apellidos AS responsable_apellidos,
                        orden.od_comercial,
                        comercial.per_nombres AS comercial_nombres,
                        comercial.per_apellidos AS comercial_apellidos,
                        orden.od_detalle,
                        orden.od_cliente
                FROM op
                LEFT JOIN orden_disenio AS orden ON op.od_id = orden.od_id
                LEFT JOIN personas AS responsable ON orden.od_responsable = responsable.cedula
                LEFT JOIN personas AS comercial ON orden.od_comercial = comercial.cedula";

    $op = $conn->query("$opQuery WHERE op.op_estado NOT IN ('OP FINALIZADA', 'OP ANULADA')");
    $opanulada = $conn->query("$opQuery WHERE op.op_estado = 'OP ANULADA'");
    $opfinalizada = $conn->query("$opQuery WHERE op.op_estado = 'OP FINALIZADA'");
    $optotal = $conn->query($opQuery);

    $lugarproduccion = $conn->query("SELECT * FROM ciudad_produccion");
    $personas = $conn->query("SELECT * FROM personas");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["idop"])) {
            $error = "POR FAVOR DEBE RELLENAR EL CAMPO DE LA OP.";
        } else {
            // Procesar los datos del formulario
        }
    }
} else {
    header("Location: ./index.php");
    exit;
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <h5 class="card-title">LISTAS DE LOS TIPOS DE OP'S</h5>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="estado-tab" data-bs-toggle="tab" data-bs-target="#estado" type="button" role="tab" aria-controls="estado" aria-selected="true">CAMBIO DE LOS ESTADOS DE LA OP</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="finalizada-tab" data-bs-toggle="tab" data-bs-target="#finalizado" type="button" role="tab" aria-controls="finalizado" aria-selected="false" tabindex="-1">OP FINALIZADAS</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anulado-tab" data-bs-toggle="tab" data-bs-target="#anulado" type="button" role="tab" aria-controls="anulado" aria-selected="false" tabindex="-2">OP ANULADAS</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="op-tab" data-bs-toggle="tab" data-bs-target="#op" type="button" role="tab" aria-controls="op" aria-selected="false" tabindex="-3">OP</button>
                    </li>
                </ul>
                <div class="tab-content pt-2" id="myTabContent">
                    <div class="tab-pane fade show active" id="estado" role="tabpanel" aria-labelledby="estado-tab">
                        <?php require "./partials/tables/op/cambioEstado.php"; ?>
                    </div>
                    <div class="tab-pane fade" id="finalizado" role="tabpanel" aria-labelledby="finalizado-tab">
                        <?php require "./partials/tables/op/opFinalizada.php"; ?>
                    </div>
                    <div class="tab-pane fade" id="anulado" role="tabpanel" aria-labelledby="anulado-tab">
                        <?php require "./partials/tables/op/opAnulada.php"; ?>
                    </div>
                    <div class="tab-pane fade" id="op" role="tabpanel" aria-labelledby="op-tab">
                       <?php require "./partials/tables/op/op.php"; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>