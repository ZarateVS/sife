$("a#Modificar").click(function() {
    var idprod = $(this).data('id');
    var url = "ModificarProducto";
    ObtenerProducto(url, idprod);
});

$("input#activar_desactivar").click(function (){
    $("input#activar_desactivar").prop('disabled', true);
    var idprod = $(this).data('id');
    var nomprod = $(this).data('nom');
    
    var valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = "valor=" + valor + "&idprod=" + idprod + "&nomprod=" + nomprod;
    var url = 'ActivarDesactivarProducto';
    ActivarDesactivar(datos, url);
});

function ActivarDesactivar(url, datos) {
    $.ajax({
        url: url,
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
                $("#catalogo").load("ObtenerProductos");
            }
        }
    }).always(function () {
        $.unblockUI();
    });
}

function ObtenerProducto(url, id) {
    $.ajax({
        url: url,
        type: "POST",
        data: "id=" + id,
        success: function (respuesta) {
            $("#contenido").html(respuesta);
        }
    });
}