<?php
// Verificar si se ha enviado un nuevo elemento para agregar al array
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nuevo_elemento"])) {
    // Obtener el valor del nuevo elemento
    $nuevo_elemento = $_POST["nuevo_elemento"];
    
    // Agregar el nuevo elemento al array en la sesión
    $_SESSION["elementos"][] = $nuevo_elemento;

    // Devolver una respuesta de éxito
    echo "Elemento agregado correctamente al servidor.";
} else {
    // Devolver un mensaje de error si no se proporciona un nuevo elemento
    echo "Error: No se proporcionó un nuevo elemento.";
}
?>
