
<table class="table datatable">
    <thead>
        <tr>
            <th>#OP</th>
            <th>CLIENTE</th>
            <th>DETALLE</th>
            <th>DISEÑADOR</th>
            <th>VENDEDOR</th>
            <th>FECHA REGISTRO</th>
            <th>DIRECCIÓN DEL LOCAL</th>
            <th>PERSONA DE CONTACTO</th>
            <th>TELÉFONO DE CONTACTO</th>
            <th>REPROCESO</th>
            <th>ESTADO</th>
        </tr>
    <tbody>
        <?php foreach ($opanulada as $opanulada) : ?>
            <tr>
                <td><?= $opanulada["op_id"] ?></td>
                <td><?= $opanulada["od_cliente"] ?></td>
                <td><?= $opanulada["od_detalle"] ?></td>
                <td><?= $opanulada["responsable_nombres"] . " " . $opanulada["responsable_apellidos"] ?></td>
                <td><?= $opanulada["comercial_nombres"] . " " . $opanulada["comercial_apellidos"] ?></td>
                <td><?= $opanulada["op_registro"] ?></td>
                <td><?= $opanulada["op_direccionLocal"] ?></td>
                <td><?= $opanulada["op_personaContacto"] ?></td>
                <td><?= $opanulada["op_telefono"] ?></td>
                <td>
                    <?php
                    $reproseso = $opanulada["op_reproceso"];
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
                <td><?= $opanulada["op_estado"] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
    </thead>
</table>