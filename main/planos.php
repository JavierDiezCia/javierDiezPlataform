<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php";



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$idop = isset($_GET["idop"]) ? $_GET["idop"] : null;
$opInfo = null;
$opPlanos = null;

if ($_SESSION["user"]["usu_rol"] || $_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 4 || $_SESSION["user"]["usu_rol"] == 5) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["idop"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Obtener la información de la op y sus planos
            $opInfoStatement = $conn->prepare("SELECT * FROM op 
            LEFT JOIN orden_disenio AS orden ON op.od_id = orden.od_id
            WHERE op_id = :idop 
            AND (op_estado = 'OP CREADA' OR op_estado = 'EN PRODUCCION') ");
            $opInfoStatement->bindParam(":idop", $_POST["idop"]);
            $opInfoStatement->execute();
            $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);

            // Obtener los planos asociados a la op
            $opPlanosStatement = $conn->prepare("SELECT * FROM planos WHERE op_id = :idop");
            $opPlanosStatement->bindParam(":idop", $_POST["idop"]);
            $opPlanosStatement->execute();
            $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <!-- Código para buscar op por op_id -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">BUSCAR POR NÚMERO DE OP</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error) : ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="planos.php">
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="idop" name="idop" placeholder="op_id">
                                <label for="idop">NÚMERO DE OP</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">BUSCAR</button>
                            <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mostrar información de la op y sus planos -->
            <?php if ($opInfo) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">DATOS DE LA OP</h5>
                                    <p>NÚMERO DE LA OP: <?= $opInfo["op_id"] ?></p>
                                    <p>CLIENTE: <?= $opInfo["od_cliente"] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if ($opPlanos) : ?>
                    <section class="section">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">PLANOS DE LA OP</h5>
                                        <!-- si el array asociativo $opPlanos no tiene nada dentro, entonces imprimir el siguiente div -->
                                        <?php if (empty($opPlanos)) : ?>
                                            <div class="col-md-4 mx-auto mb-3">
                                                <div class="card card-body text-center">
                                                    <p>BUSQUE UNA OP</p>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <section class="section">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="card">
                                                            <div class="card-title">
                                                                <h5 class="card-title">PLANOS DE LA OP</h5>
                                                                <table class="table datatable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>NÚMERO DE PLANO</th>
                                                                            <th>OP</th>
                                                                            <th>ESTADO</th>
                                                                            <th>REPROSESO</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($opPlanos as $opPlano) : ?>
                                                                            <tr>
                                                                                <td><?= $opPlano["pla_numero"] ?></td>
                                                                                <td><?= $opPlano["op_id"] ?></td>
                                                                                <td><?= $opPlano["pla_estado"] ?></td>
                                                                                <td>
                                                                                    <?php if ($opPlano["pla_estado"] == "ANULADO") : ?>
                                                                                        <?php if ($opPlano["pla_reproceso"] == 0) : ?>
                                                                                            NO ES UN REPROSESO
                                                                                        <?php elseif ($opPlano["pla_reproceso"] == 1) : ?>
                                                                                            ES UN REPROSESO
                                                                                        <?php endif ?>
                                                                                    <?php elseif ($opPlano["pla_reproceso"] != 0) : ?>
                                                                                        ES UN REPROSESO
                                                                                    <?php elseif ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 4 || $_SESSION["user"]["usu_rol"] == 5) : ?>
                                                                                        <button type="button" class="btn btn-warning mb-2" onclick="openReprosesoModal(<?= $opPlano["pla_id"] ?>)">REPROCESO</button>
                                                                                        <div class="modal fade" id="reproseso-<?= $opPlano["pla_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                                            <div class="modal-dialog modal-dialog-centered">
                                                                                                <div class="modal-content">
                                                                                                    <div class="modal-header">
                                                                                                        <h5 class="modal-title">REPROCESO EN EL PLANO</h5>
                                                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                    </div>
                                                                                                    <div class="modal-body">
                                                                                                        <p>Esta usted de acuerdo en generar un reproceso en el plano <?= $opPlano["pla_numero"] ?> de la OP <?= $opPlano["op_id"] ?></p>
                                                                                                        <section class="section">
                                                                                                            <div class="row">
                                                                                                                <div class="">
                                                                                                                    <?php if ($error) : ?>
                                                                                                                        <p class="text-danger">
                                                                                                                            <?= $error ?>
                                                                                                                        </p>
                                                                                                                    <?php endif ?>
                                                                                                                    <div class="card-body">
                                                                                                                        <form class="row g-3" method="POST" action="./cambiosEstadoPlano/reprosesoPlano.php?id=<?= $opPlano["pla_id"] ?>">
                                                                                                                            <div class="col-md-12">
                                                                                                                                <div class="form-floating">
                                                                                                                                    <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion">
                                                                                                                                    <label for="observacion">REGISTRE UNA OBSERVACION</label>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                            <!-- Otros campos del formulario si es necesario -->
                                                                                                                            <div class="modal-footer">
                                                                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                                                <!-- Cambiamos el enlace por un botón de tipo submit -->
                                                                                                                                <button type="submit" class="btn btn-primary">GENERAR REPROCESO</button>
                                                                                                                            </div>
                                                                                                                        </form>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </section>
                                                                                                    </div>

                                                                                                </div>
                                                                                                <script>
                                                                                                    function openReprosesoModal(idplano) {
                                                                                                        var modalId = "reproseso-" + idplano;
                                                                                                        $("#" + modalId).modal("show");
                                                                                                    }
                                                                                                </script>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php elseif ($_SESSION["user"]["usu_rol"] == 4) : ?>
                                                                                        <p>NO HAY REPROCESO EN EL PLANO</p>

                                                                                    <?php endif ?>
                                                                                </td>
                                                                                <td>
                                                                                    <?php if ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 4 || $_SESSION["user"]["usu_rol"] == 5) : ?>
                                                                                        <?php if ($opPlano["pla_estado"] == "ACTIVO") : ?>
                                                                                            <button type="button" class="btn btn-success mb-2" onclick="openPausarModal(<?= $opPlano["pla_id"] ?>)">PAUSAR</button>
                                                                                            <div class="modal fade" id="pausar-<?= $opPlano["pla_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                                                <div class="modal-dialog modal-dialog-centered">
                                                                                                    <div class="modal-content">
                                                                                                        <div class="modal-header">
                                                                                                            <h5 class="modal-title">PAUSAR EL PLANO</h5>
                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                        </div>
                                                                                                        <div class="modal-body">
                                                                                                            <p>Esta usted de acuerdo en pausar el plano <?= $opPlano["pla_id"] ?> de la OP <?= $opPlano["op_id"] ?></p>
                                                                                                            <section class="section">
                                                                                                                <div class="row">
                                                                                                                    <div class="">
                                                                                                                        <?php if ($error) : ?>
                                                                                                                            <p class="text-danger">
                                                                                                                                <?= $error ?>
                                                                                                                            </p>
                                                                                                                        <?php endif ?>
                                                                                                                        <div class="card-body">
                                                                                                                            <form class="row g-3" method="POST" action="./cambiosEstadoPlano/pausarPlano.php?id=<?= $opPlano["pla_id"] ?>">
                                                                                                                                <div class="col-md-12">
                                                                                                                                    <div class="form-floating">
                                                                                                                                        <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion">
                                                                                                                                        <label for="observacion"> REGISTRE UNA OBSERVACION</label>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                                <div class="modal-footer">
                                                                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                                                    <button type="submit" class="btn btn-success">PAUSAR</button>

                                                                                                                                </div>
                                                                                                                            </form>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </section>
                                                                                                        </div>

                                                                                                    </div>
                                                                                                    <script>
                                                                                                        function openPausarModal(idplano) {
                                                                                                            var modalId = "pausar-" + idplano;
                                                                                                            $("#" + modalId).modal("show");
                                                                                                        }
                                                                                                    </script>
                                                                                                </div>
                                                                                            </div>
                                                                                        <?php elseif ($opPlano["pla_estado"] == "PAUSADO") : ?>
                                                                                            <button type="button" class="btn btn-primary mb-2" onclick="openActivarModal(<?= $opPlano["pla_id"] ?>)">ACTIVAR</button>
                                                                                            <div class="modal fade" id="activar-<?= $opPlano["pla_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                                                <div class="modal-dialog modal-dialog-centered">
                                                                                                    <div class="modal-content">
                                                                                                        <div class="modal-header">
                                                                                                            <h5 class="modal-title">ACTIVAR PLANO</h5>
                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                        </div>
                                                                                                        <div class="modal-body">
                                                                                                            <p>Esta usted de acuerdo en activar el plano <?= $opPlano["pla_id"] ?> de la OP <?= $opPlano["op_id"] ?></p>
                                                                                                            <section class="section">
                                                                                                                <div class="row">
                                                                                                                    <div class="">
                                                                                                                        <?php if ($error) : ?>
                                                                                                                            <p class="text-danger">
                                                                                                                                <?= $error ?>
                                                                                                                            </p>
                                                                                                                        <?php endif ?>
                                                                                                                        <div class="card-body">
                                                                                                                            <form class="row g-3" method="POST" action="./cambiosEstadoPlano/activarPlano.php?id=<?= $opPlano["pla_id"] ?>">
                                                                                                                                <div class="col-md-12">
                                                                                                                                    <div class="form-floating">
                                                                                                                                        <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion">
                                                                                                                                        <label for="observacion"> REGISTRE UNA OBSERVACION</label>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                                <div class="modal-footer">
                                                                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                                                    <button type="submit" class="btn btn-primary">ACTIVAR</button>

                                                                                                                                </div>
                                                                                                                            </form>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                            </section>
                                                                                                        </div>

                                                                                                    </div>
                                                                                                    <script>
                                                                                                        function openActivarModal(idplano) {
                                                                                                            var modalId = "activar-" + idplano;
                                                                                                            $("#" + modalId).modal("show");
                                                                                                        }
                                                                                                    </script>
                                                                                                </div>
                                                                                            </div>
                                                                                        <?php elseif ($opPlano["pla_estado"] == "ANULADO") : ?>
                                                                                            <!-- <?= $opPlano["pla_estado"] ?> -->
                                                                                        <?php elseif ($opPlano["pla_estado"] == "CONCLUIDO") : ?>
                                                                                            <!-- <?= $opPlano["pla_estado"] ?> -->
                                                                                        <?php endif ?>
                                                                                    <?php elseif ($_SESSION["user"]["usu_rol"] == 2) : ?>
                                                                                        <!-- <?= $opPlano["pla_estado"] ?> -->
                                                                                    <?php endif ?>
                                                                                </td>
                                                                                <td>
                                                                                    <?php if ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2) : ?>
                                                                                        <?php if ($opPlano["pla_estado"] == "ACTIVO" || $opPlano["pla_estado"] == "PAUSADO") : ?>
                                                                                            <button type="button" class="btn btn-danger mb-2" onclick="openAnularModal(<?= $opPlano["pla_id"] ?>)">Anular</button>
                                                                                            <div class="modal fade" id="anular-<?= $opPlano["pla_id"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                                                <div class="modal-dialog modal-dialog-centered">
                                                                                                    <div class="modal-content">
                                                                                                        <div class="modal-header">
                                                                                                            <h5 class="modal-title">ANULAR PLANO</h5>
                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                        </div>
                                                                                                        <div class="modal-body">
                                                                                                            <p> Esta usted de acuerdo en anular el plano <?= $opPlano["pla_id"] ?> de la OP <?= $opPlano["op_id"] ?></p>
                                                                                                            <section class="section">
                                                                                                                <div class="row">
                                                                                                                    <div class="">
                                                                                                                        <?php if ($error) : ?>
                                                                                                                            <p class="text-danger">
                                                                                                                                <?= $error ?>
                                                                                                                            </p>
                                                                                                                        <?php endif ?>
                                                                                                                        <div class="card-body">
                                                                                                                            <form class="row g-3" method="POST" action="./cambiosEstadoPlano/anularPlano.php?id=<?= $opPlano["pla_id"] ?>">
                                                                                                                                <div class="col-md-12">
                                                                                                                                    <div class="form-floating">
                                                                                                                                        <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion">
                                                                                                                                        <label for="observacion"> REGISTRE UNA OBSERVACION</label>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                                <div class="modal-footer">
                                                                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                                                    
                                                                                                                                    <button type="submit" class="btn btn-primary">Anular</button>
                                                                                                                                </div>
                                                                                                                            </form>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </section>
                                                                                                        </div>

                                                                                                    </div>
                                                                                                </div>
                                                                                                <script>
                                                                                                    function openAnularModal(idplano) {
                                                                                                        var modalId = "anular-" + idplano;
                                                                                                        $("#" + modalId).modal("show");
                                                                                                    }
                                                                                                </script>
                                                                                            </div>
                                                                                        <?php endif ?>
                                                                                    <?php endif ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </section>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>