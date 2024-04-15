<?php
// Conectar a la base de datos y realizar la búsqueda
require "../sql/database.php";
require "./partials/session_handler.php"; 


if(isset($_POST['op'])){
    $op = $_POST['op'];

    // Realizar la consulta a la base de datos (reemplaza esto con tu consulta real)
    $resultados = $conn->query("SELECT pla_numero FROM planos WHERE op_id LIKE '%$op%'");

    // Construir las opciones del select
    if($resultados->rowCount() > 0){
        $output = '<option value="" selected>SELECCIONA UN NÚMERO DE PLANO.</option>';
        while($row = $resultados->fetch(PDO::FETCH_ASSOC)){
            $output .= '<option value="' . $row['pla_numero'] . '">' . $row['pla_numero'] . '</option>';
        }
        echo $output;
    } else {
        echo '<option value="" selected>AÚN NO HAY PLANOS.</option>';
    }
}
?>
