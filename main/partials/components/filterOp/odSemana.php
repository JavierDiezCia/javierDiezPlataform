<?php

if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
} 

$opSemana = $conn->query("SELECT op.*, 
                  od.od_comercial AS vendedor, 
                  od.od_detalle AS detalle,
                  od.od_cliente AS cliente,
                  cedula.per_nombres AS cedula_nombres, 
                  cedula.per_apellidos AS cedula_apellidos,
                  COUNT(planos.pla_id) AS numero_planos
                  FROM op
                  LEFT JOIN orden_disenio AS od ON op.od_id = od.od_id
                  LEFT JOIN personas AS cedula ON od.od_responsable = cedula.cedula
                  LEFT JOIN personas AS op_vendedor ON od.od_comercial = op_vendedor.cedula
                  LEFT JOIN planos ON op.op_id = planos.op_id
                  WHERE WEEK(op.op_registro) = WEEK(CURDATE())
                  GROUP BY op.op_id 
                  ORDER BY op.op_id DESC;
                  "
);

?>


<table class="table table-borderless datatable">
    <thead>
        <tr>
        <th scope="col">#OP</th>
        <th scope="col">CLIENTE</th>
        <th scope="col">DETALLE</th>
        <th scope="col">PLANOS</th>
        <th scope="col">ESTADO</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($opSemana as $op) : ?>
        <tr>
            <th scope="row"><a href="#"><?= $op["op_id"] ?> </a></th>
            <td><?= $op["cliente"] ?></td>
            <td><a href="#" class="text-primary"><?= $op["detalle"] ?></a></td>
            <td><?= $op["numero_planos"] ?></td>
            <td><?= $op["op_estado"] ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
    </table>