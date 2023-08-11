$("a#Modificar").click(function() {
    id = $(this).data('id');
    Modificar("ModificarEstado", id);
});

$("input#activar_desactivar").click(function () {
    $("input#activar_desactivar").prop('disabled', true);
    id_edo = $(this).data('id');
    nom_edo = $(this).data('nombre');
    
    valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = "valor=" + valor + "&id_edo=" + id_edo + "&nom_edo=" + nom_edo;
    ActivarDesactivar(datos);
});

function ActivarDesactivar(datos) {
    $.ajax({
        url: 'ActivarDesactivarEstado',
        type: "POST",
        data: datos,
            beforeSend: function () {
                $.blockUI({
                    message: 'Procesando. ¡Espere por favor!',
                    css: {
                        border: 'none',
                        padding: '15px',
                        backgroundColor: '#000',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        opacity: .5,
                        color: '#fff'
                    }
                });
            },
            success: function(respuesta) {
                if ($.trim(respuesta) == 1) {
                    $("#catalogo").load("ObtenerEstados");
                }
                else {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'No se pudo realizar la<br> operación.',
                        type: 'error',
                        position: 'middle-center',
                        close: function() {
                            $("input#activar_desactivar").prop('disabled', false);
                            $("#catalogo").load("ObtenerEstados");
                        }
                    });
                }
            }
    }).always(function () {
            $.unblockUI();
    });
}

function Modificar(url, id) {
    $.ajax({
        url:url,
        type:"POST",
        data: "id=" + id,
        success: function (respuesta) {
            $("#contenido").html(respuesta);
        }
    });
}

$(document).ready(function() {
    $("#table").DataTable({
        "language": {
            "url": "../public/js/Spanish.js"
        }
    });
});