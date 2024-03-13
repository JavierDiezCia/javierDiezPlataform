<?php
require  "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 


//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//declaramos la variable error que nos ayudara a mostrar errores, etc.
$error = null;
$reproseso = "0";
$state = "OP CREADA";
//$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$opEditar = null;
if (($_SESSION["user"]["usu_rol"]) || ($_SESSION["user"]["usu_rol"] == 1) || ($_SESSION["user"]["usu_rol"] == 2) || ($_SESSION["user"]["usu_rol"] == 3)) {
    //llamr las op de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op = $conn->query("SELECT op.*, 
        orden.od_responsable,
        responsable.per_nombres AS responsable_nombres,
        responsable.per_apellidos AS responsable_apellidos,
        orden.od_comercial,
        comercial.per_nombres AS comercial_nombres,
        comercial.per_apellidos AS comercial_apellidos,
        orden.od_detalle,
        orden.od_cliente,
        COUNT(planos.pla_id) AS total_planos
    FROM op
    LEFT JOIN orden_disenio AS orden ON op.od_id = orden.od_id
    LEFT JOIN personas AS responsable ON orden.od_responsable = responsable.cedula
    LEFT JOIN personas AS comercial ON orden.od_comercial = comercial.cedula
    LEFT JOIN planos ON op.op_id = planos.op_id
    WHERE op.op_estado = 'OP CREADA'
    GROUP BY op.op_id");

    


    // Buscar od_productos existentes
    $od_productosQuery = $conn->prepare("SELECT od_detalle, od_cliente FROM orden_disenio WHERE od_estado = 'OP'");
    $od_productosQuery->execute();
    $od_productos = $od_productosQuery->fetchAll(PDO::FETCH_ASSOC);

    // Obtener opciones para ciudad de produccion desde la base de datos
    $lugarproduccion = $conn->query("SELECT * FROM ciudad_produccion");

    $personas = $conn->query("SELECT*FROM personas");
    // Calculamos el número total de planos asociados a la op actual
    $planoCountStatement = $conn->prepare("SELECT COUNT(*) AS total_planos FROM planos WHERE op_id = :id");
    $planoCountStatement->execute([":id" => $id]);
    $planoCountResult = $planoCountStatement->fetch(PDO::FETCH_ASSOC);
    $totalPlanos = $planoCountResult['total_planos'];
    //VERFIFICAMOS EL METODOD QUE SE USA EL FORM CON UN IF 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {


        //VALIDFAMOS QUE NO SE MANDEN DATOS VASIOS
        if (empty($_POST["ciudad"]) || empty($_POST["direccion"]) || empty($_POST["contacto"]) || empty($_POST["telefono"])) {
            $error = "POR FAVOR LLENAR TODOS LOS CAMPOS.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["ciudad"])) {
            $error = "NOMBRE DE LA CIUDAD INVÁLIDA.";
        } elseif (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ.,\s\-]+$/', $_POST["direccion"])) {
            $error = "DIRECCIÓN INVÁLIDA.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["contacto"])) {
            $error = "CONTACTO INVÁLIDO.";
        } elseif (!preg_match('/^[0-9]{10}$/', $_POST["telefono"])) {
            $error = "EL TELÉFONO DEBE TENER 10 NÚMEROS";
        } else {
            //VERIFICAMOS SI YA EXISTE UN REGISTRO PARA  op ACTUAL
            $existingStament = $conn->prepare("SELECT * FROM op  WHERE op_id=:id");
            $existingStament->execute([":id" => $id]);
            $existingDiseniador = $existingStament->fetch(PDO::FETCH_ASSOC);

            if ($existingDiseniador) {
                // Verifica que el usuario tenga el rol necesario para actualizar
                if ($_SESSION["user"]["usu_rol"] == 1) {
                    // Actualiza la op
                    $stament = $conn->prepare("UPDATE op SET
                        op_ciudad=:ciudad,
                        op_direccionLocal=:dirrecion,
                        op_personaContacto=:contacto,
                        op_telefono=:telefono,
                        WHERE op_id=:id");
                    $stament->execute([
                        ":ciudad" => $_POST["ciudad"],
                        ":dirrecion" => strtoupper($_POST["direccion"]),
                        ":contacto" => strtoupper($_POST["contacto"]),
                        ":telefono" => $_POST["telefono"],
                        ":id" => $id
                    ]);
                    // Registra el movimiento en el kardex
                    registrarEnKardex($_SESSION["user"]["cedula"], "EDITÓ", 'OP', $id);
                } else {
                    // Usuario no autorizado para actualizar
                    $error = "NO TIENES PERMISOS PARA EDITAR ESTA OP.";
                }
            } else {
                // Obtener el od_id correspondiente al od_detalle seleccionado
                $od_detalle = $_POST["od_detalle"];
                $od_id_query = $conn->prepare("SELECT od_id FROM orden_disenio WHERE od_detalle = :od_detalle AND od_estado = 'OP'");
                $od_id_query->bindParam(":od_detalle", $od_detalle);
                $od_id_query->execute();
                $od_id_result = $od_id_query->fetch(PDO::FETCH_ASSOC);
                $od_id = $od_id_result['od_id'];

                //SINO AY UN REGISTRO ACTUALIZARME
                $stament = $conn->prepare("INSERT INTO op (od_id, lu_id, op_ciudad, op_direccionLocal, op_personaContacto, op_telefono, op_estado, op_reproceso)
                VALUES (:od_id, :lu_id, :ciudad, :direccion, :contacto, :telefono, :estado, :reproseso)");

                $stament->execute([
                    ":od_id" => $od_id,
                    ":lu_id" => $_POST["lu_idproduccion"],
                    ":ciudad" => strtoupper($_POST["ciudad"]),
                    ":direccion" => strtoupper($_POST["direccion"]),
                    ":contacto" => strtoupper($_POST["contacto"]),
                    ":telefono" => $_POST["telefono"],
                    ":estado" => $state,
                    ":reproseso" => $reproseso
                ]);

                // Registramos el movimiento en el kardex
                // Obtenemos el último op_id insertado o actualizado
                $lastInsertId = $conn->lastInsertId();
                registrarEnKardex($_SESSION["user"]["cedula"], "CREÓ", 'OP', $lastInsertId);

                // Obtenemos la cantidad de planos ingresados
                $cantidadPlanos = isset($_POST["planos"]) ? intval($_POST["planos"]) : 0;

                // Verificamos si la cantidad de planos es válida (mayor que cero)
                if ($cantidadPlanos > 0) {


                    // Iteramos sobre la cantidad de planos e insertamos un registro en la tabla planos por cada uno
                    for ($i = 1; $i <= $cantidadPlanos; $i++) {
                        $planoNumero = $i;

                        // Insertamos el registro en la tabla planos
                        $stmt = $conn->prepare("INSERT INTO planos (op_id, pla_numero, pla_estado, pla_reproceso, pla_porcentaje) VALUES (:idop, :plannumero, 'ACTIVO', 0, 0)");
                        $stmt->execute([
                            ":idop" => $lastInsertId,
                            ":plannumero" => $planoNumero
                        ]);
                    }
                }

                $estadoOd = "OP CREADA";

                //SINO AY UN REGISTRO ACTUALIZARME
                $stament = $conn->prepare("UPDATE orden_disenio SET od_estado = :estado WHERE od_id = $od_id");

                $stament->execute([
                    ":estado" => $estadoOd
                ]);
            }
            //REDIRIGIREMOS AHOME.PHP
            header("Location: op.php");
            return;
        }
    }
} else {
    header("Location:./index.php");
    return;
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <?php if (empty($id)) : ?>
                <div class="card accordion" id="accordionExample">
                    <div class="card-body accordion-item">
                        <h5 class="card-title accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                NUEVA OP
                            </button>
                        </h5>

                        <?php if ($error) : ?>
                            <p class="text_danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="row g-3" method="POST" action="op.php">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="od_detalle" name="od_detalle" required>
                                            <option selected disabled value="">SELECCIONA EL PRODUCTO</option>
                                            <?php foreach ($od_productos as $od_detalle): ?>
                                                <option value="<?= $od_detalle["od_detalle"] ?>" data-od_cliente="<?= $od_detalle["od_cliente"] ?>"><?= $od_detalle["od_detalle"] ?></option>
                                            <?php endforeach ?>
                                        </select>
                                        <label for="od_detalle">PRODUCTO</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="od_cliente" name="od_cliente" placeholder="od_cliente" required readonly></input>
                                        <label for="od_cliente">CLIENTE</label>
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="planos" name="planos" placeholder="">
                                            <label for="planos"> PLANOS</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lu_idproduccion" class="form-label">LUGAR DE PRODUCCIÓN</label>
                                        <select class="form-select" id="lu_idproduccion" name="lu_idproduccion">
                                            <?php foreach ($lugarproduccion as $lugar) : ?>
                                                <option value="<?= $lugar["lu_id"] ?>"><?= $lugar["lu_ciudad"] ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" autocomplete="ciudad" required>
                                            <label for="ciudad">CIUDAD DE ENTREGA</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion" autocomplete="direccion" required>
                                            <label for="direccion">DIRECCIÓN LOCAL</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="contacto" name="contacto" placeholder="Contacto" autocomplete="contacto" required>
                                            <label for="contacto">PERSONA DE CONTACTO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="telefono" name="telefono" placeholder="Telefono" autocomplete="telefono" required>
                                            <label for="telefono">TELÉFONO</label>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">GUARDAR</button>
                                        <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <?php
                $statement = $conn->prepare("SELECT O.*, P.* FROM op O INNER JOIN personas P ON O.cedula = P.cedula WHERE O.op_id = :id");
                $statement->bindParam(":id", $id);
                $statement->execute();
                $opEditar = $statement->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">EDITAR OP</h5>

                        <?php if ($error) : ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>

                        <form class="row g-3" method="POST" action="op.php">
                            <div class="col-md-6">
                                <label for="lu_idproduccion" class="form-label">CIUDAD DE PRODUCCIÓN</label>
                                <select class="form-select" id="lu_idproduccion" name="lu_idproduccion">
                                    <?php foreach ($lugarproduccion as $lugar) : ?>
                                        <option value="<?= $lugar["lu_id"] ?>" <?= $opEditar["lu_id"] == $lugar["lu_id"] ? "selected" : "" ?>>
                                            <?= $lugar["lu_ciudad"] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["op_ciudad"] ?>" type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                    <label for="ciudad">CUIDAD</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["op_direccionLocal"] ?>" type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion">
                                    <label for="direccion">DIRECCIÓN DEL LOCAL</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["op_personaContacto"] ?>" type="text" class="form-control" id="contacto" name="contacto" placeholder="Contacto">
                                    <label for="contacto">PERSONA DE CONTACTO</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["op_telefono"] ?>" type="number" class="form-control" id="telefono" name="telefono" placeholder="Telefono">
                                    <label for="telefono">TELÉFONO</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                                <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif ?>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-header">
                                    <h5 class="card-tittle">ÓRDENES DE PRODUCCIÓN SIN NOTIFICAR A PRODUCCIÓN</h5>
                                </div>
                                <h5 class="col-md-4 mx-auto mb-3"></h5>

                                <?php if ($op->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>NO HAY ÓRDENES DE PRODUCCIÓN AÚN.</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>OP</th>
                                                <th>DISEÑADOR</th>
                                                <th>PLANOS</th>
                                                <th>CLIENTE</th>
                                                <th>DETALLE</th>
                                                <th>REGISTRO</th>
                                                <th>NOTIFICACIÓN POR CORREO A PRODUCCIÓN</th>
                                                <th>COMERCIAL</th>
                                                <th>DIRECCIÓN DEL LOCAL</th>
                                                <th>PERSONA DE CONTACTO</th>
                                                <th>TELÉFONO</th>
                                                <th>OBSERVACIONES</th>
                                                <th>ESTADO</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($op as $op) : ?>
                                                
                                                <tr>
                                                    <th><?= $op["op_id"] ?></th>
                                                    <td><?= $op["responsable_nombres"] . " " . $op["responsable_apellidos"] ?></td>
                                                    <th><?= $op["total_planos"] ?></th>
                                                    <td><?= $op["od_cliente"] ?></td>
                                                    <td><?= $op["od_detalle"] ?></td>
                                                    <td><?= $op["op_registro"] ?></td>
                                                    <td>
                                                        <?php if ($op["total_planos"] != 0) : ?>
                                                            <a href="./validaciones/notiOp.php?id=<?= $op["op_id"] ?>" class="btn btn-primary mb-2">NOTIFICAR</a>
                                                        <?php else : ?>
                                                            <a href="planosAddtest.php?id=<?= $op["op_id"] ?>" class="btn btn-secondary mb-2">INGRESAR PLANOS</a>
                                                        <?php endif ?>
                                                    </td>
                                                    <td><?= $op["comercial_nombres"] . " " . $op["comercial_apellidos"] ?></td>
                                                    <td><?= $op["op_direccionLocal"] ?></td>
                                                    <td><?= $op["op_personaContacto"] ?></td>
                                                    <td><?= $op["op_telefono"] ?></td>
                                                    <td></td>
                                                    <td><?= $op["op_estado"] ?></td>
                                                    <?php if ($_SESSION["user"]["usu_rol"] == 1) : ?>
                                                        <td>
                                                            <a href="op.php?id=<?= $op["op_id"] ?>" class="btn btn-secondary mb-2">EDITAR</a>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td></td>
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

<script>
    document.getElementById('od_detalle').addEventListener('change', function() {
        var od_detalle = this.value;
        var od_cliente = this.options[this.selectedIndex].getAttribute('data-od_cliente');
        var compania = this.options[this.selectedIndex].getAttribute('data-compania');
        
        document.getElementById('od_cliente').value = od_cliente;
        document.getElementById('compania').value = compania;
    });
</script>