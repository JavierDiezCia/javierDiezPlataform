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
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$ciudadEditar = null;

if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["ciudad"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } else {
            // Verificamos si ya existe un registro para la ciudad actual
            $existingStatement = $conn->prepare("SELECT lu_id FROM ciudad_produccion WHERE lu_id = :id");
            $existingStatement->execute([":id" => $id]);
            $existingCiudad = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingCiudad) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE ciudad_produccion SET lu_ciudad = :ciudad WHERE lu_id = :id");
                $statement->execute([
                    ":id" => $id,
                    ":ciudad" => $_POST["ciudad"],
                ]);

                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["cedula"], "EDITÓ", 'CIUDAD DE PRODUCCIÓN', $_POST["ciudad"]);


            } else {
                // Si no existe, insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO ciudad_produccion (lu_ciudad) 
                                              VALUES (:ciudad)");
        
                $statement->execute([
                    ":ciudad" => $_POST["ciudad"],
                ]);

                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["cedula"], "CREÓ", 'CIUDADES DE PRODUCCIÓN', $_POST["ciudad"]);
            }
        
            // Redirigimos a ciudades.php
            header("Location: ciudades.php");
            return;
        }
    }

    // Llamamos los lugares de producción de la base de datos
    $ciudades = $conn->query("SELECT * FROM ciudad_produccion");

    // Obtenemos la información de la ciudad a editar
    $statement = $conn->prepare("SELECT * FROM ciudad_produccion WHERE lu_id = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $ciudadEditar = $statement->fetch(PDO::FETCH_ASSOC);

} else {
    header("Location: ./index.php");
    return;
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <?php if (empty($id)) : ?>
                <!-- Código para agregar una nueva ciudad -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">NUEVO LUGAR DE PRODUCCIÓN</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="ciudades.php">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" autocomplete="ciudad" required>
                                    <label for="ciudad">CIUDAD</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">GUARDAR</button>
                                <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Código para editar una ciudad existente -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">EDITAR CIUDAD DE PRODUCCIÓN</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="ciudades.php?id=<?= $id ?>">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" value="<?= $ciudadEditar["lu_ciudad"] ?>">
                                    <label for="ciudad">CIUDAD</label>
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
                                <h5 class="card-title">CIUDADES DE PRODUCCIÓN</h5>
                                <!-- si el array asociativo $ciudades no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($ciudades->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>NO HAY CIUDADES DE PRODUCCIÓN AÚN.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>CIUDAD</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ciudades as $ciudad): ?>
                                                <tr>
                                                    <th><?= $ciudad["lu_ciudad"]?></th>
                                                    <td>
                                                        <a href="ciudades.php?id=<?= $ciudad["lu_id"] ?>" class="btn btn-secondary mb-2">EDITAR</a>
                                                    </td>
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