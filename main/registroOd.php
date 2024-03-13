<script>
    document.getElementById('od_detalle').addEventListener('change', function() {
        var od_id = this.options[this.selectedIndex].getAttribute('data-od_id'); // Obtén el valor de od_id
        
        // Realiza una petición AJAX para obtener las actividades basadas en el od_id seleccionado
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Ajax.php'); // Ruta al archivo PHP que maneja la solicitud AJAX
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var actividades = JSON.parse(xhr.responseText); // Parsea la respuesta JSON
                // Elimina todos los elementos de opción actuales del select de actividades
                var selectActividades = document.getElementById('od_actividades');
                selectActividades.innerHTML = ''; // Limpia el select
                
                // Crea opciones para cada actividad devuelta por la consulta AJAX
                actividades.forEach(function(actividad) {
                    var option = document.createElement('option');
                    option.value = actividad.odAct_detalle;
                    option.text = actividad.odAct_detalle;
                    selectActividades.appendChild(option);
                });
                
                // Mostrar el detalle y la fecha de entrega en inputs separados
                var detalleInput = document.getElementById('detalle');
                var fechaEntregaInput = document.getElementById('fecha_entrega');
                
                detalleInput.value = actividades[0].odAct_detalle;
                fechaEntregaInput.value = actividades[0].odAct_fechaEntrega;
            } else {
                console.error('Error en la petición AJAX');
            }
        };
        // Envía el od_id seleccionado al servidor
        xhr.send('od_id=' + od_id);
    });
</script>