$("a#Modificar").click(function() {
    id = $(this).data('id');
    Modificar("ModificarSucursal", id);
});

$("input#activar_desactivar").click(function () {
    $("input#activar_desactivar").prop('disabled', true);
    idsuc = $(this).data('id');
    suc = $(this).data('sucursal');
    cve = $(this).data('clave');
    
    valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = "valor=" + valor + "&idsuc=" + idsuc + "&suc=" + suc + "&cve=" + cve;
    var r = ActivarDesactivar(datos);
    if (r == 0)
        $(this).prop("checked", false);
});

function ActivarDesactivar(datos) {
    var r = "";
    $.ajax({
        async: false,
        url: 'ActivarDesactivarSucursal',
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
                if ($.trim(respuesta) == "1") {
                    $("#catalogo").load("ObtenerSucursales");
                    r = 1;
                }
                else {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'Esta clave se encuentra <br> activa para otra sucursal.',
                        type: 'warning',
                        position: 'middle-center',
                        close: function () {
                            $("input#activar_desactivar").prop('disabled', false);
                        }
                    });
                    r = 0;
                }
            }
    }).always(function () {
            $.unblockUI();
    }); 
    return r;
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

$("#Exportar").click(function(){
    alert("Ready!");
    //window.open('http://localhost/sife/web/app_dev.php/Excel', '_blank' );
});

$(document).ready(function() {
    var t = $("#table").DataTable({
        "language": {
            "url": "../public/js/Spanish.js"
        }
    /*dom: '<"row"lBfrtip>',
    "buttons": {
        buttons: [
            {
                text: '',
                action: function ( dt ) {
                    //$("div.dt-buttons").load("boton");
                    //$("div.dt-buttons").html("<button data-toggle='tooltip' title='Exportar Sucursales' id='Exportar' class='btn btn-raised btn-success'><span class='fa fa-arrow-circle-down'></span><span class='hidden-xs'> Exportar</span></button><br>");
                }
            }
        ]
    }*/
    });
    //window.setTimeout("$('div.dt-buttons").html("<button data-toggle='tooltip' title='Exportar Colonias' id='Exportar' class='btn btn-raised btn-success'><span class='fa fa-arrow-circle-down'></span><span class='hidden-xs'> Exportar</span></button>');");
});