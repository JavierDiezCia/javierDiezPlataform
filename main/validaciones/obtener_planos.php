<?php
// Conexión a la base de datos
require "../../sql/database.php";

// Verificar si se recibió el área de trabajo del empleado
if(isset($_POST['area_trabajo'])) {
    $areaTrabajoEmpleado = $_POST['area_trabajo'];

    // Consulta SQL para obtener los datos de los planos asociados al área de trabajo del empleado
    $query = "SELECT p.pla_id, p.pla_numero, pro.pro_id 
                FROM planos p 
                INNER JOIN produccion pro ON p.pla_id = pro.pla_id 
                INNER JOIN pro_areas pa ON pro.pro_id = pa.pro_id
                WHERE pa.proAre_detalle = :area_trabajo 
                AND pro.pro_id IS NOT NULL 
                AND pa.proAre_porcentaje < 100 
                AND p.pla_estado = 'ACTIVO'";

    // Preparar la consulta
    $statement = $conn->prepare($query);
    $statement->bindParam(':area_trabajo', $areaTrabajoEmpleado);
    $statement->execute();

    // Obtener los resultados de la consulta
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (empty($result)) {
        // Si no se encontraron planos asociados al área de trabajo del empleado, devolver un error
        $result = array('error' => 'No se encontraron planos asociados al área de trabajo del empleado');
        return;
    } else {
        // Si se encontraron planos asociados al área de trabajo del empleado, devolver los resultados en formato JSON
        echo json_encode($result);
    }

} else {
    // Si no se recibió el área de trabajo del empleado, devolver un error
    echo json_encode(array('error' => 'No se proporcionó el área de trabajo del empleado'));
}
?>
