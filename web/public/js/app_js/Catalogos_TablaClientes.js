$("a#Modificar").click(function() {
    idcte = $(this).data("id");
    cp = $(this).data("cp");
    tp = $(this).data("tp");
    Modificar("ModificarEmisor", idcte, cp, tp);
});

$("input#activar_desactivar").click(function () {
    idcte = $(this).data('id');
    rs = $(this).data('rs');
    
    valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = "valor=" + valor + "&idcte=" + idcte + "&rs=" + rs;
    $.ajax({
        url: 'ActivarDesactivarCliente',
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
                    $("#catalogo").load("ObtenerClientes");
                }
            }
    }).always(function () {
            $.unblockUI();
    });
});

function Modificar(url, id, cp, tp)
{
    $.ajax({
        url:url,
        type:"POST",
        data: "id=" + id + "&cp=" + cp + "&tp=" + tp,
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