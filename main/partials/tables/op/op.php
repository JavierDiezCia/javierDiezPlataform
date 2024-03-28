
<div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">OP TOTALES</h5>
        <!-- Botón para exportar a Excel con ícono desde la carpeta exel y estilizado con Bootstrap -->
        <a href="./reporte_exel/exel_op.php" class="btn btn-success btn-xs">
            <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
            EXPORTAR A EXCEL
        </a>
    </div>
</div>
<table class="table datatable">
    <thead>
        <tr>
            <th>#OP</th>
            <th>CLIENTE</th>
            <th>DETALLE</th>
            <th>DISEÑADOR</th>
            <th>VENDEDOR</th>
            <th>FECHA DE REGISTRO</th>
            <th>DIRECCIÓN DEL LOCAL</th>
            <th>PERSONA DE CONTACTO</th>
            <th>TELÉFONO DE CONTACTO</th>
            <th>REPROCESO</th>
            <th>ESTADO</th>
        </tr>
    <tbody>
        <?php foreach ($optotal as $optotal) : ?>
            <tr>
                <td><?= $optotal["op_id"] ?></td>
                <td><?= $optotal["od_cliente"] ?></td>
                <td><?= $optotal["od_detalle"] ?></td>
                <td><?= $optotal["responsable_nombres"] . " " . $optotal["responsable_apellidos"] ?></td>
                <td><?= $optotal["comercial_nombres"] . " " . $optotal["comercial_apellidos"] ?></td>
                <td><?= $optotal["op_registro"] ?></td>
                <td><?= $optotal["op_direccionLocal"] ?></td>
                <td><?= $optotal["op_personaContacto"] ?></td>
                <td><?= $optotal["op_telefono"] ?></td>
                <td>
                    <?php
                    $reproseso = $optotal["op_reproceso"];
                    switch ($reproseso) {
                        case 0:
                            echo " NO ES UN REPROCESO";
                            break;
                        case 1:
                            echo "ES UN REPROCESO";
                            break;
                    }
                    ?>
                </td>
                <td><?= $optotal["op_estado"] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
    </thead>
</table>