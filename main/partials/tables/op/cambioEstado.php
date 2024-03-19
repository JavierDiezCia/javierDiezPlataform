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
                                        <?php elseif ($_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2) : ?>
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
                                                                                        <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion">
                                                                                        <label for="observacion">Ingrese la obervación</label>
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
                                            <button type="button" class="btn btn-success mb-2" onclick="openPausarModal(<?= $op["op_id"] ?>)">PAUSAR</button>
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
                                                                            <form class="row g-3" method="post" action="./cambiosEstadoOp/pausarOp.php?id=<?= $op["op_id"] ?>">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-floating">
                                                                                        <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion" required>
                                                                                        <label for="observacion">Registre la observacion</label>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                        <button type="submit" class="btn btn-success">PAUSAR</button>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </section>
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
                                            <button type="button" class="btn btn-primary mb-2" onclick="openActivarModal(<?= $op["op_id"] ?>)">ACTIVAR</button>
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
                                                                            <form class="row g-3" method="post" action="./cambiosEstadoOp/activarOp.php?id=<?= $op["op_id"] ?>">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-floating">
                                                                                        <input type="text" class="form-control" id="observacion" name="observacion" placeholder="observacion" required>
                                                                                        <label for="observacion">Registre la obervacion</label>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                        <button type="submit" class="btn btn-primary">ACTIVAR</button>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </section>
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
                                        <button type="button" class="btn btn-danger mb-2" onclick="openAnularModal(<?= $op["op_id"] ?>)">ANULAR</button>
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
                                                                        <form class="row g-3" method="post" action="./cambiosEstadoOp/anularOP.php?id=<?= $op["op_id"] ?> ">
                                                                            <div class="col-md-12">
                                                                                <div class="form-floating">
                                                                                    <input type="text" class="form-control" id="observacion" name="observacion" placeholder="obervacion" required>
                                                                                    <label for="observacion">Registre la Obervacion</label>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                                    <button type="submit" class="btn btn-primary">Anular</button>
                                                                                </div>
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