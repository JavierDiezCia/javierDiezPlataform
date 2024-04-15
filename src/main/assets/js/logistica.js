function buscarPorOp() {
    var opInput = document.getElementById('op');
    var op = opInput.value;  // Corregir de ariaValueMax a value

    // REALIZAR LA CONSULTA AJAX SOLO SI LA OP NO ESTÁ VACÍA
    if (op.trim() !== '') {
        // REALIZAR LA CONSULTA AJAX
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // ACTUALIZAR EL VALOR DEL CAMPO "CLIENTE" CON LA RESPUESTA DEL SERVIDOR
                document.getElementById('cliente').value = xhr.responseText;
            }
        };
        xhr.open('GET', 'ajax.php?op=' + op, true);
        xhr.send();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const opInput = document.getElementById('op');
    const trabajadorInfo = document.getElementById("trabajadorInfo");

    opInput.addEventListener("input", function () {
        const selectedOp = opInput.value;

        // REALIZAR UNA SOLICITUD AJAX PARA OBTENER EL CLIENTE POR LA OP
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // ACTUALIZAR EL CONTENIDO DEL DIV CON EL CLIENTE RECUPERADO
                trabajadorInfo.innerHTML = xhr.responseText;
            }
        };
        xhr.open("GET", "obtener_cliente.php?cliente=" + selectedOp, true);
        xhr.send();
    });
});
