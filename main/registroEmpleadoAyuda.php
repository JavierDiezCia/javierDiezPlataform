<?php
require "../sql/database.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}
// declaramos la variable error
$error = null;
// Validar si el usuario es un empleado
if ($_SESSION["user"]["usu_rol"] == 6 || $_SESSION["user"]["usu_rol"] == 1) {
    // Obtener la cédula del empleado
    $actividades_pintura = array("REVISIÓN OP", "CONFIRMAR COLORES EN LA OP", "SELECCIÓN DE PINTURA SEGÚN MATERIAL", "MASILLAR", "LIJAR", "FONDEADO", "PROTECCIÓN PARA DIVISIÓN DE COLORES", "TERMINADO", "CUARTO DE SECADO", "PINTURA ELECTROESTÁTICA", "ENTREGA JEFE DE PRODUCCIÓN", "APLICACIÓN SELLADOR EN MADERA", "REPINTAR", "APLICACIÓN WASH PREMIER", "APLICACIÓN MONTO", "APLICACIÓN TINTE (MADERA)", "LIMPIEZA");
    $actividades_acrilicos = array("REVISIÓN OP", "REQUERIMIENTO DE MATERIALES", "REDISEÑO DE CORTES Y GRABADO", "DISEÑO DE MATRICES", "ENVÍO A MÁQUINAS (ROUTER/LASE)", "PULIDO DE MATERIAL", "TERMOFORMAR", "SOPLADO", "CORTE DE BASE DE LETRAS", "MDF PINTURA", "SILVATRIM", "SISTEMA ELÉCTRICO", "SELLADOR DE BORDES", "ANCLAJE A BASE", "ENTREGA JEFE DE PRODUCCIÓN", "LIMPIEZA PANERAS", "LIMPIEZA", "TENSADO LONA", "APLICACIÓN VINILOS", "ARMADO LETRAS", "CALADO DE LETRAS");
    $actividades_metal = array("REVISIÓN OP", "REVISIÓN DE MATERIAL", "SOLICITUD DE MATERIAL", "ENVÍO A BAROLAR", "CORTE EN TROZADORA", "DISEÑO EN AUTOCAD DE CORTE ESPECIAL (PLASMA)", "CORTE PLASMA", "CORTE CIZALLA", "PLANTILLA DE ARMADO", "DOBLADORA", "SUELDA MIC", "SUELDA TIC", "SUELDA ALUMINIO", "SUELDA ESTANIO", "PULIDO NORMAL", "PULIDO INOX", "COLOCACIÓN ITEMS ESPECIALES", "MOLDEO", "ENVÍO A PINTURA", "CORTE MANUAL", "LIMPIEZA");
    $actividades_carpinteria = array("RECIBEN OP", "REVISIÓN OP", "DESARROLLO DE MATRICES", "CONFIRMACIÓN DE MEDIDAS Y MATERIAL", "DESPIECE DE ELEMENTOS", "CORTE ESCUADRADORA (SOLO MELAMÍNICO)", "LAMINADORA (SOLO MELAMÍNICO)", "CIERRA DE BRAZO RADIAL", "CIERRA DE BANCO", "PREPARADO DE LOS ELEMENTOS PARA EL MUEBLE", "REMATE 1: LAMINAR MANUALMENTE", "REMATE 2: CORRECCIÓN DE FALLAS", "REMATE 3: PULIR", "LIMPIEZA", "ENTREGA JEFE DE PRODUCCIÓN", "ENTREGA PINTURA (SI LO REQUIERE EL PRODUCTO)", "ENTREGA ACRÍLICO (SI LO REQUIERE EL PRODUCTO)");
    $actividades_acm = array("REVISIÓN OP", "REDISEÑO DE ESTRUCTURAS", "SOLICITUD DE MATERIAL", "SOLICITAR ESTRUCTURAS A METALMECÁNICA", "SOLICITAR CORTE ROUTER", "RANURA PARA DOBLEZ", "TERMINADOS");
    $actividades_maquinas = array();

    // Inicializar $actividades
    $actividades = [];
    if ($area == "PINTURA") {
        $actividades = $actividades_pintura;
    } else if ($area == "ACRÍLICOS Y ACABADOS") {
        $actividades = $actividades_acrilicos;
    } else if ($area == "CARPINTERÍA") {
        $actividades = $actividades_carpinteria;
    } else if ($area == "METALMECÁNICA") {
        $actividades = $actividades_metal;
    } else if ($area == "ACM") {
        $actividades = $actividades_acm;
    } else if ($area == "MAQUINAS") {
        $actividades = $actividades_maquinas;
    }
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registro de Empleado de Ayuda</h5>
                        <!-- Si hay un error, mostrarlo -->
                        <?php if ($error) : ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="registroEmpleadoAyuda.php">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="op_id" name="op_id" required>
                                        <option selected disabled value="">SELECIONE LA ORDEN DE PRODUCCION</option>
                                        <?php foreach ($ops as $op) : ?>
                                            <option value="<?= $op["id"] ?>"><?= $op["id"] ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <label for="op_id">Orden de Producción</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="pla_id" name="pla_id" required>
                                        <option selected disabled value="">SELECIONE EL PLANO</option>
                                    </select>
                                    <label for="pla_id">Plano</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="area" name="area" required onchange="obtenerActividades(this.value)">
                                        <option selected disabled value="">SELECIONE EL AREA</option>
                                        <option value="ACM">ACM</option>
                                        <option value="ACRÍLICOS Y ACABADOS">ACRÍLICOS Y ACABADOS</option>
                                        <option value="CARPINTERÍA">CARPINTERÍA</option>
                                        <option value="MAQUINAS">MAQUINAS</option>
                                        <option value="METALMECÁNICA">METALMECÁNICA</option>
                                        <option value="PINTURA">PINTURA</option>
                                    </select>
                                    <label for="area">Área</label>
                                </div>
                            </div>
                            <div class="col-md-6" id="contenedorDeActividades">
                                <h5 class="card-title">ACTIVIDADES</h5>
                                <?php foreach ($actividades as $actividad) : ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="<?= strtolower(str_replace(" ", "_", $actividad)) ?>" name="actividades[]" value="<?= $actividad ?>">
                                        <label class="form-check-label" for="<?= strtolower(str_replace(" ", "_",  $actividad)) ?>">
                                            <?= $actividad ?>
                                        </label>
                                    </div>
                                <?php endforeach ?>
                                <!-- Campo para ingresar otra actividad -->
                                <div class="form-floating mb-3 mt-3">
                                    <input type="text" class="form-control" id="otra_actividad" name="otra_actividad">
                                    <label for="otra_actividad">Otra Actividad</label>
                                </div>
                            </div>

                            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
                            <script>
                                function obtenerActividades(areaSeleccionada) {
                                    $.ajax({
                                        url: 'ajax.php',
                                        type: 'post',
                                        data: {
                                            area: areaSeleccionada
                                        },
                                        success: function(response) {
                                            var actividades = JSON.parse(response);
                                            var html = '';
                                            for (var i = 0; i < actividades.length; i++) {
                                                html += '<div class="form-check">';
                                                html += '<input class="form-check-input" type="checkbox" id="' + actividades[i].toLowerCase().replace(" ", "_") + '" name="actividades[]" value="' + actividades[i] + '">';
                                                html += '<label class="form-check-label" for="' + actividades[i].toLowerCase().replace(" ", "_") + '">' + actividades[i] + '</label>';
                                                html += '</div>';
                                            }
                                            $("#contenedorDeActividades").html(html);
                                        }
                                    });
                                }
                            </script>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">REGISTRAR</button>
                                <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>