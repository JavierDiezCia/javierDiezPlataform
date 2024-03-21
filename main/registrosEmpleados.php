<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php";

// Verificar si la sesión no existe, redirigir al login.php y detener la ejecución
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

$error = null;
$result = []; // Inicializa el resultado como un array vacío

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selectYear"]) && isset($_POST["selectMonth"]) && isset($_POST["area"])) {
    $year = $_POST["selectYear"];
    $month = $_POST["selectMonth"];
    $area = $_POST["area"];

    if ($_SESSION["user"]["usu_rol"] == 1) {
        try {
            $registro = $conn->prepare("SELECT r.*, pe.per_nombres AS nombre,
                                        pe.per_apellidos AS apellido,
                                        pl.pla_numero,
                                        re.reg_areaTrabajo,
                                        re.reg_fechaFin,
                                        pe.per_areaTrabajo
                                        FROM registro AS r
                                        JOIN registro_empleado AS re ON r.reg_id = re.reg_id
                                        JOIN personas AS pe ON r.reg_cedula = pe.cedula
                                        JOIN planos AS pl ON r.pla_id = pl.pla_id
                                        WHERE MONTH(r.reg_fecha) = :month 
                                        AND YEAR(r.reg_fecha) = :year
                                        AND re.reg_areaTrabajo = :area_trabajo");

            $registro->bindParam(":month", $month);
            $registro->bindParam(":year", $year);
            $registro->bindParam(":area_trabajo", $area);

            // Execute the query
            $registro->execute();

            // Fetch all records
            $result = $registro->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        // Redirigir a la página principal o a donde desees si el usuario no tiene permisos adecuados
        header("Location: pages-error-404.html");
        return;
    }
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <h4 class="card-title text-center">Registro de Empleados</h4>
                <h5 class="card-title">Seleccione el Mes y el Año</h5>
                <!-- Si hay un error, mostrarlo -->
                <?php if ($error) : ?>
                    <p class="text-danger">
                        <?= $error ?>
                    </p>
                <?php endif ?>
                <form class="row g-3" method="POST" action="registrosEmpleados.php">
                    <div class="col-md-3">
                        <label for="selectYear">AÑO:</label>
                        <select class="form-control" id="selectYear" name="selectYear"> <!-- Agregado: name="selectYear" para identificar el campo en PHP -->
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <!-- Agrega más opciones según sea necesario -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="selectMonth">MES:</label>
                        <select class="form-control" id="selectMonth" name="selectMonth"> <!-- Agregado: name="selectMonth" para identificar el campo en PHP -->
                            <option value="1">ENERO</option>
                            <option value="2">FEBRERO</option>
                            <option value="3">MARZO</option>
                            <option value="4">ABRIL</option>
                            <option value="5">MAYO</option>
                            <option value="6">JUNIO</option>
                            <option value="7">JULIO</option>
                            <option value="8">AGOSTO</option>
                            <option value="9">SEPTIEMBRE</option>
                            <option value="10">OCTUBRE</option>
                            <option value="11">NOVIEMBRE</option>
                            <option value="12">DICIEMBRE</option>
                            <!-- Agrega más opciones según sea necesario -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="area">Área</label>
                        <select class="form-select" id="area" name="area" required>
                            <option selected disabled value="">SELECIONE EL AREA</option>
                            <option value="ACM">ACM</option>
                            <option value="ACRÍLICOS Y ACABADOS">ACRÍLICOS Y ACABADOS</option>
                            <option value="CARPINTERÍA">CARPINTERÍA</option>
                            <option value="MAQUINAS">MAQUINAS</option>
                            <option value="METALMECÁNICA">METALMECÁNICA</option>
                            <option value="PINTURA">PINTURA</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Bucar</button>
                        <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Mostrar la tabla solo si se han obtenido resultados -->
<?php if (!empty($result)) : ?>
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-header">
                            <h5 class="card-title text-center">Registro de Empleados por Area de ACM</h5>
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>Nombre del quien iso el registro</th>
                                        <th>OP</th>
                                        <th>Plano</th>
                                        <th>Area</th>
                                        <th>Fecha del Registro</th>
                                        <th>Fecha de Finalizacion del Registro</th>
                                        <th>Observaciones</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result as $reg) : ?>
                                        <tr>
                                            <td><?= $reg["nombre"] . " " . $reg["apellido"] ?></td>
                                            <td><?= $reg["op_id"] ?> </td>
                                            <td><?= $reg["pla_numero"] ?></td>
                                            <td><?php if ($reg["per_areaTrabajo"] === $reg["reg_areaTrabajo"]) : ?>
                                                    Pertenece
                                                <?php else : ?>
                                                    Apoyo
                                                <?php endif; ?></td>
                                            <td><?= $reg["reg_fecha"] ?></td>
                                            <td><?= $reg["reg_fechaFin"] ?></td>
                                            <td><?= $reg["reg_observacion"] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warmig mb-2" data-bs-toggle="modal" data-bs-target="#verticalycentered">Actualizar</button>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php else : ?>
    <div class="row">
        <div class="col-lg-12">
            <p class="text-center">No hay resultados para mostrar.</p>
        </div>
    </div>
<?php endif; ?>

<?php require "./partials/footer.php"; ?>