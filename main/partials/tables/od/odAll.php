<?php


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../../login-form/login.php");
    return;
}


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

// Preparar y ejecutar la consulta
$ordenes_disenio = $conn->query($query);
$ordenes_disenio->execute();
?>


<?php if ($ordenes_disenio->rowCount() == 0) : ?>
    <div class="col-md-4 mx-auto mb-3 mt-3">
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
                <?php if ($_SESSION["user"]["usu_rol"] == 1) : ?>
                    <th></th>
                <?php endif ?>
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
                        <a href="./detallesOd.php?id=<?= $orden["od_id"] ?>" class="btn btn-primary mb-2">VER REGISTROS</a>
                    </td>
                    <?php if ($_SESSION["user"]["usu_rol"] == 1) : ?>
                        <td>
                            <a href="validaciones/odPropuesta.php?id=<?= $orden["od_id"] ?>" class="btn btn-warning mb-2">EDITAR</a>
                        </td>
                    <?php endif ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>