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
$idproduccion = isset($_GET["id"]) ? $_GET["id"] : null; // Cambié "idop" por "id"
$produccionInfo = null;

// Verificamos si se ha proporcionado un ID de producción válido
if ($idproduccion) {
    // Consultamos la información de producción
    $produccionInfoStatement = $conn->prepare("SELECT * FROM produccion WHERE pro_id = :idproduccion");
    $produccionInfoStatement->bindParam(":idproduccion", $idproduccion);
    $produccionInfoStatement->execute();
    $produccionInfo = $produccionInfoStatement->fetch(PDO::FETCH_ASSOC);
}

// Verificamos el método que usa el formulario con un if
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar que se han enviado los datos necesarios
    if (isset($_POST["idproduccion"]) && isset($_POST["idplano"]) && isset($_POST["proobservaciones"]) && isset($_POST["areatrabajo"])) {
        // Obtener datos del formulario
        $idproduccion = $_POST["idproduccion"];
        $idplano = $_POST["idplano"];
        $proobservaciones = $_POST["proobservaciones"];
        $areatrabajo = $_POST["areatrabajo"];

        // Actualizar datos de producción en la tabla produccion
        $updateStatement = $conn->prepare("UPDATE produccion SET pla_id = :idplano WHERE pro_id = :idproduccion");
        $updateStatement->execute([
            ":idplano" => $idplano,
            ":idproduccion" => $idproduccion
        ]);

        // Eliminar las áreas asociadas actuales
        $deleteAreasStatement = $conn->prepare("DELETE FROM pro_areas WHERE pro_id = :idproduccion");
        $deleteAreasStatement->bindParam(":idproduccion", $idproduccion);
        $deleteAreasStatement->execute();

        // Insertar las nuevas áreas asociadas
        foreach ($areatrabajo as $area) {
            $insertAreaStatement = $conn->prepare("INSERT INTO pro_areas (pro_id, proAre_detalle) VALUES (:idproduccion, :areadetalle)");
            $insertAreaStatement->execute([
                ":idproduccion" => $idproduccion,
                ":areadetalle" => $area
            ]);
        }

        // Registramos el movimiento en el kardex
        registrarEnKardex($_SESSION["user"]["cedula"], "EDITÓ", 'PRODUCCIÓN', $idproduccion);

        // Redirigir a alguna página de éxito o a donde desees
        header("Location: produccion.php");
        exit(); // Detener la ejecución del script
    } else {
        $error = "POR FAVOR, COMPLETE TODOS LOS CAMPOS REQUERIDOS.";
    }
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">EDITAR REGISTRO DE PRODUCCIÓN</h5>

                    <!-- si hay un error, mostrar mensaje de error -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>

                    <!-- Formulario para editar registro de producción -->
                    <form class="row g-3" method="POST" action="produccionEdit.php">
                        <input type="hidden" name="idproduccion" value="<?= $produccionInfo["pro_id"] ?>">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="idplano" name="idplano" placeholder="ID Plano" value="<?= $produccionInfo["pla_id"] ?>" autocomplete="off">
                                <label for="idplano">NÚMERO DE PLANO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="proobservaciones" name="proobservaciones" placeholder="Observaciones" value="<?= $produccionInfo["PROOBSERVACIONES"] ?>" autocomplete="off">
                                <label for="proobservaciones">OBSERVACIONES</label>
                            </div>
                        </div>

                        <h5 class="card-title">VINCULAR ÁREAS</h5>

                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <?php
                                // Definir las áreas de trabajo
                                $areas = array(
                                    "CARPINTERÍA",
                                    "ACM",
                                    "PINTURA",
                                    "ACÍLICOS Y ACABADOS",
                                    "MÁQUINAS",
                                    "METAL MECANICA"
                                );
                                // Consultamos las áreas asociadas a la producción actual
                                $areasAsociadas = [];
                                if ($produccionInfo) {
                                    // Consultamos las áreas asociadas a la producción actual
                                    $areasAsociadasStatement = $conn->prepare("SELECT proAre_detalle FROM pro_areas WHERE pro_id = :idproduccion");
                                    $areasAsociadasStatement->execute([":idproduccion" => $produccionInfo["pro_id"]]);
                                    $areasAsociadasResult = $areasAsociadasStatement->fetchAll(PDO::FETCH_COLUMN);
                                    // Almacenamos las áreas asociadas en el array
                                    $areasAsociadas = array_map('intval', $areasAsociadasResult);
                                }

                                // Ahora, cuando imprimimos los checkboxes, verificamos si el área está asociada y marcamos el checkbox correspondiente
                                foreach ($areas as $index => $area) {
                                    if ($area != "DISEÑO") {
                                        echo "<div class='form-check'>";
                                        $checked = in_array($index + 1, $areasAsociadas) ? "checked" : ""; // Verificar si el área está asociada
                                        echo "<input class='form-check-input' type='checkbox' name='areatrabajo[]' value='" . ($index + 1) . "' id='areatrabajo" . ($index + 1) . "' $checked>";
                                        echo "<label class='form-check-label' for='areatrabajo" . ($index + 1) . "'>" . $area . "</label>";
                                        echo "</div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                            <button type="reset" class="btn btn-secondary">LIMPIAR CAMPOS</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
