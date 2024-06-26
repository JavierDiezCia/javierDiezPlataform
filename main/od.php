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
$id = $_GET["id"] ?? null;
$ordenEditar = null;
$state = "PROPUESTA";
$elementos = $_SESSION["elementos"] ?? [null];

// Obtener el diseñador de la sesión activa
$diseniador = $_SESSION["user"]["cedula"];

// Obtener el número total de actividades para la orden de diseño actual
$totalActividades = $conn->prepare("SELECT COUNT(*) FROM od_actividades WHERE od_id = :id AND odAct_estado = 0");
$totalActividades->execute([":id" => $id]);
$totalActividades = $totalActividades->fetchColumn();

// Obtener el número de registros en registros_disenio para la orden de diseño actual
$registrosDisenio = $conn->prepare("SELECT COUNT(*) FROM registros_disenio WHERE od_id = :id AND rd_hora_fin IS NOT NULL");
$registrosDisenio->execute([":id" => $id]);
$registrosDisenio = $registrosDisenio->fetchColumn();



if ($_SESSION["user"]["usu_rol"] && ($_SESSION["user"]["usu_rol"] == 2 || $_SESSION["user"]["usu_rol"] == 3 || $_SESSION["user"]["usu_rol"] == 1)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["detalle"]) || empty($_POST["cliente"]) || empty($_POST["cedula"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } else {
            if ($id) {
                $statement = $conn->prepare("UPDATE orden_disenio SET od_detalle = :detalle, od_cliente = :cliente WHERE od_id = :id");
                $statement->execute([
                    ":detalle" => strtoupper($_POST["detalle"]),
                    ":cliente" => strtoupper($_POST["cliente"]),
                    ":id" => $id
                ]);

                //registrar la notificacion
                $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_destinatario, noti_detalle, noti_fecha) VALUES (:cedula, :destinatario, :detalle, :fecha)")->execute([
                    ":cedula" => $_SESSION["user"]["cedula"],
                    ":destinatario" => 2,
                    ":detalle" => "La orden de diseño " . "#" . $id . " " . $_POST["detalle"] . " ha sido editada.",
                    ":fecha" => date("Y-m-d H:i:s"),
                ]);

                registrarEnKardex($_SESSION["user"]["cedula"], "EDITÓ", 'ÓRDENES DE DISEÑO', $_POST["detalle"]);

                header("Location: od.php");
                exit;
            } else {
                $statement = $conn->prepare("INSERT INTO orden_disenio (od_responsable, od_comercial, od_detalle, od_cliente, od_estado) 
                VALUES (:responsable, :comercial, :detalle, :cliente, :estado)");

                $statement->execute([
                    ":responsable" => $_SESSION["user"]["cedula"],
                    ":comercial" => $_POST["cedula"],
                    ":detalle" => strtoupper($_POST["detalle"]),
                    ":cliente" => strtoupper($_POST["cliente"]),
                    ":estado" => $state
                ]);

                $nuevaOrdenId = $conn->lastInsertId();
            

                registrarEnKardex($_SESSION["user"]["cedula"], "CREÓ", 'ÓRDENES DE DISEÑO', $_POST["detalle"]);
                
                $notiDetalle  = "Nueva orden de diseño creada por " . $_SESSION["user"]["cedula"] . " para " . $_POST["cliente"] . " con el detalle " . $_POST["detalle"] . ".";

                //notificaciones
                $notificacion = $conn->prepare("INSERT INTO notificaciones (noti_cedula, noti_fecha, noti_detalle, noti_destinatario) VALUES (:cedula, :fecha, :detalle, :destinatario)");
                $notificacion->execute([
                    ":cedula" => $_SESSION["user"]["cedula"],
                    ":fecha" => date("Y-m-d H:i:s"),
                    ":detalle" => $notiDetalle,
                    ":destinatario" => 2
                ]);

                // crear un row en la tabla noti_visualizaciones por cada usuario que tenga el rol 2
                $notiId = $conn->lastInsertId();
                $usuarios = $conn->prepare("SELECT P.cedula FROM personas P
                                            JOIN usuarios U ON P.cedula = U.cedula
                                            WHERE usu_rol = 2");
                $usuarios->execute();
                $usuarios = $usuarios->fetchAll(PDO::FETCH_ASSOC);
                foreach ($usuarios as $usuario) {
                    $notiVisualizacion = $conn->prepare("INSERT INTO noti_visualizaciones (noti_id, notiVis_cedula) VALUES (:noti_id, :cedula)");
                    $notiVisualizacion->execute([
                        ":noti_id" => $notiId,
                        ":cedula" => $usuario["cedula"]
                    ]);
                }

                header("Location: od_actividades.php?id=$nuevaOrdenId");
                exit;
            }
        }
    }

    $ordenes = $conn->prepare("SELECT od.*, 
        personas_responsable.per_nombres AS responsable_nombres, 
        personas_responsable.per_apellidos AS responsable_apellidos,
        personas_comercial.per_nombres AS comercial_nombres,
        personas_comercial.per_apellidos AS comercial_apellidos
        FROM orden_disenio od
        JOIN personas personas_responsable ON od.od_responsable = personas_responsable.cedula
        JOIN personas personas_comercial ON od.od_comercial = personas_comercial.cedula
        WHERE od.od_responsable = :diseniador AND od.od_estado = 'PROPUESTA'
        ORDER BY od.od_id DESC");
    $ordenes->bindParam(":diseniador", $diseniador);
    $ordenes->execute();

    if (!empty($id)) {
        $statement = $conn->prepare("SELECT * FROM orden_disenio WHERE od_id = :id");
        $statement->bindParam(":id", $id);
        $statement->execute();
        $ordenEditar = $statement->fetch(PDO::FETCH_ASSOC);
    }
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
                <!-- Código para agregar una nueva orden de diseño -->
                <div class="card accordion" id="accordionExample">
                    <div class="card-body accordion-item">
                        <h5 class="card-title accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                NUEVA ORDEN DE DISEÑO
                            </button>
                        </h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="row g-3" method="POST" action="od.php">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nombres" name="vendedor" placeholder="Buscar por nombre" list="nombresList" oninput="buscarPorNombres()" autocomplete="vendedor" required>
                                            <label for="vendedor">INGRESAR AMBOS NOMBRES DEL COMERCIAL</label>
                                            <datalist id="nombresList">
                                                <?php foreach ($personas as $persona) : ?>
                                                    <option value="<?= $persona["per_nombres"] ?>">
                                                <?php endforeach ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cedula" readonly required>
                                            <label for="cedula">CÉDULA DEL COMERCIAL</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="detalle" name="detalle" placeholder="Producto" autocomplete="detalle" required>
                                            <label for="detalle">PRODUCTO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Marca" autocomplete="cliente" required>
                                            <label for="cliente">CLIENTE</label>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" name="od" class="btn btn-primary">GUARDAR</button>
                                        <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- Código para editar una orden de diseño existente -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">EDITAR ORDEN DE DISEÑO</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="od.php?id=<?= $id ?>">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="detalle" name="detalle" placeholder="Producto" autocomplete="detalle" value="<?= $ordenEditar["od_detalle"] ?>">
                                    <label for="detalle">DETALLE</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Marca" autocomplete="cliente" value="<?= $ordenEditar["od_cliente"] ?>" readonly>
                                    <label for="cliente">CLIENTE</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="comercial" name="cedula" placeholder="comercial" autocomplete="comercial" value="<?= $ordenEditar["od_comercial"] ?>" readonly>
                                    <label for="comercial">CÉDULA DEL COMERCIAL</label>
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
                                <h5 class="card-title">ÓRDENES DE DISEÑO</h5>
                                <!-- si el array asociativo $ordenes no tiene nada dentro, entonces imprimir el siguiente div -->
                                
                                    <div class="card-header">
                                        <ul  class="nav nav-tabs" id="myTabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="tab1" data-toggle="tab" href="#content1" role="tab" aria-controls="content1" aria-selected="true">MIS OD EN PROPUESTA</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab2" data-toggle="tab" href="#content2" role="tab" aria-controls="content2" aria-selected="false">HISTORIAL DE MIS OD</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="content1" role="tabpanel" arial-labelledby="tab1">
                                            <?php require "./partials/tables/od/diseniador/odPropuesta.php"; ?>
                                            <?php if ($ordenes->rowCount() == 0): ?>
                                                <div class= "col-md-4 mx-auto mb-3">
                                                    <div class= "card card-body text-center">
                                                        <p>NO HAY ÓRDENES DE DISEÑO AÚN</p>
                                                    </div>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                        <div class="tab-pane fade" id="content2" role="tabpanel" arial-labelledby="tab2">
                                            <?php require "./partials/tables/od/diseniador/odMine.php"; ?>
                                        </div>
                                    </div>
                                
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
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el contenedor de la lista
    var listaElementos = document.getElementById('listaElementos');

    // Obtener el campo de entrada y el botón de agregar
    var campoEntrada = document.getElementById('nuevo_elemento');
    var botonAgregar = document.getElementById('agregarElemento');

    // Manejador de evento para agregar elemento
    botonAgregar.addEventListener('click', function() {
        // Obtener el valor del nuevo elemento
        var nuevoElemento = campoEntrada.value;

        // Validar si el campo no está vacío
        if (nuevoElemento.trim() !== '') {
            // Crear un nuevo elemento de lista y agregarlo al contenedor
            var nuevoItem = document.createElement('li');
            nuevoItem.textContent = nuevoElemento;
            listaElementos.appendChild(nuevoItem);

            // Limpiar el campo de entrada después de agregar el elemento
            campoEntrada.value = '';

            // Enviar el nuevo elemento al servidor utilizando AJAX
            enviarElementoAlServidor(nuevoElemento);
        }
    });

    // Función para enviar el nuevo elemento al servidor utilizando AJAX
    function enviarElementoAlServidor(nuevoElemento) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'forms/actualizar_elementos.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // La solicitud se completó exitosamente
                    console.log('Elemento agregado correctamente al servidor.');
                } else {
                    // Hubo un error al procesar la solicitud
                    console.error('Error al agregar el elemento al servidor.');
                }
            }
        };
        xhr.send('nuevo_elemento=' + encodeURIComponent(nuevoElemento));
    }
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var listaElementos = document.getElementById('listaElementos');
        var campoEntrada = document.getElementById('nuevo_elemento');
        var botonAgregar = document.getElementById('agregarElemento');
        var botonLimpiar = document.getElementById('limpiarArray'); // Botón para limpiar el array

        botonAgregar.addEventListener('click', function() {
            var nuevoElemento = campoEntrada.value;
            if (nuevoElemento.trim() !== '') {
                var nuevoItem = document.createElement('li');
                nuevoItem.textContent = nuevoElemento;
                listaElementos.appendChild(nuevoItem);
                campoEntrada.value = '';
            }
        });

        // Manejador de clic para limpiar el array
        botonLimpiar.addEventListener('click', function() {
            listaElementos.innerHTML = ''; // Vaciar el contenido del contenedor
            // También podrías limpiar el array en el servidor utilizando AJAX si fuera necesario
        });
    });

</script>