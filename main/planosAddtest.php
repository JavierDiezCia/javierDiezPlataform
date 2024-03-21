<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit();
}

// Declarar variables
$idop = $_GET["id"] ?? null;

// buscar op por id
if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1 || $_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3) {
    $query = "SELECT OP.*, OD.* 
                FROM op 
                LEFT JOIN orden_disenio OD ON OP.od_id = OD.od_id
                WHERE op_id = :idop AND op_estado != 'OP FINALIZADA' AND op_estado != 'OP ANULADA'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":idop", $_GET["id"]);
    $stmt->execute();
    $op = $stmt->fetch(PDO::FETCH_ASSOC);

    //buscar los planos de la op
    $query = "SELECT * FROM planos WHERE op_id = :idop ORDER BY pla_numero ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":idop", $_GET["id"]);
    $stmt->execute();
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Declaramos la variable error que nos ayudará a mostrar errores, etc.
    $error = null;
    //verificamos que no se repita el mismo numero de plano en la misma op
    $stmt = $conn->prepare("SELECT * FROM planos WHERE op_id = :op_id AND pla_numero = :pla_numero");
    $stmt->bindParam(":op_id", $idop);
    $stmt->bindParam(":pla_numero", $_POST["pla_numero"]);
    $stmt->execute();
    $plano = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["pla_numero"]) || empty($_POST["pla_descripcion"])) {
            $error = "Por favor, llene todos los campos.";
            
        } elseif ($plano) {
            $error = "El número de plano " . $_POST['pla_numero'] . " ya existe en la OP.";

        } else {
            $pla_numero = $_POST["pla_numero"];
            $pla_descripcion = $_POST["pla_descripcion"];
            $query = "INSERT INTO planos (op_id, pla_numero, pla_descripcion) VALUES (:op_id, :pla_numero, :pla_descripcion)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":op_id", $idop);
            $stmt->bindParam(":pla_numero", $pla_numero);
            $stmt->bindParam(":pla_descripcion", $pla_descripcion);
            $stmt->execute();

            $last_id = $conn->lastInsertId();

            $notiDetalle  = "Se ha agregado un nuevo plano a la OP " . $idop . " con el número " . $pla_numero . " y la descripción " . $pla_descripcion . ".";

            //notificaciones PARA EL ROL 2
            $notificacion = $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_fecha, noti_detalle, noti_destinatario) VALUES (:cedula, :fecha, :detalle, :destinatario)");
            $notificacion->execute([
                ":cedula" => $_SESSION["user"]["cedula"],
                ":fecha" => date("Y-m-d H:i:s"),
                ":detalle" => $notiDetalle,
                ":destinatario" => 2
            ]);

            // crear un row en la tabla noti_visualizaciones por cada usuario que tenga el rol 2
            $notiId = $conn->lastInsertId();
            $usuarios = $conn->prepare("SELECT P.cedula FROM personas P
                                        JOIN usuarios U ON P.cedula = U.cedula
                                        WHERE usu_rol = 2");
            $usuarios->execute();
            $usuarios = $usuarios->fetchAll(PDO::FETCH_ASSOC);
            foreach ($usuarios as $usuario) {
                $notiVisualizacion = $conn->prepare("INSERT INTO noti_visualizaciones (noti_id, notiVis_cedula) VALUES (:noti_id, :cedula)");
                $notiVisualizacion->execute([
                    ":noti_id" => $notiId,
                    ":cedula" => $usuario["cedula"]
                ]);
            }

            //notificacioneS PARA EL ROL 4
            $notificacion = $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_fecha, noti_detalle, noti_destinatario) VALUES (:cedula, :fecha, :detalle, :destinatario)");
            $notificacion->execute([
                ":cedula" => $_SESSION["user"]["cedula"],
                ":fecha" => date("Y-m-d H:i:s"),
                ":detalle" => $notiDetalle,
                ":destinatario" => 4
            ]);

            // crear un row en la tabla noti_visualizaciones por cada usuario que tenga el rol 2
            $notiId = $conn->lastInsertId();
            $usuarios = $conn->prepare("SELECT P.cedula FROM personas P
                                        JOIN usuarios U ON P.cedula = U.cedula
                                        WHERE usu_rol = 4");
            $usuarios->execute();
            $usuarios = $usuarios->fetchAll(PDO::FETCH_ASSOC);
            foreach ($usuarios as $usuario) {
                $notiVisualizacion = $conn->prepare("INSERT INTO noti_visualizaciones (noti_id, notiVis_cedula) VALUES (:noti_id, :cedula)");
                $notiVisualizacion->execute([
                    ":noti_id" => $notiId,
                    ":cedula" => $usuario["cedula"]
                ]);
            }

            header("Location: ./planosActividades.php?id=$last_id");
        }
    }
}
?>



<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="">
            <?php if ($idop): ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- si la op no existe -->
                            <?php if (!$op): ?>
                                <div class="alert alert-danger">La OP # <?= $idop ?> no existe.</div>
                            <?php else: ?>
                            <div class="card ">
                                <div class="card-header"><h4>INFORMACION DE LA OP <?= $op['op_id'] ?> </h4></div>
                                <div class="card-body d-flex">
                                    <!-- seccion de la izquierda con los datos de la op-->
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <h3><em># OP:</em>            <?= $op['op_id'] ?></h3>
                                            <h3><em>DETALLE GENERAL:</em> <?= $op['od_detalle'] ?></h3>
                                            <h3><em>CLIENTE:</em>         <?= $op['od_cliente'] ?></h3>
                                            <h3><em>RESPONSABLE:</em>     <?= $op['od_responsable'] ?></h3>
                                            <h3><em>DIRECCION:</em>       <?= $op['op_direccionLocal'] ?></h3>
                                        </div>
                                    </div>
                                    <!-- seccion de la derecha con el form -->
                                    <div class="col-lg-6 text-right">
                                        <h3>INGRESAR PLANOS</h3>
                                        <?php if ($error) : ?>
                                            <div class="alert alert-danger"><?= $error ?></div>
                                        <?php endif ?>
                                        <form action="planosAddtest.php?id=<?= $idop ?>" method="post">
                                            <div class="form-group">
                                                <label for="pla_numero">Número de Plano</label>
                                                <input type="text" name="pla_numero" id="pla_numero" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="pla_descripcion">Descripción</label>
                                                <input type="text" name="pla_descripcion" id="pla_descripcion" class="form-control"></input>
                                            </div>
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">AGREGAR PLANO</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif ?>
                        </div>
                    </div>
                </section>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- si la op no existe no mostrar la tabla -->
                            <?php if (!$op): ?>

                            <?php else: ?>
                            <div class="card ">
                                <div class="card-header"><h4>PLANOS DE LA OP <?= $op['op_id'] ?> </h4></div>
                                <div class="card-body">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>Número de Plano</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($planos as $plano) : ?>
                                                <tr>
                                                    <td><?= $plano["pla_numero"] ?></td>
                                                    <td><?= $plano["pla_descripcion"] ?></td>
                                                    <td><?= $plano["pla_estado"] ?></td>
                                                    <td>
                                                        <a href="./planosActividades.php?id=<?= $plano["pla_id"] ?>" class="btn btn-primary">VER ACTIVIDADES</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endif ?>
                        </div>
                    </div>
                </section>
            <?php endif ?>
            <section>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"> BUSCAR PLANOS POR OP</h5>
                                <form action="planosAddtest.php" method="get">
                                    <div class="form-group">
                                        <label for="id">ID DE LA OP</label>
                                        <input type="text" name="id" id="id" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">BUSCAR</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            </div>    
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
