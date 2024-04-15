function buscarPorCedula() {
    var cedulaInput = document.getElementById('cedula');
    var cedula = cedulaInput.value;

    // Realizar la consulta AJAX solo si la cédula no está vacía
    if (cedula.trim() !== '') {
        // Realizar la consulta AJAX
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Actualizar el valor del campo "Trabajador" con la respuesta del servidor
                document.getElementById('nombre').value = xhr.responseText;
            }
        };
        xhr.open('GET', 'ajax.php?cedula=' + cedula, true);
        xhr.send();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const cedulaInput = document.getElementById("cedula");
    const trabajadorInfo = document.getElementById("trabajadorInfo");

    cedulaInput.addEventListener("input", function() {
        const selectedCedula = cedulaInput.value;

        // Realizar una solicitud AJAX para obtener el nombre asociado a la cédula
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Actualizar el contenido del div con el nombre recuperado
                trabajadorInfo.innerHTML = xhr.responseText;
            }
        };
        xhr.open("GET", "obtener_nombre.php?cedula=" + selectedCedula, true);
        xhr.send();
    });
});
