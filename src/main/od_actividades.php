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


if (!empty($idAct)) {
    $actividad = $conn->prepare("SELECT * FROM od_actividades WHERE odAct_id = :id");
    $actividad->execute([":id" => $idAct]);
    $actividadEdit = $actividad->fetch(PDO::FETCH_ASSOC);
}

// Verificar el rol del usuario
if ($_SESSION["user"]["usu_rol"] && ($_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 1)) {

    // verificamos que el estado sea PROPUESTA, OP, OP CREADA PARA REALIZAR ACCIONES
    $stmt = $conn->prepare("SELECT od_estado FROM orden_disenio WHERE od_id = :id");
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
            $statement = $conn->prepare("SELECT COUNT(*) FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0 AND UPPER(odAct_detalle) = :detalle");
            $statement->execute([
                ":od_id" => $id,
                ":detalle" => $detalle
            ]);
            $count = $statement->fetchColumn();

            $detalleEdit = strtoupper($_POST["detalle"]);

            $stmt = $conn->prepare("UPDATE od_actividades SET odAct_detalle = :detalle, odAct_fechaEntrega = :fechaEntrega WHERE odAct_id = :id");
            $stmt->execute([
                ":detalle" => $detalleEdit,
                ":fechaEntrega" => $_POST["fechaEntrega"],
                ":id" => $idAct
            ]);

            //si la actividad tenia registros con el detalle anterior, actualizar el detalle
            $stmt = $conn->prepare("UPDATE registros_disenio SET rd_detalle = :detalle WHERE od_id = :id AND rd_detalle = :detalleAnterior AND rd_delete = 0");
            $stmt->execute([
                ":detalle" => $detalleEdit,
                ":id" => $id,
                ":detalleAnterior" => $actividadEdit["odAct_detalle"]
            ]);


            // notificaciones para lso diseniadores rol 3
            $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_destinatario, noti_detalle, noti_fecha) VALUES (:cedula, :destinatario, :detalle, :fecha)")->execute([
                ":cedula" => $_SESSION["user"]["cedula"],
                ":destinatario" => 3,
                ":detalle" => "Se ha editado la actividad " . "<b>$detalle</b>." . " de la orden de diseño " . "#" . $id . " " . $orden["od_detalle"],
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
            registrarEnKardex($_SESSION['user']['cedula'], "EDITÓ", 'ACTIVIDAD', "Actividad: " . $detalle . " de la orden de diseño " . "#" . $idAct . " " . $orden['od_detalle']);

            header("Location: ./od_actividades.php?id=$id");
            
        } else {
            // Convertir el detalle a mayúsculas
            $detalle = strtoupper($_POST["detalle"]);

            // Verificar si el detalle ya existe en la base de datos
            $statement = $conn->prepare("SELECT COUNT(*) FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0 AND UPPER(odAct_detalle) = :detalle");
            $statement->execute([
                ":od_id" => $id,
                ":detalle" => $detalle
            ]);
            $count = $statement->fetchColumn();

            if ($count > 0) {
                $error = "El detalle de la actividad ya existe.";
            } else {
                // Insertar una nueva actividad relacionada con la orden de diseño
                $statement = $conn->prepare("INSERT INTO od_actividades (od_id, odAct_detalle, odAct_fechaEntrega) VALUES (:od_id, :detalle, :fechaEntrega)");
                $statement->execute([
                    ":od_id" => $id,
                    ":detalle" => $detalle,
                    ":fechaEntrega" => $_POST["fechaEntrega"]
                ]);

                // notificaciones para lso diseniadores rol 3
                $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_destinatario, noti_detalle, noti_fecha) VALUES (:cedula, :destinatario, :detalle, :fecha)")->execute([
                    ":cedula" => $_SESSION["user"]["cedula"],
                    ":destinatario" => 3,
                    ":detalle" => "Se ha agregado una nueva actividad " . "<b>$detalle</b>." . " a la orden de diseño " . "#" . $id ,
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
                registrarEnKardex($_SESSION['user']['cedula'], "AGREGÓ", 'ACTIVIDAD', "Actividad: " . $detalle . " a la orden de diseño " . "#" . $id);

            }
        }
    }

    // Obtener la información de la orden de diseño según el ID proporcionado
    $statement = $conn->prepare("SELECT * FROM orden_disenio WHERE od_id = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $orden = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la orden de diseño existe
    if (!$orden) {
        echo "Orden de diseño no encontrada.";
        exit;
    }

    // Obtener todas las actividades relacionadas con la orden de diseño
    $actividades = $conn->prepare("SELECT * FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0 ORDER BY odAct_id DESC");
    $actividades->bindParam(":od_id", $id);
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <div class="row">
        <div class="card p-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2>Actividades para la Orden de Diseño <?php echo $orden['od_detalle']; ?></h2>
                            <a href="./od.php" class="btn btn-secondary"><i class="bi bi-arrow-90deg-left"></i></a>
                        </div>
                        <?php if ($error): ?>
                            <p class="text-danger"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <!-- PERMITIMOS EL FORMULARIO SOLO SI $stmt es PROPUESTA, OP, OP CREADA -->
                        <?php if ($estado["od_estado"] == "PROPUESTA" || $estado["od_estado"] == "OP" || $estado["od_estado"] == "OP CREADA"): ?>
                            <?php if ($idAct): ?>
                                <h3>Editar Actividad</h3>
                                <form method="POST">
                                <div class="mb-3">
                                    <label for="detalle" class="form-label">Detalle de la Actividad</label>
                                    <input value="<?= $actividadEdit['odAct_detalle'] ?>" type="text" class="form-control" id="detalle" name="detalle" required>
                                </div>
                                <div class="mb-3">
                                    <label for="fechaEntrega" class="form-label">Fecha de Entrega</label>
                                    <input value="<?= $actividadEdit['odAct_fechaEntrega'] ?>" type="datetime-local" class="form-control" id="fechaEntrega" name="fechaEntrega" >
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
                                    <p><?= $contador ?></p>
                                    <?php echo $actividad['odAct_detalle']; ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $actividad['odAct_fechaEntrega']; ?></span>
<div id="cronometro">00:00:00</div>
<i class="fas fa-play"></i>
                                    <!-- boton de editar -->
                                    <a href="./od_actividades.php?idAct=<?= $actividad["odAct_id"] ?>&id=<?= $actividad["od_id"] ?>" class="text-rigth">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="gray" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path d="M0 1a1 1 0 0 1 1-1h5.586a1 1 0 0 1 .707.293l8.914 8.914a1 1 0 0 1 0 1.414l-3.586 3.586a1 1 0 0 1-1.414 0l-8.914-8.914a1 1 0 0 1-.293-.707V1z"/>
                                            <path d="M0 2v13h13V2H0z"/>
                                        </svg>
                                    </a>
                                    <!-- si la actividad no tiene registro permitir borrar, caso contrario no mostrar el boton -->
                                    <?php
                                    //VERIFICAR SI HAY REGISTROS SIN ACTIVIDADES
                                    $detallesSinRegistro = $conn->prepare("SELECT rd_detalle FROM registros_disenio WHERE od_id = :id AND rd_hora_fin IS NOT NULL AND rd_delete = 0 AND rd_detalle = :detalle");
                                    $detallesSinRegistro->execute([":id" => $id, ":detalle" => $actividad["odAct_detalle"]]);
                                    $detallesSinRegistro = $detallesSinRegistro->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php if (!empty($detallesSinRegistro)): ?>
                                        <a class="text-rigth">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="gray" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                            </svg>
                                        </a>
                                    <?php else : ?>
                                        <a class="text-rigth disabled" href="./validaciones/od/deleteActividad.php?id=<?= $actividad["odAct_id"]?>&od_id=<?= $actividad["od_id"] ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="red" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
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
