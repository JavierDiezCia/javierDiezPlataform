<?php 
require "../sql/database.php";
require "./partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
//validacion para el usuario tipo diseniador 
if ($_SESSION["user"]["usu_rol"] == 3||$_SESSION["user"]["usu_rol"] == 1) {
    // Obtener el diseñador de la sesión activa
    $diseniador = $_SESSION["user"]["cedula"];

    // Consultar el registro actual del diseñador
    $registroQuery = $conn->prepare("SELECT registros_disenio.*, orden_disenio.od_detalle
    FROM registros_disenio
    JOIN orden_disenio ON registros_disenio.od_id = orden_disenio.od_id
    WHERE registros_disenio.rd_diseniador = :diseniador
    AND registros_disenio.rd_hora_fin IS NULL
    LIMIT 1;
    ");
    $registroQuery->execute(array(':diseniador' => $diseniador));
    $registro = $registroQuery->fetch(PDO::FETCH_ASSOC);

    // Verificamos si se encontró el registro actual
    if (!$registro) {
        header("Location: registroOd.php");
        return;
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que no se manden datos vacíos
            
            // Insertamos un nuevo registro
            $statement = $conn->prepare("UPDATE registros_disenio SET rd_hora_fin = CURRENT_TIMESTAMP, rd_observaciones = :observaciones WHERE rd_id = :id");

            $statement->execute([
                ":observaciones" => $_POST["observaciones"],
                ":id" => $registro["rd_id"]
            ]);

            // Redirigimos a la página principal o a donde desees
            header("Location: historialRegistros.php");
            return;
            
        }
    }
} else {
    // Redirigimos a la página principal o a donde desees
    header("Location: pages-error-404.html");
    return;
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">REGISTRO ACTUAL</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="registroOdFinal.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["od_detalle"] ?>" class="form-control" id="od_detalle" name="od_detalle" placeholder="od_detalle" required readonly></input>
                                <label for="od_detalle">PRODUCTO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["rd_detalle"] ?>" class="form-control" id="od_detalle" name="od_detalle" placeholder="od_detalle" required readonly></input>
                                <label for="od_detalle">ACTIVIDAD</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["rd_hora_ini"] ?>" class="form-control" id="horainicio" name="horainicio" placeholder="horainicio" required readonly></input>
                                <label for="horainicio">HORA INICIO</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
                                <label for="observaciones">OBSERVACIONES (Opcional).</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">FINALIZAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
