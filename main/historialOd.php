<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Validamos los perfiles
if ($_SESSION["user"]["usu_rol"] != 2) {
    // Si el rol no es 2 (Diseñador ADMIN), redirigimos al usuario a la página de inicio
    header("Location:./index.php");
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
                                <div class="card-header">
                                    <h5 class="card-tittle">ORDENES DE DISEÑO</h5>
                                    <ul class="nav nav-tabs" id="myTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="tab1" data-toggle="tab" href="#content1" role="tab" aria-controls="content1" aria-selected="true">HISTORIAL</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab7" data-toggle="tab" href="#content7" role="tab" aria-controls="content7" aria-selected="false">PROPUESTA</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab2" data-toggle="tab" href="#content2" role="tab" aria-controls="content2" aria-selected="false">APROBADA SIN OP</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab3" data-toggle="tab" href="#content3" role="tab" aria-controls="content3" aria-selected="false">OP CREADA</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab4" data-toggle="tab" href="#content4" role="tab" aria-controls="content4" aria-selected="false">MATERIALIDAD</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab5" data-toggle="tab" href="#content5" role="tab" aria-controls="content5" aria-selected="false">DESAPROBADA</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab6" data-toggle="tab" href="#content6" role="tab" aria-controls="content6" aria-selected="false">EN COBRAZNA</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="content1" role="tabpanel" aria-labelledby="tab1">
                                        <?php require "./partials/tables/od/odAll.php"; ?>
                                    </div>
                                    <div class="tab-pane fade" id="content7" role="tabpanel" aria-labelledby="tab7">
                                        <?php require "./partials/tables/od/odPropuesta.php"; ?>
                                    </div>
                                    <div class="tab-pane fade" id="content2" role="tabpanel" aria-labelledby="tab2">
                                        <?php require "./partials/tables/od/odOp.php"; ?>
                                    </div>
                                    <div class="tab-pane fade" id="content3" role="tabpanel" aria-labelledby="tab3">
                                        <?php require "./partials/tables/od/opCreada.php"; ?>
                                    </div>
                                    <div class="tab-pane fade" id="content4" role="tabpanel" aria-labelledby="tab4">
                                        <?php require "./partials/tables/od/odMaterialidad.php"; ?>
                                    </div>
                                    <div class="tab-pane fade" id="content5" role="tabpanel" aria-labelledby="tab5">
                                        <?php require "./partials/tables/od/odDesaprobada.php"; ?>
                                    </div>
                                    <div class="tab-pane fade" id="content6" role="tabpanel" aria-labelledby="tab6">
                                        <?php require "./partials/tables/od/odEnCobranza.php"; ?>
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
