<?php
require "../sql/database.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
if ($_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 1) {
    // Obtener el diseñador de la sesión activa
    $trabajador = $_SESSION["user"]["cedula"];
    // Consultar el registro actual del diseñador
    $registroQuery = $conn->prepare("SELECT *
    FROM registro
    JOIN registro_empleado ON registro.reg_id = registro_empleado.reg_id
    JOIN registro_empleado_actividades AS Re ON registro.reg_id = Re.reg_id
    JOIN produccion ON registro.pro_id = produccion.pro_id
    JOIN planos ON produccion.pla_id = planos.pla_id
    WHERE registro.reg_cedula = :trabajador
     AND registro_empleado.reg_fechaFin IS NULL
    LIMIT 1");
    $registroQuery->execute(array(':trabajador' => $trabajador)); // Suponiendo que $cedula es la variable que contiene el área de trabajo del diseñador
    $registro = $registroQuery->fetch(PDO::FETCH_ASSOC);
    $detalleQuery = $conn->prepare("SELECT reg_detalle FROM registro_empleado_actividades WHERE reg_id = :registroId");
    $detalleQuery->execute(array(':registroId' => $registro["reg_id"]));
    $detalles = $detalleQuery->fetchAll(PDO::FETCH_COLUMN);
    // Verificamos si se encontró el registro actual
    if (!$registro) {
        header("Location: registroEmpleado.php");
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Insertamos un nuevo registro
            $statement = $conn->prepare("UPDATE registro_empleado SET reg_fechaFin = CURRENT_TIMESTAMP WHERE reg_id = :id");

            $statement->execute([
                ":id" => $registro["reg_id"]
            ]);
            $statement1 = $conn->prepare("UPDATE registro SET reg_observacion = :observaciones WHERE reg_id = :id");
            
            $statement1->execute([
                ":observaciones" => $_POST["observaciones"],
                ":id" => $registro["reg_id"]
            ]);
            // Redirigimos a la página principal o a donde desees
            header("Location: index.php");
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
                    <?php if ($error) : ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="registroEmpleadoFin.php">
                    <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["op_id"] ?>" class="form-control" id="op_id" name="op_id" placeholder="op_id" require readonly></input>
                                <label>OP</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["pla_numero"] ?>" class="form-control" id="pla_numero" name="pla_numero" placeholder="pla_numero" require readonly></input>
                                <label>PLANO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                        <label for="reg_detalle" class="form-label">Actividades</label>
                            <div class="form-floating mb-3">
                            
                                <ul>
                                    <?php foreach ($detalles as $detalle) : ?>
                                        <li><?= $detalle ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["reg_fecha"] ?>" class="form-control" id="reg_fecha" name="reg_fecha" placeholder="reg_fecha" require readonly></input>
                                <label>Fecha de Registro</label>
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