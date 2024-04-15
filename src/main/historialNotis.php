<?php

require '../sql/database.php';
require 'partials/session_handler.php';

if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

$statement = $conn->prepare("SELECT N.*, P.per_nombres, P.per_apellidos, NV.notiVis_vista
                                FROM notificaciones N
                                JOIN personas P ON N.noti_cedula = P.cedula
                                JOIN noti_visualizaciones NV ON N.noti_id = NV.noti_id
                                WHERE noti_destinatario = :destinatario AND notiVis_cedula = :cedula
                                ORDER BY noti_fecha DESC LIMIT 200");
$statement->bindParam(":destinatario", $_SESSION['user']['usu_rol']);
$statement->bindParam(":cedula", $_SESSION["user"]["cedula"]);
$statement->execute();
$notificaciones = $statement->fetchAll(PDO::FETCH_ASSOC);

$tiempoTranscurrido = new DateTime('2022-01-01 00:00:00');
$tiempoTranscurrido->modify('-1 day');



?>

<?php require 'partials/header.php'; ?>
<?php require 'partials/dashboard.php'; ?>

<section>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-header">
                            <h5 class="card-title">Historial de notificaciones</h5>
                        </div>
                        <?php if (empty($notis)) : ?>
                            <div class="cold-mx-4 mx-auto mb-3">
                                <div class="card card-body text-center">
                                    <p>NO HAY NOTIFICACIONES AÚN.</p>
                                </div>
                            </div>
                        <?php else : ?>
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Remitente</th>
                                        <th>Detalle</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notificaciones as $noti) : ?>
                                        <tr>
                                            <td><?= date('l j \d\e F \|\ H:i', strtotime($noti['noti_fecha'])) ?></td>
                                            <td><?= $noti['per_nombres'] . " " . $noti['per_apellidos'] ?></td>
                                            <td><?= $noti['noti_detalle'] ?></td>
                                            <td><?= $noti['notiVis_vista'] == 0 ? "No vista" : "Vista" ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'partials/footer.php'; ?>