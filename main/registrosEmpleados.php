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
$registroActualizar = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selectYear"]) && isset($_POST["selectMonth"]) && isset($_POST["area"])) {
    $year = $_POST["selectYear"];
    $month = $_POST["selectMonth"];
    $area = $_POST["area"];
    $day = $_POST["selectDay"];

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
                                        AND DAY(r.reg_fecha) = :day
                                        AND re.reg_areaTrabajo = :area_trabajo");

            $registro->bindParam(":month", $month);
            $registro->bindParam(":year", $year);
            $registro->bindParam(":area_trabajo", $area);
            $registro->bindParam(":day", $day);

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
                        <select class="form-control" id="selectYear" name="selectYear">
                            <!-- Agregado: name="selectYear" para identificar el campo en PHP -->
                            <option value="">Seleccione el año</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <!-- Agrega más opciones según sea necesario -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="selectMonth">MES:</label>
                        <select class="form-control" id="selectMonth" name="selectMonth">
                            <!-- Agregado: name="selectMonth" para identificar el campo en PHP -->
                            <option value="">Selecione el mes</option>
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
                        <label for="selectDay">DÍA:</label>
                        <select class="form-control" id="selectDay" name="selectDay">
                            <!-- Las opciones para este campo se generarán dinámicamente con JavaScript -->
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
                    <script>
                        document.getElementById('selectYear').addEventListener('change', updateDays);
                        document.getElementById('selectMonth').addEventListener('change', updateDays);

                        function updateDays() {
                            var year = document.getElementById('selectYear').value;
                            var month = document.getElementById('selectMonth').value;
                            var daySelect = document.getElementById('selectDay');

                            // Limpiar las opciones existentes
                            daySelect.innerHTML = '';

                            // Obtener el número de días en el mes seleccionado
                            var days = new Date(year, month, 0).getDate();

                            // Generar nuevas opciones para el campo de selección del día
                            for (var i = 1; i <= days; i++) {
                                var option = document.createElement('option');
                                option.value = i;
                                option.text = i;
                                daySelect.appendChild(option);
                            }
                        }
                    </script>
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
                            <!-- Botón para exportar a Excel con ícono desde la carpeta exel y estilizado con Bootstrap -->
                            <a href="./reporte_exel/exel_trabajador_registro.php?year=<?= $year ?>&month=<?= $month ?>&area=<?= $area ?>" class="btn btn-success btn-xs">
                                <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
                                EXPORTAR A EXCEL
                            </a>
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
                                                <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#actualizar">Actualizar</button>
                                                <div class="modal fade" id="actualizar" tabindex="-1" style="display: none" aria-modal="true" role="dialog">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Actualizar Registro</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Esta seguro que desea actualizar el registro dela persona</p>
                                                                <form class="row g-3" method="POST" action="">
                                                                    <div class="col-md-6">
                                                                        <div class="form-floating mb-3">
                                                                            <select class="form-select" id="area" name="area" required onchange="obtenerActividades(this.value)">
                                                                                <option selected disabled value="">SELECIONE EL AREA</option>
                                                                                <option value="ACM">ACM</option>
                                                                                <option value="ACRÍLICOS Y ACABADOS">ACRÍLICOS Y ACABADOS</option>
                                                                                <option value="CARPINTERÍA">CARPINTERÍA</option>
                                                                                <option value="MAQUINAS">MAQUINAS</option>
                                                                                <option value="METALMECÁNICA">METALMECÁNICA</option>
                                                                                <option value="PINTURA">PINTURA</option>
                                                                            </select>
                                                                            <label for="area">Área</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-floating mb-3">
                                                                            <select class="form-select" id="op_id" name="op_id" required onchange="cargarPlanos(document.getElementById('area').value, this.value)">
                                                                                <option selected disabled value="">SELECCIONE LA ORDEN DE PRODUCCION</option>
                                                                            </select>
                                                                            <label for="op_id">Orden de Producción</label>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-floating mb-3">
                                                                            <select class="form-select" id="pla_id" name="pla_id" required>
                                                                                <option selected disabled value="">SELECIONE EL PLANO</option>
                                                                            </select>
                                                                            <label for="pla_id">Plano</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-floating mb-3">
                                                                            <input class="form-control" id="reg_fecha" name="reg_fecha" placeholder="reg_fecha" require readonly></input>
                                                                            <label>Fecha de Registro</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-floating mb-3">
                                                                            <h5 class="card-title">ACTIVIDADES</h5>
                                                                            <div class="form-floating mb-3" id="actividades">
                                                                                <!-- Las casillas de verificación de actividades se agregarán aquí dinámicamente -->
                                                                                <!-- Campo para ingresar otra actividad -->

                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button type="button" class="btn btn-primary">Actualizar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
<script>
    function obtenerActividades(area) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax.php?area=" + area, true);
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("actividades").innerHTML = this.responseText;

                // Aquí agregamos el código para cargar las órdenes de producción
                cargarOrdenesProduccion(area);

            }
        };
        xhr.send();
    }

    function cargarOrdenesProduccion(area) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax.php?areaOP=" + area, true);
        xhr.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    if (this.responseText.trim() !== "") {
                        document.getElementById("op_id").innerHTML = this.responseText;
                    } else {
                        console.error("La respuesta del servidor está vacía.");
                    }
                } else {
                    console.error("Error en la solicitud AJAX: " + this.status);
                }
            }
        };
        xhr.send();
    }

    function cargarPlanos(area, op_id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax.php?areaPlano=" + area + "&op_idPlanos=" + op_id, true);
        xhr.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    if (this.responseText.trim() !== "") {
                        document.getElementById("pla_id").innerHTML = this.responseText;
                    } else {
                        console.error("La respuesta del servidor está vacía.");
                    }

                } else {
                    console.error("Error en la solicitud AJAX: " + this.status);
                }
            }
        };
        xhr.send();
    }
</script>