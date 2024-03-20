<?php

if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

$odMine = $conn->prepare("SELECT od.*, 
        personas_responsable.per_nombres AS responsable_nombres, 
        personas_responsable.per_apellidos AS responsable_apellidos,
        personas_comercial.per_nombres AS comercial_nombres,
        personas_comercial.per_apellidos AS comercial_apellidos
        FROM orden_disenio od
        JOIN personas personas_responsable ON od.od_responsable = personas_responsable.cedula
        JOIN personas personas_comercial ON od.od_comercial = personas_comercial.cedula
        WHERE od.od_responsable = :diseniador
        ORDER BY od.od_id DESC");
    $odMine->bindParam(":diseniador", $diseniador);
    $odMine->execute();

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
        </tr>
    </thead>
    <tbody>
        <?php foreach ($odMine as $od): ?>
            <tr>
                <td><?= $od["od_id"] ?></td>
                <td><?= $od["responsable_nombres"] ?> <?= $od["responsable_apellidos"] ?></td>
                <td><?= $od["od_detalle"] ?></td>
                <td><?= $od["od_cliente"] ?></td>
                <td><?= $od["comercial_nombres"] ?> <?= $od["comercial_apellidos"] ?></td>
                <td><?= $od["od_estado"] ?></td>
                <td>
                    <a href="./od_actividades.php?id=<?= $od["od_id"] ?>" class="btn btn-primary mb-2">VER ACTIVIDADES</a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>