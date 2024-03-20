<?php

if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

?>

<table class="table datatable">
    <thead>
        <tr>
            <th>#OD</th>
            <th>RESPONSABLE</th>
            <th>DETALLE</th>
            <th>CLIENTE</th>
            <th>COMERCIAL</th>
            <th>ESTADO</th>
            <th>ACTIVIDADES</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ordenes as $orden): ?>
            <tr>
                <td><?= $orden["od_id"] ?></td>
                <td><?= $orden["responsable_nombres"] ?> <?= $orden["responsable_apellidos"] ?></td>
                <td><?= $orden["od_detalle"] ?></td>
                <td><?= $orden["od_cliente"] ?></td>
                <td><?= $orden["comercial_nombres"] ?> <?= $orden["comercial_apellidos"] ?></td>
                <td><?= $orden["od_estado"] ?></td>
                <td>
                    <a href="./od_actividades.php?id=<?= $orden["od_id"] ?>" class="btn btn-primary mb-2">VER ACTIVIDADES</a>
                </td>
                <td>
                    <?php
                    //VERIFICAR SI HAY REGISTROS SIN ACTIVIDADES
                    $detallesSinRegistro = $conn->prepare("SELECT odAct_detalle FROM od_actividades WHERE od_id = :id AND odAct_estado = 0 AND odAct_detalle NOT IN (SELECT rd_detalle FROM registros_disenio WHERE od_id = :id AND rd_hora_fin IS NOT NULL AND rd_delete = 0)");
                    $detallesSinRegistro->execute([":id" => $orden["od_id"]]);
                    $detallesSinRegistro = $detallesSinRegistro->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (empty($detallesSinRegistro)) : ?>
                        <a href="validaciones/od/odRevisar.php?id=<?= $orden["od_id"] ?>" class="btn btn-success mb-2">ENVIAR PARA APROBAR</a>
                    <?php else : ?>
                        <a href="actividadesFaltantes.php?id=<?= $orden["od_id"] ?>" class="btn btn-danger mb-2">AÚN FALTAN ACTIVIDADES POR COMPLETAR!</a>
                    <?php endif ?>
                </td>
                <td>
                    <a href="od.php?id=<?= $orden["od_id"] ?>" class="btn btn-secondary mb-2">EDITAR</a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>