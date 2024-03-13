<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$idop = null; 
$opInfo = null;
$opPlanos = null;

if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["idop"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } else {
            // Obtener la información de la op y sus planos
            $opInfoStatement = $conn->prepare("SELECT * FROM op WHERE op_id = :idop AND op_estado == 'OP CREADA' OR op_estado == 'EN PRODUCCION' ");
            $opInfoStatement->bindParam(":idop", $_POST["idop"]);
            $opInfoStatement->execute();
            $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);

            // Obtener los planos asociados a la op
            $opPlanosStatement = $conn->prepare("SELECT * FROM planos WHERE op_id = :idop");
            $opPlanosStatement->bindParam(":idop", $_POST["idop"]);
            $opPlanosStatement->execute();
            $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <!-- Código para buscar op por op_id -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">BUSCAR POR NÚMERO DE OP</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="planosAddtest.php" onsubmit="updateAction()">
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="idop" name="idop" placeholder="op_id" autocomplete="idop" required>
                                <label for="idop">NÚMERO DE OP</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">BUSCAR</button>
                            <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mostrar información de la op y sus planos -->
            <?php if ($opInfo): ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">DATOS DE LA OP</h5>
                                    <p>NÚMERO DE OP: <?= $opInfo["op_id"] ?></p>
                                    <p>CLIENTE: <?= $opInfo["OPCLIENTE"] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if ($opPlanos): ?>
                    <section class="section">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">PLANOS DE LA OP</h5>
                                        <!-- si el array asociativo $opPlanos no tiene nada dentro, entonces imprimir el siguiente div -->
                                        <?php if (empty($opPlanos)): ?>
                                            <div class="col-md-4 mx-auto mb-3">
                                                <div class="card card-body text-center">
                                                    <p>BUSQUE UNA OP</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Table with stripped rows -->
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>NÚMERO DE PLANO</th>
                                                        <th>ESTADO</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($opPlanos as $opPlano): ?>
                                                        <tr>
                                                            <td><?= $opPlano["pla_numero"] ?></td>
                                                            <td><?= $opPlano["pla_estado"] ?></td>
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
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</section>
<script>
    function updateAction() {
        // Obtener el valor del campo idop
        var idopValue = document.getElementById("idop").value;
        // Actualizar la acción del formulario agregando el valor de idop
        document.querySelector("form").action = "planosAddtest.php?id=" + idopValue;
    }
</script>
<?php require "./partials/footer.php"; ?>
