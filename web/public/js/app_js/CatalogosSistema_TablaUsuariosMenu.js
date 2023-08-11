$("input#activar_desactivar").click(function() {
    idusr = $(this).data('idusr');
    campo = $(this).data('campo');
    idmnu = $(this).data('idmnu');
    rol = $(this).data('rol');
    nommnu = $(this).data('nom_mnu');
    
    valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = '{"valor":'+'"'+valor+'"'+',"idusr":'+'"'+idusr+'"'+',"campo":'+'"'+campo+'"'+',"idmnu":'+'"'+idmnu+'"'+',"nommnu":'+'"'+nommnu+'"'+',"rol":'+'"'+rol+'"'+'}';
    $.ajax({
        url: 'ActivarDesactivarAccionMenu',
        type: "POST",
        data: datos,
        success: function(respuesta) {
            if ($.trim(respuesta) == "1") {
                $("#catalogo").load("ObtenerUsuariosConMenusAsignados");
            }
            else {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No se pudo realizar la operación.',
                    type: 'error',
                    position: 'middle-center'
                });
            }
        }
    });
});

function Modificar(url, id) {
    $.ajax({
        url: url,
        type: "POST",
        data: "id="+id,
        success: function (respuesta) {
            $("#contenido").html(respuesta);
        }
    });
}

$("#btnSi").click(function(){
        var idusr = $("#id_usuario").val();
        $.ajax({
            url: 'EliminarRelacionUsuarioMenu',
            type: "POST",
            data: "idusr="+idusr,
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
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'Operación completa.',
                        type: 'success',
                        position: 'middle-center',
                        close: function () {
                            $("#listado").load("ObtenerUsuariosConMenusAsignados");
                        }
                    });
                } else {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'No se pudo eliminar relación. Inténtelo nuevamente.',
                        type: 'error',
                        position: 'middle-center'
                    });
                }
            }
        }).always(function () {
            $.unblockUI();
        });
});

$("#btnNo").click(function(){
});

$(document).ready(function() {
    $("#table").DataTable({
        "language": {
            "url": "../public/js/Spanish.js"
        },
        responsive: true,         
        "columnDefs": [
            { "visible": false, "targets": 0 }
        ],
        "order": [[ 0, 'asc' ]],
        "displayLength": 25,
        "drawCallback": function ( ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;

            api.column(0, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group"><td colspan="5">'+group+'</td></tr>\n\
                         <tr><td><b>Menú</b></td><td><b>Insertar</b></td><td><b>Actualizar</b></td><td><b>Eliminar</b></td></tr>'
                    );
 
                    last = group;
                }
            });
        }
    });
    
    $("#table tbody").on( 'click', 'a#Modificar', function() {
        var idusr = $(this).data('idusr');
        Modificar("ModificarUsuarioMenu", idusr);
    });
    
    $("#table tbody").on( 'click', 'a#Eliminar', function() {
        
        idusr = $(this).data('idusr');
        $("#id_usuario").val(idusr);
        /*$.ajax({
            url: 'EliminarAsignacionMenuUsuario',
            type: "POST",
            data: "idusr="+idusr,
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
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'Operación completa.',
                        type: 'success',
                        position: 'middle-center',
                        close: function () {
                            $("#listado").load("ObtenerUsuariosConMenusAsignados");
                        }
                    });
                } else {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'No se pudo eliminar relación. Inténtelo nuevamente.',
                        type: 'error',
                        position: 'middle-center'
                    });
                }
            }
        }).always(function () {
            $.unblockUI();
        });*/
    });
});