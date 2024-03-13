<?php
require "../sql/database.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$personaEditar = null;

if (($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 1)) {
    // Llamar los contactos de la base de datos y especificar que sean los que tengan el persona_id de la función session_start
    $personas = $conn->query("SELECT * FROM personas WHERE per_estado = 0");

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
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">EMPLEADOS</h5>
                                <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($personas->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>NO HAY EMPLEADOS ELIMINADOS AÚN.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                    <tr>
                                        <th>APELLIDOS</th>
                                        <th>NOMBRES</th>
                                        <th>CÉDULA</th>
                                        <th>EDAD</th>
                                        <th>ÁREA DE TRABAJO</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($personas as $persona): ?>
                                        <tr>
                                        <th><?= $persona["per_apellidos"]?></th>
                                        <td><?= $persona["per_nombres"]?></td>
                                        <td><?= $persona["cedula"]?></td>
                                        <td>
                                            <?php
                                            // Calcular la edad a partir de la fecha de nacimiento
                                            $birthdate = new DateTime($persona["per_fechaNacimiento"]);
                                            $today = new DateTime();
                                            $age = $today->diff($birthdate)->y;
                                            echo $age;
                                            ?>
                                        </td>
                                        <td><?= $persona["per_areaTrabajo"]?></td>
                                        <td>
                                            <a href="./restaurar/personas.php?id=<?= $persona["cedula"] ?>" class="btn btn-danger mb-2">RESTAURAR</a>
                                        </td>
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
