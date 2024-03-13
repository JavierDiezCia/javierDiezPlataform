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

// Verificar el rol del usuario
if ($_SESSION["user"]["usu_rol"] && ($_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 1)) {

    // Verificar el método de solicitud HTTP
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validar que no se envíen datos vacíos
        if (empty($_POST["detalle"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } else {
            // Insertar una nueva actividad relacionada con la orden de diseño
            $statement = $conn->prepare("INSERT INTO od_actividades (od_id, odAct_detalle, odAct_fechaEntrega) VALUES (:od_id, :detalle, :fechaEntrega)");
            $statement->execute([
                ":od_id" => $id,
                ":detalle" => $_POST["detalle"],
                ":fechaEntrega" => $_POST["fechaEntrega"]
            ]);
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
    $actividades = $conn->prepare("SELECT * FROM od_actividades WHERE od_id = :od_id AND odAct_estado = 0 ORDER BY id DESC");
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
    <div class="row">
        <div class="card p-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Actividades para la Orden de Diseño <?php echo $orden['od_detalle']; ?></h2>
                        <?php if ($error): ?>
                            <p class="text-danger"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="detalle" class="form-label">Detalle de la Actividad</label>
                                <input type="text" class="form-control" id="detalle" name="detalle" required>
                            </div>
                            <div class="mb-3">
                                <label for="fechaEntrega" class="form-label">Fecha de Entrega</label>
                                <input type="datetime-local" class="form-control" id="fechaEntrega" name="fechaEntrega" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Agregar Actividad</button>
                        </form>

                        <hr>

                        <h3>Listado de Actividades</h3>
                        <ul class="list-group">
                            <?php $contador = $actividades->rowCount(); ?>
                            <?php foreach ($actividades as $actividad): ?>
                                <li class="list-group-item list-group-item d-flex justify-content-between align-items-center">
                                    <p><?= $contador-- ?></p>
                                    <?php echo $actividad['odAct_detalle']; ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $actividad['odAct_fechaEntrega']; ?></span>
                                    <a class="text-rigth" href="./validaciones/deleteActividad.php?id=<?= $actividad["id"]?>&od_id=<?= $actividad["od_id"] ?>">
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
