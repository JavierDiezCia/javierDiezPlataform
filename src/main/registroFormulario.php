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
$sinFinalizar = "Sin Finalizar";
$id = isset($_GET["id"]) ? $_GET["id"] : null;

if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    //CONSULTA DE L FORMULARIOS SIN TERMIANR
    $logistica1 = $conn->query("SELECT LOGI.*, 
    CEDULA.PERNOMBRES AS CEDULA_NOMBRES1, 
    CEDULA.PERAPELLIDOS AS CEDULA_APELLDIOS1,
    PLANOS.PLANNUMERO  AS IDPLANO1, 
    PLANOS.IDOP AS IDOP1
   FROM LOGISTICA AS LOGI
   JOIN PERSONAS AS CEDULA ON LOGI.LOGCEDULA = CEDULA.CEDULA 
   LEFT JOIN PLANOS   ON LOGI.IDPLANO = PLANOS.IDPLANO
   
   WHERE LOGI.LOGESTADO ='REGISTRO SIN FINALIZAR'");

    //CONSULTADE  LOS REGISTRO COMPLETADOS
    $logistica = $conn->query("SELECT LOGI.*, 
                                CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLDIOS,
                                  PLANOS.PLANNUMERO  AS IDPLANO  , PLANOS.IDOP AS IDOP 
                                FROM LOGISTICA AS LOGI
                                JOIN PERSONAS AS CEDULA ON LOGI.LOGCEDULA = CEDULA.CEDULA 
                                LEFT JOIN PLANOS   ON LOGI.IDPLANO= PLANOS.IDPLANO
                                 
                                WHERE LOGI.LOGESTADO ='FINALIZADO EL REGISTRO'");

    //OBTENER LOS DATOS DE LA OP
    $op = $conn->query("SELECT*FROM OP");
} else {
    header("Location:./index.php");
    return;
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<!-- Agrega el script jQuery y el script AJAX aquí -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function(){
        var timeout;

        $('#op').on('input', function(){
            var opValue = $(this).val();

            // Cancela la solicitud anterior si aún no se ha completado
            clearTimeout(timeout);

            // Espera 500ms después de que el usuario haya dejado de escribir
            timeout = setTimeout(function(){
                // Realizar la solicitud AJAX
                $.ajax({
                    type: 'POST',
                    url: 'buscar_planos.php',
                    data: { op: opValue },
                    success: function(response){
                        // Actualizar las opciones del select
                        $('#plano').html(response);
                    }
                });
            }, 500);
        });
    });
</script>

<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <h5 class="card-title">Tipos de Registros de los formularios</h5>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item " role="presentation">
                        <button class="nav-link active" id="finalizado-tab" data-bs-toggle="tab" data-bs-target="#finalizado" type="button" role="tab" aria-controls="finalizar" aria-selected="true" >Registros Finalizados</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="sinFinalizar-tab" data-bs-toggle="tab" data-bs-target="#sinFinalizar" type="button" role="tab" aria-controls="sinFinalizar" aria-selected="false" tabindex="-1">Registros sin Finalizar</button>
                    </li>
                    
                </ul>
                <div class="tab-content pt-2" id="myTabContent">
                    <div class="tab-pane fade " id="sinFinalizar" role="tabpanel" aria-labelledby="sinFinalizar-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title"> Registros de los Formularios de Logistica sin Terminar </h5>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Registro</th>
                                                        <th>OP</th>
                                                        <th>Plano</th>
                                                        <th>Area de Trabajo</th>
                                                        <th>Hora y Fecha de Registro</th>
                                                        <th>Hora y Fecha del final del Trabajo</th>
                                                        <th>Observaciones</th>
                                                        <th>Persona que realizo el Registro</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($logistica1 as $logistica1) : ?>
                                                        <tr>
                                                            <td><?= $logistica1["IDLOGISTICA"] ?></td>
                                                            <td><?= $logistica1["IDOP1"] ?></td>
                                                            <td><?= $logistica1["IDPLANO1"] ?></td>
                                                            <td><?= $logistica1["LOGAREATRABAJO"] ?></td>
                                                            <td><?= $logistica1["LOGHORAINCIO"] ?></td>
                                                            <td><?= $sinFinalizar ?></td>
                                                            <td><?= $logistica1["LOGOBSERVACIONES"] ?></td>
                                                            <td><?= $logistica1["CEDULA_NOMBRES1"] ." " .$logistica1["CEDULA_APELLDIOS1"] ?></td>
                                                            <td><?= $logistica1["LOGESTADO"] ?></td>
                                                        
                                                        </tr> 
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </section>
                    </div>
                    <div class="tab-pane fade show active" id="finalizado" role="tabpanel1" aria-labelledby="finalizado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="">
                                    <?php if(empty($id)) : ?>

                                    <?php else : ?>
                                        <?php
                                             $stament = $conn->prepare("SELECT");
                                             $stament->bindParam(":id", $id);
                                             $registroEditar = $stament->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Editar Registro de Logistica</h5>
                                                
                                                <?php if ($error) : ?>
                                                    <p class="text-danger">
                                                        <?=$error ?>
                                                    </p>
                                                <?php endif ?>
                                                <form class="row g-3" method="POST" action="registroFormulario.php">
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input value="" type="text" class="form-control" id="op" name="op" placeholder="Buscar por  Op" list="opList" >
                                                            <label for="op">Ingrese la Op</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Cliente" readonly>
                                                            <label for="cliente">Cliente</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-mb-6">
                                                        <div class="form-floating">
                                                            <select  class="form-select" id="plano" name="plano" aria-label="Stat e">
                                                            <option value="" selected>Selecione el numero de plano</option>
                                                            </select>
                                                        </div>
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
                                                                <h5 class="card-title">Registro de los Formularios de Logistica Terminados</h5>
                                                            </div>
                                                            <table class="table datatable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Registro</th>  
                                                                        <th>OP</th> 
                                                                        <th>Plano</th>
                                                                        <th>Area de Trabajo</th>
                                                                        <th>Hora y Fecha de Registro</th>
                                                                        <th>Hora y Fecha del final del Trabajo</th>
                                                                        <th>Observaciones</th>
                                                                        <th>Persona que realizo el Registro</th>
                                                                        <th>Estado</th>
                                                                        
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach($logistica as $logistica) : ?>
                                                                        <tr>
                                                                            <td><?= $logistica["IDLOGISTICA"] ?></td>
                                                                            <td><?= $logistica["IDOP"] ?></td>
                                                                            <td><?= $logistica["IDPLANO"] ?></td>
                                                                            <td><?= $logistica["LOGAREATRABAJO"] ?></td>
                                                                            <td><?= $logistica["LOGHORAINCIO"] ?></td>
                                                                            <td><?= $logistica["LOGHORAFINAL"] ?></td>
                                                                            <td><?= $logistica["LOGOBSERVACIONES"] ?></td>
                                                                            <td><?= $logistica["CEDULA_NOMBRES"] ." " .$logistica["CEDULA_APELLDIOS"]?></td>
                                                                            <td><?= $logistica["LOGESTADO"] ?></td>
                                                                            
                                                                        </tr>
                                                                    <?php endforeach ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>