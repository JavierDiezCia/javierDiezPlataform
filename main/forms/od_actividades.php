<?php

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// DECLARAMOS LA VARIABLE ERROR
$error = null;

// Obtener todos los elementos del array (si existen)
$elementos = isset($_SESSION["elementos"]) ? $_SESSION["elementos"] : [];
?>

<!-- Mostrar los elementos existentes -->
<ul id="listaElementos">
    <?php foreach ($elementos as $elemento): ?>
        <li><?= $elemento ?></li>
    <?php endforeach; ?>
</ul>

<!-- Formulario para agregar nuevos elementos -->
<form id="formularioActividades">
    <input type="text" name="nuevo_elemento" id="nuevo_elemento" placeholder="Nuevo elemento" required>
    <button type="button" id="agregarElemento">Agregar</button>
</form>

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
