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
$areaEditar = null;

if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1) {
    $produccionRegistros = $conn->query("SELECT * FROM produccion");
    $pro_areasAsociadas = $conn->query("SELECT * FROM pro_areas where pro_id = {$produccionRegistros['pro_id']}");
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["area"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } else {
            // Verificamos si ya existe un registro para el área actual
            $existingStatement = $conn->prepare("SELECT pro_id FROM pro_areas WHERE pro_id = :id");
            $existingStatement->execute([":id" => $id]);
            $existingArea = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingArea) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE pro_areas SET proAre_detalle = :area WHERE pro_id = :id");
                $statement->execute([
                    ":id" => $id,
                    ":area" => $_POST["area"],
                ]);

                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["cedula"], "EDITO", 'AREAS ASOCIODAS', $_POST["area"]);

            } else {
                // Si no existe, insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO pro_areas (proAre_detalle) VALUES (:area)");
        
                $statement->execute([
                    ":area" => $_POST["area"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], "ASIGNÓ.", 'AREAS ASOCIODAS', $_POST["area"]);
            }
        
            // Redirigimos a pro_areas.php
            header("Location: areas.php");
            return;
        }
    }

    // Llamamos las áreas de la base de datos
    $pro_areas = $conn->query("SELECT * FROM pro_areas");

    // Obtenemos la información del área a editar
    $statement = $conn->prepare("SELECT * FROM pro_areas WHERE pro_id = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $areaEditar = $statement->fetch(PDO::FETCH_ASSOC);

} else {
    header("Location: ./index.php");
    return;
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Registros de Producción</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID Plano</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Áreas Asociadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produccionRegistros as $registro): ?>
                            <tr>
                                <th scope="row"><?= $registro["pro_id"] ?></th>
                                <td><?= $registro["pla_id"] ?></td>
                                <td><?= $registro["pro_fecha"] ?></td>
                                <td>
                                    <?php foreach($pro_areasAsociadas as $area) : ?>  
                                        <?php 
                                            if ($area["proAre_detalle"] == 1  ) {
                                                echo("CARPINTERÍA");
                                            } elseif ($area["proAre_detalle"] == 2  ) {
                                                echo("ACM");
                                            } 
                                        ?>
                                        "",
                                                            "",
                                                            "PINTURA",
                                                            "ACRÍLICOS Y ACABADOS",
                                                            "MÁQUINAS",
                                                            "IMPRESIONES"
                                    <?php endforeach ?>    
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
