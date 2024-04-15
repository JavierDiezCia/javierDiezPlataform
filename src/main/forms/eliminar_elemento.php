<?php

// Verificar si se ha recibido un elemento para eliminar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["elemento"])) {
    // Obtener el elemento a eliminar
    $elementoAEliminar = $_POST["elemento"];

    // Buscar el elemento en el array y eliminarlo si se encuentra
    if (($key = array_search($elementoAEliminar, $_SESSION["elementos"])) !== false) {
        unset($_SESSION["elementos"][$key]);
        // Devolver una respuesta exitosa
        http_response_code(200);
        echo "Elemento eliminado correctamente.";
    } else {
        // Devolver una respuesta de error si el elemento no se encontró
        http_response_code(404);
        echo "Elemento no encontrado en la lista.";
    }
} else {
    // Devolver una respuesta de error si no se proporcionó el elemento
    http_response_code(400);
    echo "No se proporcionó un elemento para eliminar.";
}
?>
