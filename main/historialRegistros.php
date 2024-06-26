<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable $registros
$registros = null;

//BUSCAMOS EL DATO DEL USER PARA QUE SE IDENTIFIQUE
$usuario = $_SESSION["user"]["cedula"];

// Validamos los perfiles
if ($_SESSION["user"]["usu_rol"] == 2||$_SESSION["user"]["usu_rol"] == 1) {
    // Definir los nombres de los días de la semana
    $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    // Si el rol es 2 (Diseñador ADMIN), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de orden_disenio
    $registros = $conn->prepare("SELECT R.*, O.od_detalle, O.od_cliente, P.per_nombres, P.per_apellidos 
    FROM registros_disenio R 
    JOIN orden_disenio O ON R.od_id = O.od_id 
    JOIN personas P ON R.rd_diseniador = P.cedula
    JOIN usuarios U ON P.cedula = U.cedula
    WHERE U.usu_rol = 3 AND R.rd_delete = 0
    ORDER BY R.rd_id DESC
    ");
    $registros->execute();

    // Obtenemos los nombres de todos los usuarios con el rol 3
    $usuarios_rol_3 = $conn->prepare("SELECT P.per_nombres, P.per_apellidos
        FROM personas P 
        JOIN usuarios U ON P.cedula = U.cedula
        WHERE U.usu_rol = 3");
    $usuarios_rol_3->execute();

    // Creamos un array para almacenar los nombres de los usuarios con rol 3
    $nombres_usuarios_rol_3 = [];
    while ($row = $usuarios_rol_3->fetch(PDO::FETCH_ASSOC)) {
        $nombres_usuarios_rol_3[] = $row["per_nombres"] . " " . $row["per_apellidos"];
    }




} elseif ($_SESSION["user"]["usu_rol"] == 3) {
    // Si el rol es 3 (Diseñador), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de orden_disenio
    $registros = $conn->prepare("SELECT R.*, O.od_detalle, O.od_cliente, P.per_nombres, P.per_apellidos 
                                FROM registros_disenio R 
                                JOIN orden_disenio O ON R.od_id = O.od_id 
                                JOIN personas P ON R.rd_diseniador = P.cedula
                                WHERE R.rd_diseniador = :usuario AND R.rd_delete = 0
                                ORDER BY R.rd_id DESC");
    $registros->bindParam(":usuario", $usuario);
    $registros->execute();

} else {
    // Si el rol no es ninguno de los anteriores, redirigimos al usuario a la página de inicio
    header("Location:./index.php");
    return;
}

?>


<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <?php if (($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 3)) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h5 class="card-tittle">MIS REGISTROS</h5>
                                    </div>
                                    <h5 class="col-md-4 mx-auto mb-3"></h5>

                                    <?php if ($registros->rowCount() == 0) : ?>
                                        <div class="col-md-4 mx-auto mb-3">
                                            <div class="card card-body text-center">
                                                <p>NO HAY REGISTROS AÚN.</p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <!-- Table with stripped rows -->
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>#OD</th>
                                                    <th>DETALLE</th>
                                                    <th>CLIENTE</th>
                                                    <th>ACTIVIDAD</th>
                                                    <th>HORA INICIO</th>
                                                    <th>HORA FINAL</th>
                                                    <th>OBSERVACIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $contador = $registros->rowCount(); ?>
                                                <?php foreach ($registros as $registros) : ?>
                                                    <tr>
                                                        <td><?= $contador-- ?></td>
                                                        <th><?= $registros["od_id"] ?></th>
                                                        <th><?= $registros["od_detalle"] ?></th>
                                                        <th><?= $registros["od_cliente"] ?></th>
                                                        <th><?= $registros["rd_detalle"] ?></th>
                                                        <td><?= $registros["rd_hora_ini"] ?></td>
                                                        <td><?= $registros["rd_hora_fin"] ?></td>
                                                        <td><?= $registros["rd_observaciones"] ?></td>
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
            <?php elseif (($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 2)||($_SESSION["user"]["usu_rol"] == 1)) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card accordion" id="accordionExample">
                                <div class="card-body accordion-item">
                                    <h5 class="card-title accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            REGISTROS
                                        </button>
                                    </h5>
                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                                <button type="button" class="btn btn-success btn-xs" data-bs-toggle="modal" data-bs-target="#reporte">
                                                    <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
                                                    Exportar a Excel
                                                </button>
                                                <div class="modal fade" id="reporte" tabindex="-1" style="display: none;" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">REPORTE DE LAS ORDENES DE DISEÑO</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>ESTA SEGURO DE GENERAR EL REPORTE DE LAS ORDENES DE DISEÑO SI ES ASI POR FAVOR SELECCIONE EL AÑO Y EL MES DEL REPORTE QUE SE VA A GENERAR Y SELECCIONE GENERAR EL REPORTE</p>
                                                                <form action="./reporte_exel/exel_disenio.php" method="post"> <!-- Modificado: Formulario que envía los datos mediante POST -->
                                                                    <div class="form-group">
                                                                        <label for="selectYear">AÑO:</label>
                                                                        <select class="form-control" id="selectYear" name="selectYear"> <!-- Agregado: name="selectYear" para identificar el campo en PHP -->
                                                                            <option value="2024">2024</option>
                                                                            <option value="2025">2025</option>
                                                                            <option value="2026">2026</option>
                                                                            <option value="2027">2027</option>
                                                                            <!-- Agrega más opciones según sea necesario -->
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
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
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button>
                                                                        <button type="submit" class="btn btn-success">GENERAR REPORTE</button> <!-- Modificado: Botón submit para enviar el formulario -->
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    

                                    <h5 class="col-md-4 mx-auto mb-3"></h5>

                                    <?php if ($registros->rowCount() == 0) : ?>
                                        <div class="col-md-4 mx-auto mb-3">
                                            <div class="card card-body text-center">
                                                <p>NO HAY REGISTROS AÚN</p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <!-- Table with stripped rows -->
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>#OD</th>
                                                    <th>DISEÑADOR</th>
                                                    <th>DETALLE</th>
                                                    <th>CLIENTE</th>
                                                    <th>ACTIVIDAD</th>
                                                    <th>HORA INICIO</th>
                                                    <th>HORA FINAL</th>
                                                    <th>OBSERVACIONES</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $contador = $registros->rowCount(); ?>
                                                <?php foreach ($registros as $registros) : ?>

                                                    <tr>
                                                        <td><?= $contador-- ?></td>
                                                        <th><?= $registros["od_id"] ?></th>
                                                        <th><?= $registros["per_nombres"] . " " . $registros["per_apellidos"] ?></th>
                                                        <th><?= $registros["od_detalle"] ?></th>
                                                        <th><?= $registros["od_cliente"] ?></th>
                                                        <th><?= $registros["rd_detalle"] ?></th>
                                                        <td><?= $registros["rd_hora_ini"] ?></td>
                                                        <td><?= $registros["rd_hora_fin"] ?></td>
                                                        <td><?= $registros["rd_observaciones"] ?></td>
                                                        <td>
                                                            <a href="delete/registroDisenio.php?id=<?= $registros["rd_id"] ?>" class="btn btn-danger mb-2">ELIMINAR</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    <?php endif ?>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">SEMANA</h5>

                                    <!-- Column Chart -->
                                    <div id="columnChart"></div>

                                    <?php require "./partials/charts/registrosDisenio.php"; ?>
                                    
                                    
                                    <!-- End Column Chart -->

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