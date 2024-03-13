<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Validamos los perfiles
if ($_SESSION["user"]["usu_rol"] != 2) {
    // Si el rol no es 2 (Diseñador ADMIN), redirigimos al usuario a la página de inicio
    header("Location:./index.php");
    return;
}

// Obtener el od_estado del filtro si está presente
$estado_filter = isset($_GET['od_estado']) ? intval($_GET['od_estado']) : null;

// Preparar la consulta base
$query = "SELECT od.*, 
                  persona_responsable.per_nombres AS responsable_nombres, 
                  persona_responsable.per_apellidos AS responsable_apellidos, 
                  persona_comercial.per_nombres AS comercial_nombres, 
                  persona_comercial.per_apellidos AS comercial_apellidos
          FROM orden_disenio od
          LEFT JOIN personas persona_responsable ON od.od_responsable = persona_responsable.cedula
          LEFT JOIN personas persona_comercial ON od.od_comercial = persona_comercial.cedula
          ORDER BY od.od_id DESC";


// Si hay un od_estado filtrado, agregarlo a la consulta
if ($estado_filter !== null) {
    $query .= " WHERE od_estado = :estado";
}

// Preparar y ejecutar la consulta
$ordenes_disenio = $conn->prepare($query);

// Si hay un od_estado filtrado, bindear el parámetro y ejecutar la consulta
if ($estado_filter !== null) {
    $ordenes_disenio->bindParam(':estado', $estado_filter, PDO::PARAM_INT);
}

$ordenes_disenio->execute();

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
                                <div class="card-header">
                                    <h5 class="card-tittle">ORDENES DE DISEÑO</h5>
                                    
                                </div>

                                <?php if ($ordenes_disenio->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>NO HAY ÓRDENES DE DISEÑO AÚN.</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th># OD</th>
                                                <th>RESPONSABLE</th>
                                                <th>DETALLE</th>
                                                <th>CLIENTE</th>
                                                <th>COMERCIAL</th>
                                                <th>FECHA DE REGISTRO</th>
                                                <th>ESTADO</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenes_disenio as $orden) : ?>
                                                <tr>
                                                    <th><?= $orden["od_id"] ?></th>
                                                    <td><?= $orden["responsable_nombres"] ?> <?= $orden["responsable_apellidos"] ?></td>
                                                    <th><?= $orden["od_detalle"] ?></th>
                                                    <th><?= $orden["od_cliente"] ?></th>
                                                    <td><?= $orden["comercial_nombres"] ?> <?= $orden["comercial_apellidos"] ?></td>
                                                    <th><?= $orden["od_fechaRegistro"] ?></th>
                                                    <th><?= $orden["od_estado"] ?></th>
                                                    <td>
                                                        <a href="detallesOd.php?id=<?= $orden["od_id"] ?>" class="btn btn-primary mb-2">VER REGISTROS</a>
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
            </section>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
