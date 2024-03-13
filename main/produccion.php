<?php 
require "../sql/database.php";
require "./partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Validación para el usuario tipo diseñador
if ($_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 1) {
    // Obtener el diseñador de la sesión activa
    $diseniador = $_SESSION["user"]["cedula"];

    // Buscar OP (Orden de Producción) existentes en estado "EN PRODUCCION"
    $opQuery = $conn->prepare("SELECT op_id FROM op WHERE op_estado = 'EN PRODUCCION'");
    $opQuery->execute();
    $ops = $opQuery->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["op_id"]) || empty($_POST["pla_id"]) || empty($_POST["areatrabajo"])) {
            $error = "Por favor complete todos los campos.";
        } else {
            // Insertamos un nuevo registro en la tabla "produccion"
            $statement = $conn->prepare("INSERT INTO produccion (pla_id, pro_fecha) VALUES (:pla_id, CURRENT_TIMESTAMP)");
            $statement->execute([
                ":pla_id" => $_POST["pla_id"]
            ]);

            // Obtenemos el ID del registro de producción recién insertado
            $pro_id = $conn->lastInsertId();

            // Insertamos las áreas asociadas al registro de producción en la tabla "pro_areas"
            $areasSeleccionadas = $_POST["areatrabajo"];
            foreach ($areasSeleccionadas as $area) {
                $insertStatement = $conn->prepare("INSERT INTO pro_areas (pro_id, proAre_detalle, proAre_fechaIni, proAre_fechaFin, proAre_porcentaje) 
                                                  VALUES (:pro_id, :area, CURRENT_TIMESTAMP, :fecha_fin, 0)");
                $insertStatement->execute([
                    ":pro_id" => $pro_id,
                    ":area" => $area,
                    ":fecha_fin" => $_POST["fecha_fin"]
                ]);
            }

            // Redirigimos a la página principal o a donde desees
            header("Location: produccion.php");
            return;
        }
    }
} else {
    // Redirigimos a la página principal o a donde desees
    header("Location: pages-error-404.html");
    return;
}

// Obtener los registros que no tienen proArePorcentaje al 100%
$registrosIncompletosQuery = $conn->prepare("SELECT 
                                            pa.pro_id,
                                            op.op_id AS op_id,
                                            p.pla_id AS plano_id,
                                            pl.pla_numero,
                                            pa.proAre_detalle AS area,
                                            pa.proAre_porcentaje,
                                            pa.proAre_fechaIni
                                            FROM 
                                            pro_areas pa
                                            JOIN 
                                            produccion p ON pa.pro_id = p.pro_id
                                            JOIN 
                                            planos pl ON p.pla_id = pl.pla_id
                                            JOIN 
                                            op op ON pl.op_id = op.op_id
                                            WHERE 
                                            pa.proAre_porcentaje < 100;
                                            ");
$registrosIncompletosQuery->execute();
$registrosIncompletos = $registrosIncompletosQuery->fetchAll(PDO::FETCH_ASSOC);

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Asignación de Áreas de Trabajo</h5>

                    <!-- Si hay un error, mostrarlo -->
                    <?php if ($error): ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="produccion.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="op_id" name="op_id" required>
                                    <option selected disabled value="">Selecciona la OP</option>
                                    <?php foreach ($ops as $op): ?>
                                        <option value="<?= $op["op_id"] ?>"><?= $op["op_id"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <label for="op_id">OP</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="pla_id" name="pla_id" required>
                                    <!-- Aquí se cargarán los planos de la OP seleccionada mediante JavaScript -->
                                </select>
                                <label for="pla_id">Plano</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" type="datetime-local" id="fecha_fin" name="fecha_fin" required>
                                <label for="fecha_fin">Fecha de Finalización</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <p>Selecciona las áreas de trabajo:</p>
                                <?php
                                // Definir las áreas de trabajo
                                $areas = array("CARPINTERÍA", "ACM", "PINTURA", "ACRÍLICOS Y ACABADOS", "MÁQUINAS", "METALMECÁNICA");
                                foreach ($areas as $area) {
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input' type='checkbox' name='areatrabajo[]' value='" . $area . "' id='" . $area . "'>";
                                    echo "<label class='form-check-label' for='" . $area . "'>" . $area . "</label>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php if ($registrosIncompletos): ?>
    <section class="section">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registros Incompletos</h5>
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th># DE OP</th>
                                        <th># DE PLANO</th>
                                        <th>FECHA DE REGISTRO</th>
                                        <th>ÁREA</th>
                                        <th>PORCENTAJE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrosIncompletos as $registro): ?>
                                        <tr>
                                            <td><?= $registro["op_id"] ?></td>
                                            <td><?= $registro["pla_numero"] ?></td>
                                            <td><?= $registro["proAre_fechaIni"] ?></td>
                                            <td><?= $registro["area"] ?></td>
                                            <td><?= $registro["proAre_porcentaje"] ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif ?>


<?php require "./partials/footer.php"; ?>

<!-- Mostrar registros incompletos -->


<script>
    // Cargar los planos correspondientes a la OP seleccionada
    document.getElementById('op_id').addEventListener('change', function() {
        var op_id = this.value;
        var pla_id_select = document.getElementById('pla_id');
        // Limpiar el select de planos
        pla_id_select.innerHTML = '<option selected disabled value="">Selecciona el Plano</option>';
        // Hacer una petición AJAX para obtener los planos de la OP seleccionada
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'Ajax.php?op_id=' + op_id, true);
        xhr.onload = function() {
            if (this.status == 200) {
                var planos = JSON.parse(this.responseText);
                planos.forEach(function(plano) {
                    var option = document.createElement('option');
                    option.text = plano.pla_numero;
                    option.value = plano.pla_id;
                    pla_id_select.add(option);
                });
            }
        }
        xhr.send();
    });
</script>
