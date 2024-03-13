<?php 
require "../sql/database.php";
require "./partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//validacion para el usuario tipo diseniador 
if ($_SESSION["user"]["usu_rol"] == 3||$_SESSION["user"]["usu_rol"] == 1) {
    // Obtener el diseñador de la sesión activa
    $diseniador = $_SESSION["user"]["cedula"];

    // Buscar od_productos existentes
    $od_productosQuery = $conn->prepare("SELECT od_detalle, od_cliente, od_id FROM orden_disenio WHERE od_estado = 'PROPUESTA'");
    $od_productosQuery->execute();
    $od_productos = $od_productosQuery->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si ya hay un registro activo para el diseñador actual
    $registroQuery = $conn->prepare("SELECT * FROM registros_disenio WHERE rd_diseniador = :diseniador AND rd_hora_fin IS NULL LIMIT 1");
    $registroQuery->execute(array(':diseniador' => $diseniador));

    if ($registroQuery->rowCount() > 0) {
        header("Location: registroOdFinal.php");
        return;
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que no se manden datos vacíos
            if (empty($_POST["od_detalle"]) && empty($_POST["odAct_detalle"])) {
                $error = "POR FAVOR SELECCIONA UN PRODUCTO.";
            } else {
                $od_id = $_POST['od_id'];
    
                // Insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO registros_disenio (od_id, rd_diseniador, rd_detalle, rd_hora_ini, rd_hora_fin) 
                                            VALUES (:od_id, :diseniador, :rd_detalle, CURRENT_TIMESTAMP, NULL)");
    
                $statement->execute([
                    ":od_id" => $od_id,
                    ":diseniador" => $diseniador,
                    ":rd_detalle" => $_POST['od_actividades']
                ]);
    
                // Redirigimos a la página principal o a donde desees
                header("Location: registroOd.php");
                return;
            }
        }
    }
} else {
    // Redirigimos a la página principal o a donde desees
    header("Location: pages-error-404.html");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;


?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">NUEVO REGISTRO DE DISEÑO</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="registroOd.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="od_detalle" name="od_detalle" required>
                                    <option selected disabled value="">SELECCIONA EL PRODUCTO</option>
                                    <?php $productCounter = 1; ?>
                                    <?php foreach ($od_productos as $od_detalle): ?>
                                        <option value="<?= $od_detalle["od_detalle"] ?>" data-od_cliente="<?= $od_detalle["od_cliente"] ?>" data-od_id="<?= $od_detalle["od_id"] ?>">
                                            <?= $productCounter++ ?>. <?= $od_detalle["od_detalle"] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                                <label for="od_detalle">PRODUCTO</label>
                            </div>
                        </div>
                        <input type="hidden" id="od_id" name="od_id">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="od_cliente" name="od_cliente" placeholder="od_cliente" required readonly></input>
                                <label for="od_cliente">CLIENTE</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="od_actividades" name="od_actividades" required>
                                    <option selected disabled value="">SELECCIONA LA ACTIVIDAD</option>
                                    <?php $activityCounter = 1; ?>
                                    <!-- Las opciones se cargarán dinámicamente mediante JavaScript -->
                                </select>
                                <label for="od_actividades">ACTIVIDAD</label>
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

<script>
    document.getElementById('od_detalle').addEventListener('change', function() {
        var od_id = this.options[this.selectedIndex].getAttribute('data-od_id'); // Obtén el valor de od_id
        
        // Realiza una petición AJAX para obtener las actividades basadas en el od_id seleccionado
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Ajax.php'); // Ruta al archivo PHP que maneja la solicitud AJAX
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var actividades = JSON.parse(xhr.responseText); // Parsea la respuesta JSON
                // Elimina todos los elementos de opción actuales del select de actividades
                var selectActividades = document.getElementById('od_actividades');
                selectActividades.innerHTML = ''; // Limpia el select
                // Crea opciones para cada actividad devuelta por la consulta AJAX
                actividades.forEach(function(actividad) {
                    var option = document.createElement('option');
                    option.value = actividad.odAct_detalle;
                    var counter = 1;
                    actividades.forEach(function(actividad) {
                        var option = document.createElement('option');
                        option.value = actividad.odAct_detalle;
                        option.text = counter + '. ' + actividad.odAct_detalle + ' ' + actividad.odAct_fechaEntrega;
                        selectActividades.appendChild(option);
                        counter++;
                    });
                    selectActividades.appendChild(option);
                });
            } else {
                console.error('Error en la petición AJAX');
            }
        };
        // Envía el od_id seleccionado al servidor
        xhr.send('od_id=' + od_id);
    });

</script>

<script>
    document.getElementById('od_detalle').addEventListener('change', function() {
        var od_id = this.options[this.selectedIndex].getAttribute('data-od_id');
        var od_cliente = this.options[this.selectedIndex].getAttribute('data-od_cliente');
        document.getElementById('od_cliente').value = od_cliente;
        document.getElementById('od_id').value = od_id; // Agregar esta línea para establecer el valor de od_id en un campo oculto
    });
</script>