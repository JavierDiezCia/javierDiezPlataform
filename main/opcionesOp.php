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
                        <button class="nav-link" id="finalizada-tab" data-bs-toggle="tab" data-bs-target="#finalizado" type="button" role="tab" aria-controls="finalizado" aria-selected="false" tabindex="-1">OP'S FINALIZADAS</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anulado-tab" data-bs-toggle="tab" data-bs-target="#anulado" type="button" role="tab" aria-controls="anulado" aria-selected="false" tabindex="-2">OP'S ANULADOS</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="op-tab" data-bs-toggle="tab" data-bs-target="#op" type="button" role="tab" aria-controls="op" aria-selected="false" tabindex="-3">OP</button>
                    </li>
                </ul>
                <div class="tab-content pt-2" id="myTabContent">
                    <div class="tab-pane fade show active" id="estado" role="tabpanel" aria-labelledby="estado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="card-title">CAMBIOS OP</h5>
                                                </div>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>OP</th>
                                                        <th>CLIENTE</th>
                                                        <th>DISEÑADOR</th>
                                                        <th>ESTADO</th>
                                                        <th>REPROCESO</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($op as $op) : ?>
                                                        <tr>
                                                            <td><?= $op["op_id"] ?> </td>
                                                            <td><?= $op["od_cliente"] ?></td>
                                                            <td><?= $op["responsable_nombres"] . " " . $op["responsable_apellidos"] ?></td>
                                                            <td><?= $op["op_estado"] ?></td>
                                                            <td>
                                                                <?php if ($op["op_reproceso"] != 0) : ?>
                                                                    Es un reproceso
                                                                <?php elseif ($_SESSION["user"]["usu_rol"] == 1 ||$_SESSION["user"]["usu_rol"] == 2) : ?>
                                                                    <button type="button" class="btn btn-warning mb-2" onclick="openReprosesoModal(<?= $op["op_id"] ?>)">REPROCESO</button>
                                                                    <div class="modal fade" id="reproseso-<?= $op["op_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">REPROCESO DE LA OP</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Esta usted de acuerdo en generar un reproseso en la op <?= $op["op_id"] ?> del cliente <?= $op["od_cliente"] ?></p>
                                                                                    <section class="section">
                                                                                        <div class="row">
                                                                                            <div class="">
                                                                                                <?php if ($error) : ?>
                                                                                                    <p class="text_danger">
                                                                                                        <?= $error ?>
                                                                                                    </p>
                                                                                                <?php endif ?>
                                                                                                <div class="card-body">
                                                                                                    <form class="row g-3" method="post" action="">
                                                                                                        <div class="col-md-12">
                                                                                                            <div class="form-floating">
                                                                                                                <input type="text" class="form-control" id="observacion" name="obseravcion" placeholder="observacion">
                                                                                                                <label for="obseravacion">Ingrese la obervación</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </section>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/reprosesoOP.php?id=<?= $op["op_id"] ?>" class="btn btn-warning mb-2">REPROCESO</a>
                                                                                </div>
                                                                            </div>
                                                                            <script>
                                                                                function openReprosesoModal(idop) {
                                                                                    // Construye el ID del modal específico basado en el ID de la op
                                                                                    var modalId = "reproseso-" + idop;
                                                                                    // Abre el modal correspondiente
                                                                                    $("#" + modalId).modal("show");
                                                                                }
                                                                            </script>
                                                                        </div>
                                                                    </div>
                                                                <?php elseif ($_SESSION["user"]["usu_rol"] == 2) : ?>
                                                                    <p>NO HAY REPROCESO EN LA OP</p>
                                                                <?php endif  ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($op["op_estado"] != "OP PAUSADA") : ?>
                                                                    <button type="button" class="btn btn-success mb-2" onclick="openPausarModal(<?= $op["op_id"] ?>)">Pausar</button>
                                                                    <div class="modal fade" id="pausar-<?= $op["op_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Pausar op</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Esta usted de acuerdo de pausar la op <?= $op["op_id"] ?> del cliente <?= $op["od_cliente"] ?></p>
                                                                                    <section class="section">
                                                                                        <div class="row">
                                                                                            <div class="">
                                                                                                <?php if ($error) : ?>
                                                                                                    <p class="text_danger">
                                                                                                        <?= $error ?>
                                                                                                    </p>
                                                                                                <?php endif ?>
                                                                                                <div class="card-body">
                                                                                                    <form class="row g-3" method="post" action="">
                                                                                                        <div class="col-md-6">
                                                                                                            <div class="form-floating">
                                                                                                                <input type="text" class="form-control" id="observacio" name="obervacione" placeholder="observacion">
                                                                                                                <label for="observacion">Registre la observacion</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </section>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/pausarOp.php?id=<?= $op["op_id"] ?>" class="btn btn-success mb-2">Pausar</a>
                                                                                </div>
                                                                            </div>
                                                                            <script>
                                                                                                    function openPausarModal(idop) {
                                                                                                        // Construye el ID del modal específico basado en el ID de la op
                                                                                                        var modalId = "pausar-" + idop;
                                                                                                        // Abre el modal correspondiente
                                                                                                        $("#" + modalId).modal("show");
                                                                                                    }
                                                                                                </script>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php else : ?>
                                                                                        <button type="button" class="btn btn-primary mb-2" onclick="openActivarModal(<?= $op["op_id"] ?>)">Activar</button>
                                                                                        <div class="modal fade" id="activar-<?= $op["op_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                                            <div class="modal-dialog modal-dialog-centered">
                                                                                                <div class="modal-content">
                                                                                                    <div class="modal-header">
                                                                                                        <h5 class="modal-title">Activar op</h5>
                                                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                    </div>
                                                                                                    <div class="modal-body">
                                                                                                        <p>Esta usted seguro de activar la op <?= $op["op_id"] ?> del cliente <?= $op["od_cliente"] ?></p>
                                                                                                        <section class="section">
                                                                                                            <div class="row">
                                                                                                                <div class="">
                                                                                                                    <?php if ($error) : ?>
                                                                                                                        <p class="text_danger">
                                                                                                                            <?= $error ?>
                                                                                                                        </p>
                                                                                                                    <?php endif ?>
                                                                                                                    <div class="card-body">
                                                                                                                        <form class="row g-3" method="post" action="">
                                                                                                                            <div class="col-md-6">
                                                                                                                                <div class="form-floating">
                                                                                                                                    <input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="observaciones">
                                                                                                                                    <label for="observaciones">Registre la obervacion</label>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </form>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </section>
                                                                                                    </div>
                                                                                                    <div class="modal-footer">
                                                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                        <a href="./cambiosEstadoOp/activarOp.php?id=<?= $op["op_id"] ?>" class="btn btn-primary mb-2">Activar</a>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <script>
                                                                                                    function openActivarModal(idop) {
                                                                                                        // Construye el ID del modal específico basado en el ID de la op
                                                                                                        var modalId = "activar-" + idop;
                                                                                                        // Abre el modal correspondiente
                                                                                                        $("#" + modalId).modal("show");
                                                                                                    }
                                                                                                </script>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php endif ?>
                                                                                </td>
                                                                                <td>
                                                                                    <button type="button" class="btn btn-danger mb-2" onclick="openAnularModal(<?= $op["op_id"] ?>)">Anular</button>
                                                                                    <div class="modal fade" id="anular-<?= $op["op_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                                            <div class="modal-content">
                                                                                                <div class="modal-header">
                                                                                                    <h5 class="modal-title">Anular Op</h5>
                                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                </div>
                                                                                                <div class="modal-body">
                                                                                                    <p>Esta usted seguro que quiere anular la siguiente op <?= $op["op_id"] ?> del cliente <?= $op["od_cliente"] ?></p>
                                                                                                    <section class="section">
                                                                                                        <div class="row">
                                                                                                            <div class="">
                                                                                                                <?php if ($error) : ?>
                                                                                                                    <p class="text_danger">
                                                                                                                        <?= $error ?>
                                                                                                                    </p>
                                                                                                                <?php endif ?>
                                                                                                                <div class="card-body">
                                                                                                                    <form class="row g-3" method="post" action="">
                                                                                                                        <div class="col-md-6">
                                                                                                        <div class="form-floating">
                                                                                                            <input type="text" class="form-control" id="observacion" name="obsevacion" placeholder="obervacion">
                                                                                                            <label for="obssevacio">Registre la Obervacion</label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </section>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                                <a href="./cambiosEstadoOp/anularOP.php?id=<?= $op["op_id"] ?>" class="btn btn-primary">Anular</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <script>
                                                                        function openAnularModal(idop) {
                                                                            // Construye el ID del modal específico basado en el ID de la op
                                                                            var modalId = "anular-" + idop;
                                                                            // Abre el modal correspondiente
                                                                            $("#" + modalId).modal("show");
                                                                        }
                                                                    </script>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="finalizado" role="tabpanel" aria-labelledby="finalizado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="card-title">OP'S FINALIZADAS</h5>
                                                </div>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>OP</th>
                                                        <th>CLIENTE</th>
                                                        <th>DETALLE</th>
                                                        <th>DISEÑADOR</th>
                                                        <th>VENDEDOR</th>
                                                        <th>FECHA DE REGISTRO</th>
                                                        <th>DIRECCIÓN DEL LOCAL</th>
                                                        <th>PERSONA DE CONTACTO</th>
                                                        <th>TELÉFONO DE CONTACTO</th>
                                                        <th>REPROCESO</th>
                                                        <th>ESTADO</th>
                                                    </tr>
                                                <tbody>
                                                    <?php foreach ($opfinalizada as $opfinalizada) : ?>
                                                        <tr>
                                                            <td><?= $opfinalizada["op_id"] ?></td>
                                                            <td><?= $opfinalizada["op_cliente"] ?></td>
                                                            <td><?= $opfinalizada["op_detalle"] ?></td>
                                                            <td><?= $opfinalizada["cedula_nombres"] . " " . $opfinalizada["cedula_apellidos"] ?></td>
                                                            <td><?= $opfinalizada["vendedor_nombres"] . " " . $opfinalizada["vendedor_apellidos"] ?></td>
                                                            <td><?= $opfinalizada["op_registro"] ?></td>
                                                            <td><?= $opfinalizada["op_direccionLocal"] ?></td>
                                                            <td><?= $opfinalizada["op_personaContacto"] ?></td>
                                                            <td><?= $opfinalizada["op_telefono"] ?></td>
                                                            <td>
                                                                <?php
                                                                $reproseso = $opfinalizada["op_reproceso"];
                                                                switch ($reproseso) {
                                                                    case 0:
                                                                        echo " NO ES UN REPROSESO";
                                                                        break;
                                                                    case 1:
                                                                        echo "ES UN REPROSESO";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= $opfinalizada["op_estado"] ?></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="anulado" role="tabpanel" aria-labelledby="anulado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="card-title">OP'S ANULADAS</h5>
                                                </div>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>OP</th>
                                                        <th>CLIENTE</th>
                                                        <th>DETALLE</th>
                                                        <th>DISEÑADOR</th>
                                                        <th>VENDEDOR</th>
                                                        <th>FECHA REGISTRO</th>
                                                        <th>DIRECCIÓN DEL LOCAL</th>
                                                        <th>PERSONA DE CONTACTO</th>
                                                        <th>TELÉFONO DE CONTACTO</th>
                                                        <th>REPROCESO</th>
                                                        <th>ESTADO</th>
                                                    </tr>
                                                <tbody>
                                                    <?php foreach ($opanulada as $opanulada) : ?>
                                                        <tr>
                                                            <td><?= $opanulada["op_id"] ?></td>
                                                            <td><?= $opanulada["od_cliente"] ?></td>
                                                            <td><?= $opanulada["od_detalle"] ?></td>
                                                            <td><?= $opanulada["responsable_nombres"] . " " . $opanulada["responsable_apellidos"] ?></td>
                                                            <td><?= $opanulada["comercial_nombres"] . " " . $opanulada["comercial_apellidos"] ?></td>
                                                            <td><?= $opanulada["op_registro"] ?></td>
                                                            <td><?= $opanulada["op_direccionLocal"] ?></td>
                                                            <td><?= $opanulada["op_personaContacto"] ?></td>
                                                            <td><?= $opanulada["op_telefono"] ?></td>
                                                            <td>
                                                                <?php
                                                                $reproseso = $opanulada["op_reproceso"];
                                                                switch ($reproseso) {
                                                                    case 0:
                                                                        echo " NO ES UN REPROSESO";
                                                                        break;
                                                                    case 1:
                                                                        echo "ES UN REPROSESO";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= $opanulada["op_estado"] ?></td>
                                                            <td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="op" role="tabpanel" aria-labelledby="op-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="card-title">OP TOTALES</h5>
                                                    <!-- Botón para exportar a Excel con ícono desde la carpeta exel y estilizado con Bootstrap -->
                                                    <a href="./reporte_exel/exel_op.php" class="btn btn-success btn-xs">
                                                        <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
                                                        EXPORTAR A EXCEL
                                                    </a>
                                                </div>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>OP</th>
                                                        <th>CLIENTE</th>
                                                        <th>DETALLE</th>
                                                        <th>DISEÑADOR</th>
                                                        <th>VENDEDOR</th>
                                                        <th>FECHA DE REGISTRO</th>
                                                        <th>DIRECCIÓN DEL LOCAL</th>
                                                        <th>PERSONA DE CONTACTO</th>
                                                        <th>TELÉFONO DE CONTACTO</th>
                                                        <th>REPROCESO</th>
                                                        <th>ESTADO</th>
                                                    </tr>
                                                <tbody>
                                                    <?php foreach ($optotal as $optotal) : ?>
                                                        <tr>
                                                            <td><?= $optotal["op_id"] ?></td>
                                                            <td><?= $optotal["od_cliente"] ?></td>
                                                            <td><?= $optotal["od_detalle"] ?></td>
                                                            <td><?= $optotal["responsable_nombres"] . " " . $optotal["responsable_apellidos"] ?></td>
                                                            <td><?= $optotal["comercial_nombres"] . " " . $optotal["comercial_apellidos"] ?></td>
                                                            <td><?= $optotal["op_registro"] ?></td>
                                                            <td><?= $optotal["op_direccionLocal"] ?></td>
                                                            <td><?= $optotal["op_personaContacto"] ?></td>
                                                            <td><?= $optotal["op_telefono"] ?></td>
                                                            <td>
                                                                <?php
                                                                $reproseso = $optotal["op_reproceso"];
                                                                switch ($reproseso) {
                                                                    case 0:
                                                                        echo " NO ES UN REPROSESO";
                                                                        break;
                                                                    case 1:
                                                                        echo "ES UN REPROSESO";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= $optotal["op_estado"] ?></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>