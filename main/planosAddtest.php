<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit();
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$idop = $_GET["id"]; 
$opInfo = null;
$opPlanos = null;

if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3) {
    // Manejo del formulario POST para agregar nuevos planos
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["idop"]) && !empty($_POST["planos"])) {
        $idop = $_POST["idop"];
        $cantidadPlanos = intval($_POST["planos"]);

        // Consulta para recuperar los planos asociados a la OP
        $opPlanosStatement = $conn->prepare("SELECT * FROM planos WHERE op_id = :idop LIMIT 1000");
        $opPlanosStatement->bindParam(":idop", $idop);
        $opPlanosStatement->execute();
        $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);

        // Contar el número total de planos asociados a la OP
        $totalPlanos = count($opPlanos);

        // Recuperar el último número de plano existente
        $maxPlanoNumero = 0;
        foreach ($opPlanos as $plano) {
            $maxPlanoNumero = max($maxPlanoNumero, $plano['pla_numero']);
        }

        // Agregar los nuevos planos
        for ($i = 1; $i <= min($cantidadPlanos, 150); $i++) {
            $planoNumero = $maxPlanoNumero + $i;

            $stmt = $conn->prepare("INSERT INTO planos (op_id, pla_numero, pla_estado, pla_reproceso) VALUES (:idop, :pla_numero, 'ACTIVO', 0)");
            $stmt->execute([
                ":idop" => $idop,
                ":pla_numero" => $planoNumero
            ]);
        }


        // Actualizar la lista de planos después de agregar los nuevos
        $opPlanosStatement = $conn->prepare("SELECT * FROM planos WHERE op_id = :idop LIMIT 1000");
        $opPlanosStatement->bindParam(":idop", $idop);
        $opPlanosStatement->execute();
        $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    // Recuperar datos de la OP y los planos
    $opInfoStatement = $conn->prepare("SELECT op.op_id, od.od_cliente, od.od_detalle
        FROM op
        LEFT JOIN orden_disenio AS od ON op.od_id = od.od_id
        WHERE op.op_id = :idop AND (op.op_estado = 'OP CREADA' OR op.op_estado = 'EN PRODUCCION')
    ");
    $opInfoStatement->bindParam(":idop", $idop);
    $opInfoStatement->execute();
    $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);

    $opPlanosStatement = $conn->prepare("SELECT * FROM planos WHERE op_id = :idop LIMIT 1000");
    $opPlanosStatement->bindParam(":idop", $idop);
    $opPlanosStatement->execute();
    $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
}
    $totalPlanos = count($opPlanos);
?>



<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="">
            <?php if ($opInfo): ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card ">
                                <div class="card-body">
                                    <h5 class="card-title">DATOS DE LA OP</h5>
                                    <p>NÚMERO DE OP: <?= $opInfo["op_id"] ?></p>
                                    <p>CLIENTE: <?= $opInfo["od_cliente"] ?></p>
                                    <p>DETALLE: <?= $opInfo["od_detalle"]?></p>

                                    <form class="row g-3" method="POST" action="planosAddtest.php?id=<?= $idop ?>">
                                        <input type="hidden" name="idop" value="<?= $idop ?>">
                                        <div class="col-md-6">
                                            <h4 class="">PLANOS TOTALES: <?= $totalPlanos ?></h4>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="number" class="form-control" id="planos" name="planos" placeholder="" autocomplete="planos" required max="150">
                                                <label for="planos">AÑADIR PLANOS</label>
                                            </div>
                                            <?php if ($opPlanos): ?>
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    INGRESE LA CANTIDAD DE PLANOS A AÑADIR. MAX:150.
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif ?>
            </div>    
                <?php if ($opPlanos): ?>
                    <section class="section">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">PLANOS DE LA OP</h5>
                                        <!-- si el array asociativo $opPlanos no tiene nada dentro, entonces imprimir el siguiente div -->
                                        <?php if (empty($opPlanos)): ?>
                                            <div class="col-md-4 mx-auto mb-3">
                                                <div class="card card-body text-center">
                                                    <p>BUSQUE UNA OP</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Table with stripped rows -->
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>NÚMERO DE PLANO</th>
                                                        <th>ESTADO</th>
                                                        <!-- <th></th> -->
                                                        <!-- <th></th>
                                                        <th></th> -->
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($opPlanos as $opPlano): ?>
                                                        <tr>
                                                            <td><?= $opPlano["pla_numero"] ?></td>
                                                            <td><?= $opPlano["pla_estado"] ?></td>
                                                            <!-- <td>
                                                                <?php if($opPlano["pla_estado"] == 1 ) : ?>
                                                                    <a href="#" class="btn btn-primary mb-2">Pausar</a>
                                                                <?php elseif($opPlano["pla_estado"] == 2 ) : ?>
                                                                    <a href="#" class="btn btn-success mb-2">Activar</a>
                                                                <?php else : ?>
                                                                <?php endif ?>
                                                            </td> -->
                                                            <!-- <td>
                                                                <?php if($opPlano["PLANOTIFICACION"] == 0 ) : ?>
                                                                    <a href="./validaciones/notiPlano.php?id=<?= $opPlano["IDPLANO"] ?>" class="btn btn-warning mb-2">Notificar problema</a>
                                                                <?php else : ?>
                                                                <?php endif ?>
                                                            </td> -->
                                                            <!-- <td>
                                                                <?php if($opPlano["pla_estado"] !== 3 ) : ?>
                                                                    <a href="#" class="btn btn-danger mb-2">Anular</a>
                                                                <?php elseif($opPlano["pla_estado"] == 3 ) : ?>
                                                                    <a href="#" class="btn btn-success mb-2">Reanudar</a>
                                                                <?php else : ?>
                                                                <?php endif ?>
                                                            </td> -->
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>       
                <?php endif ?>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
