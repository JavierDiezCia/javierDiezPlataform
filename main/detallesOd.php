<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 




// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
if (($_SESSION["user"]["usu_rol"] != 2)) {
    header("Location: ../index.php");
    return;
}

// Obtener el rd_id de la orden de diseño desde la URL
$id_orden_disenio = $_GET["id"];

// Obtener la información de la orden de diseño
$statement_od = $conn->prepare("SELECT od.*, P.per_nombres, P.per_apellidos 
                                    FROM orden_disenio od 
                                    JOIN personas P ON od.od_responsable = P.cedula
                                    WHERE od_id = :id");
$statement_od->execute([":id" => $id_orden_disenio]);
$orden_disenio = $statement_od->fetch(PDO::FETCH_ASSOC);

// Obtener los registros asociados a la orden de diseño
$registros = $conn->prepare("SELECT R.*, O.od_cliente, P.per_nombres, P.per_apellidos
                                FROM registros_disenio R 
                                JOIN orden_disenio O ON R.od_id = O.od_id 
                                JOIN personas P ON R.rd_diseniador = P.cedula
                                WHERE R.od_id = :id
                                ORDER BY R.rd_id DESC");
$registros->execute([":id" => $id_orden_disenio]);

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Mostrar información de la orden de diseño -->
                                <div class="card-header">
    <h5 class="card-tittle">DETALLES DE LA ORDEN DE DISEÑO</h5>
    <!-- Verificación del ID de la orden de diseño -->
    <?php
    // Verifica si $orden_disenio["od_id"] está definido y no es nulo
    if (isset($orden_disenio["od_id"])) {
        echo "<p>ID de la orden de diseño: " . $orden_disenio["od_id"] . "</p>";
    } else {
        echo "<p>ID de la orden de diseño no está definido o es nulo</p>";
    }
    ?>
    <!-- Botón para exportar a Excel con ícono desde la carpeta exel y estilizado con Bootstrap -->
    <a href="./reporte_exel/exel_orden_disenio.php?id=<?= $orden_disenio["od_id"] ?>" class="btn btn-success btn-xs">
        <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
        EXPORTAR A EXCEL
    </a>
</div>

                                <div class="card-body">
                                    <p><strong>NÚMERO DE ORDEN DE DISEÑO:</strong> <?php echo $orden_disenio["od_id"]; ?></p>
                                    <p><strong>DETALLE:</strong> <?php echo $orden_disenio["od_detalle"]; ?></p>
                                    <p><strong>RESPONSABLE:</strong> <?php echo $orden_disenio["per_nombres"] . " " . $orden_disenio["per_apellidos"]; ?></p>
                                    <p><strong>CLIENTE:</strong> <?php echo $orden_disenio["od_cliente"]; ?></p>
                                    <p><strong>ESTADO:</strong> <?php echo $orden_disenio["od_estado"]; ?></p>
                                </div>

                                <!-- Tabla con los registros asociados a la orden de diseño -->
                                <div class="card-header">
                                    <h5 class="card-tittle">REGISTROS DE ESTA ORDEN DE DISEÑO</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>DISEÑADOR</th>
                                                <th>ACTIVIDAD</th>
                                                <th>FECHA DE INICIO</th>
                                                <th>FECHA FINAL</th>
                                                <th>OBSERVACIONES</th>
                                                <th>ROL EN ESTA OD</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $contador = $registros->rowCount(); ?>
                                            <?php foreach ($registros as $registro) : ?>
                                                <tr>
                                                    <td><?= $contador-- ?></td>
                                                    <td><?php echo $registro["per_nombres"] . " " . $registro["per_apellidos"]; ?></td>
                                                    <td><?php echo $registro["rd_detalle"]; ?></td>
                                                    <td><?php echo $registro["rd_hora_ini"]; ?></td>
                                                    <td><?php echo $registro["rd_hora_fin"]; ?></td>
                                                    <td><?php echo $registro["rd_observaciones"]; ?></td>
                                                    <td><?php echo ($registro["rd_diseniador"] == $orden_disenio["od_responsable"]) ? 'RESPONSABLE' : 'COLABORADOR'; ?></td>
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
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>