function buscarPorNombres() {
    var nombresInput = document.getElementById('nombres');
    var nombres = nombresInput.value;

    // Realizar la consulta AJAX solo si los nombres no están vacíos
    if (nombres.trim() !== '') {
        // Realizar la consulta AJAX
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Actualizar el valor del campo "cedula" con la respuesta del servidor
                document.getElementById('cedula').value = xhr.responseText;
            }
        };
        xhr.open('GET', 'ajax.php?nombres=' + nombres, true);
        xhr.send();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const nombresInput = document.getElementById('nombres');
    const trabajadorInfo = document.getElementById("trabajadorInfo");

    nombresInput.addEventListener("input", function () {
        const selectedNombres = nombresInput.value;

        // Realizar una solicitud AJAX para obtener la cédula por los nombres del asociado
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Actualizar el contenido del div con la cédula recuperada
                trabajadorInfo.innerHTML = xhr.responseText;
            }
        };

        xhr.open("GET", "obtener_cedula.php?nombres=" + selectedNombres, true);
        xhr.send();
    });
});
