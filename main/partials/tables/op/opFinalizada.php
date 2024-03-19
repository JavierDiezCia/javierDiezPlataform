<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">OP FINALIZADAS</h5>
                        </div>
                    </div>
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>OP</th>
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
                            <?php foreach ($opfinalizada as $opfinalizada) : ?>
                                <tr>
                                    <td><?= $opfinalizada["op_id"] ?></td>
                                    <td><?= $opfinalizada["op_cliente"] ?></td>
                                    <td><?= $opfinalizada["op_detalle"] ?></td>
                                    <td><?= $opfinalizada["cedula_nombres"] . " " . $opfinalizada["cedula_apellidos"] ?></td>
                                    <td><?= $opfinalizada["vendedor_nombres"] . " " . $opfinalizada["vendedor_apellidos"] ?></td>
                                    <td><?= $opfinalizada["op_registro"] ?></td>
                                    <td><?= $opfinalizada["op_direccionLocal"] ?></td>
                                    <td><?= $opfinalizada["op_personaContacto"] ?></td>
                                    <td><?= $opfinalizada["op_telefono"] ?></td>
                                    <td>
                                        <?php
                                        $reproseso = $opfinalizada["op_reproceso"];
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
                                    <td><?= $opfinalizada["op_estado"] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>