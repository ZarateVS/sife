$("a#Modificar").click(function() {
    var id_col = $(this).data('id_col');
    var id_edo = $(this).data('id_edo');
    Modificar("MostrarDatosColonia", id_col, id_edo);
});

$("input#activar_desactivar").click(function () {
    $("input#activar_desactivar").prop('disabled', true);
    var id_colonia = $(this).data('id');
    var nom_colonia = $(this).data('nombre');
    
    var valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = "valor=" + valor + "&id_colonia=" + id_colonia + "&nom_colonia=" + nom_colonia;
    var id_mpio = $("#municipio").val();
    ActivarDesactivar(datos, id_mpio);
});

function ActivarDesactivar(datos, id_mpio) {
    $.ajax({
        url: 'ActivarDesactivarColonia',
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
                    llenarTablaColonias('ObtenerColonias', id_mpio);
                }
                else {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'No se pudo realizar la operación.',
                        type: 'error',
                        position: 'middle-center',
                        close: function() {
                            $("input#activar_desactivar").prop('disabled', false);
                            llenarTablaColonias('ObtenerColonias', id_mpio);
                        }
                    });
                }
            }
    }).always(function () {
            $.unblockUI();
    });
}

function Modificar(url, id_col, id_edo) {
    $.ajax({
        url: url,
        type: "POST",
        data: "id_col="+id_col+"&id_edo="+id_edo,
        success: function (respuesta) {
            $("#contenido").html(respuesta);
        }
    });
}

function llenarTablaColonias(url, id ){
    $.ajax({
        url:url,
        type:'POST',
        data:'id='+id,
        success: function (respuesta) {
            $("#catalogo").html(respuesta);
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