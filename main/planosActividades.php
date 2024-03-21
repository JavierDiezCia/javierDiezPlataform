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
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$idAct = isset($_GET["idAct"]) ? $_GET["idAct"] : null;

// obtener la infor del plano
$plano = $conn->prepare("SELECT * FROM planos WHERE pla_id = :id");
$plano->execute([":id" => $id]);
$plano = $plano->fetch(PDO::FETCH_ASSOC);


if (!empty($idAct)) {
    $actividad = $conn->prepare("SELECT * FROM pla_actividades WHERE id = :id");
    $actividad->execute([":id" => $idAct]);
    $actividadEdit = $actividad->fetch(PDO::FETCH_ASSOC);
}

// Verificar el rol del usuario
if ($_SESSION["user"]["usu_rol"] && ($_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 1)) {

    // verificamos que el estado sea ACTIVO PARA REALIZAR ACCIONES
    $stmt = $conn->prepare("SELECT pla_estado FROM planos WHERE pla_id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $estado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar el método de solicitud HTTP
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validar que no se envíen datos vacíos
        if (empty($_POST["detalle"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } elseif ($idAct) {
            // proceso de editar una actividad
            $detalle = strtoupper($_POST["detalle"]);
            $statement = $conn->prepare("SELECT COUNT(*) FROM pla_actividades WHERE id = :id AND plaAct_estado = 0 AND UPPER(plaAct_detalle) = :detalle");
            $statement->execute([
                ":id" => $idAct,
                ":detalle" => $detalle
            ]);
            $count = $statement->fetchColumn();

            $detalleEdit = strtoupper($_POST["detalle"]);

            $stmt = $conn->prepare("UPDATE pla_actividades SET plaAct_detalle = :detalle, plaAct_fechaEntrega = :fechaEntrega WHERE id = :id");
            $stmt->execute([
                ":detalle" => $detalleEdit,
                ":fechaEntrega" => $_POST["fechaEntrega"],
                ":id" => $idAct
            ]);

            //si la actividad tenia registros con el detalle anterior, actualizar el detalle
            // $stmt = $conn->prepare("UPDATE registros_disenio SET rd_detalle = :detalle WHERE pla_id = :id AND rd_detalle = :detalleAnterior AND rd_delete = 0");
            // $stmt->execute([
            //     ":detalle" => $detalleEdit,
            //     ":id" => $id,
            //     ":detalleAnterior" => $actividadEdit["plaAct_detalle"]
            // ]);


            // notificaciones para lso diseniadores rol 3
            $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_destinatario, noti_detalle, noti_fecha) VALUES (:cedula, :destinatario, :detalle, :fecha)")->execute([
                ":cedula" => $_SESSION["user"]["cedula"],
                ":destinatario" => 3,
                ":detalle" => "Se ha editado la actividad " . "<b>$detalle</b>." . " del plano " . "#" . $id . " " . $plano["pla_detalle"],
                ":fecha" => date("Y-m-d H:i:s"),
            ]);
            // notificaciones con visualizaciones en la tabla noti_visualizaciones
            $notiId = $conn->lastInsertId();
            $usuarios = $conn->prepare("SELECT P.cedula FROM personas P
                                        JOIN usuarios U ON P.cedula = U.cedula
                                        WHERE usu_rol = 3");
            $usuarios->execute();
            $usuarios = $usuarios->fetchAll(PDO::FETCH_ASSOC);
            foreach ($usuarios as $usuario) {
                $notiVisualizacion = $conn->prepare("INSERT INTO noti_visualizaciones (noti_id, notiVis_cedula) VALUES (:noti_id, :cedula)");
                $notiVisualizacion->execute([
                    ":noti_id" => $notiId,
                    ":cedula" => $usuario["cedula"]
                ]);
            }
            // Registramos el movimiento en el kardex
            registrarEnKardex($_SESSION['user']['cedula'], "EDITÓ", 'ACTIVIDAD', "Actividad: " . $detalle . " de la orden de diseño " . "#" . $idAct . " " . $plano['pla_descripcion']);

            header("Location: ./planosActividades.php?id=$id");
            
        } else {
            // Convertir el detalle a mayúsculas
            $detalle = strtoupper($_POST["detalle"]);

            // Verificar si el detalle ya existe en la base de datos
            $statement = $conn->prepare("SELECT COUNT(*) FROM pla_actividades WHERE pla_id = :pla_id AND plaAct_estado = 0 AND UPPER(plaAct_detalle) = :detalle");
            $statement->execute([
                ":pla_id" => $id,
                ":detalle" => $detalle
            ]);
            $count = $statement->fetchColumn();

            if ($count > 0) {
                $error = "El detalle de la actividad ya existe.";
            } else {
                // Insertar una nueva actividad relacionada con la orden de diseño
                $statement = $conn->prepare("INSERT INTO pla_actividades (pla_id, plaAct_detalle, plaAct_fechaEntrega) VALUES (:pla_id, :detalle, :fechaEntrega)");
                $statement->execute([
                    ":pla_id" => $id,
                    ":detalle" => $detalle,
                    ":fechaEntrega" => $_POST["fechaEntrega"]
                ]);

                // notificaciones para lso diseniadores rol 3
                $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_destinatario, noti_detalle, noti_fecha) VALUES (:cedula, :destinatario, :detalle, :fecha)")->execute([
                    ":cedula" => $_SESSION["user"]["cedula"],
                    ":destinatario" => 3,
                    ":detalle" => "Se ha agregado una nueva actividad " . "<b>$detalle</b>." . " a el plano " . "#" . $plano['pla_numero'] . " " . "de la op # ". $id,
                    ":fecha" => date("Y-m-d H:i:s"),
                ]);
                // notificaciones con visualizaciones en la tabla noti_visualizaciones
                $notiId = $conn->lastInsertId();
                $usuarios = $conn->prepare("SELECT P.cedula FROM personas P
                                            JOIN usuarios U ON P.cedula = U.cedula
                                            WHERE usu_rol = 3");
                $usuarios->execute();
                $usuarios = $usuarios->fetchAll(PDO::FETCH_ASSOC);
                foreach ($usuarios as $usuario) {
                    $notiVisualizacion = $conn->prepare("INSERT INTO noti_visualizaciones (noti_id, notiVis_cedula) VALUES (:noti_id, :cedula)");
                    $notiVisualizacion->execute([
                        ":noti_id" => $notiId,
                        ":cedula" => $usuario["cedula"]
                    ]);
                }
                
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION['user']['cedula'], "AGREGÓ", 'ACTIVIDAD', "Actividad: " . $detalle . " a la orden de diseño " . "#" . $id . " " . $plano['pla_descripcion']);

            }
        }
    }

    // Obtener la información
    $statement = $conn->prepare("SELECT * FROM planos WHERE pla_id = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $plano = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la orden de diseño existe
    if (!$plano) {
        echo "Plano no encontrado.";
        exit;
    }

    // Obtener todas las actividades relacionadas con la orden de diseño
    $actividades = $conn->prepare("SELECT * FROM pla_actividades WHERE pla_id = :pla_id AND plaAct_estado = 0 ORDER BY id DESC");
    $actividades->bindParam(":pla_id", $id);
    $actividades->execute();
} else {
    // Si el usuario no tiene el rol adecuado, redirigir a la página de inicio
    header("Location: ./index.php");
    exit;
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="card p-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2>Actividades para el Plano # <?php echo $id; ?> | <?php echo $plano['pla_descripcion']; ?></h2>
                            <a href="./planosAddtest.php?id=<?= $plano['op_id'] ?>" class="btn btn-secondary"><i class="bi bi-arrow-90deg-left"></i></a>
                        </div>
                        <?php if ($error): ?>
                            <p class="text-danger"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <!-- PERMITIMOS EL FORMULARIO SOLO SI $stmt es ACTIVO -->
                        <?php if ($estado["pla_estado"] == "ACTIVO"): ?>
                            <?php if ($idAct): ?>
                                <h3>Editar Actividad</h3>
                                <form method="POST">
                                <div class="mb-3">
                                    <label for="detalle" class="form-label">Detalle de la Actividad</label>
                                    <input value="<?= $actividadEdit['plaAct_detalle'] ?>" type="text" class="form-control" id="detalle" name="detalle" required>
                                </div>
                                <div class="mb-3">
                                    <label for="fechaEntrega" class="form-label">Fecha de Entrega</label>
                                    <input value="<?= $actividadEdit['plaAct_fechaEntrega'] ?>" type="datetime-local" class="form-control" id="fechaEntrega" name="fechaEntrega" >
                                </div>
                                <button type="submit" class="btn btn-primary">Agregar Actividad</button>
                            </form>
                            <?php else: ?>
                                <h3>Agregar Actividad</h3>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="detalle" class="form-label">Detalle de la Actividad</label>
                                        <input type="text" class="form-control" id="detalle" name="detalle" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fechaEntrega" class="form-label">Fecha de Entrega</label>
                                        <input type="datetime-local" class="form-control" id="fechaEntrega" name="fechaEntrega" >
                                    </div>
                                    <button type="submit" class="btn btn-primary">Agregar Actividad</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-danger">No puedes agregar actividades a esta orden de diseño.</p>
                        <?php endif; ?>

                        <hr>

                        <h3>Listado de Actividades</h3>
                        <ul class="list-group">
                            <?php $contador = $actividades->rowCount(); ?>
                            <?php foreach ($actividades as $actividad): ?>
                                <li class="list-group-item list-group-item d-flex justify-content-between align-items-center">
                                    <p><?= $contador-- ?></p>
                                    <?php echo $actividad['plaAct_detalle']; ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $actividad['plaAct_fechaEntrega']; ?></span>
                                    <!-- boton de editar -->
                                    <a href="./planosActividades.php?id=<?= $actividad["pla_id"] ?>&idAct=<?= $actividad["id"] ?>" class="text-rigth">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="gray" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path d="M0 1a1 1 0 0 1 1-1h5.586a1 1 0 0 1 .707.293l8.914 8.914a1 1 0 0 1 0 1.414l-3.586 3.586a1 1 0 0 1-1.414 0l-8.914-8.914a1 1 0 0 1-.293-.707V1z"/>
                                            <path d="M0 2v13h13V2H0z"/>
                                        </svg>
                                    </a>
                                    <a class="text-rigth disabled" href="./validaciones/od/deleteActividad.php?pla=<?= $actividad["pla_id"] ?>&id=<?= $actividad["id"] ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="red" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                        </svg>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
