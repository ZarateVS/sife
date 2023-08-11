$("input#activar_desactivar_consulta, input#activar_desactivar_captura").click(function () {
    var idusr = $(this).data('idusr');
    var campo = $(this).data('campo');
    var idsuc = $(this).data('idsuc');
    var accion = $(this).data('accion');
    
    var valor = $(this).is(':checked') ? 1 : 0;
    
    var datos = '{\n\
                    "valor":"'+valor+'",\n\
                    "idusr":"'+idusr+'",\n\
                    "campo":"'+campo+'",\n\
                    "idsuc":"'+idsuc+'",\n\
                    "accion":"'+accion+'"\
                }';
    ActivarDesactivar(datos);
});

$("input[type=checkbox]#todos_consulta").click(function () {
    var idusr = $(this).data('idusr');
    var valor = $(this).is(':checked') ? 1 : 0;
    ActivarDesactivarTodos(idusr, valor, "Consultar");
    /*var ids_sucs = [];
    var i = 0;
    $("input#activar_desactivar_consulta").each(function () {
        var idusr2 = $(this).data('idusr');
        if (idusr == idusr2) {
            ids_sucs[i] = '"'+$(this).data('idsuc')+'"';
            i++;
        }
    });
    var datos = '{\n\
                    "valor":'+'"'+valor+'"'+',\n\
                    "id_usr":'+'"'+idusr+'"'+',\n\
                    "campo":'+'"consulta"'+',\n\
                    "ids_sucs":'+'['+ids_sucs+']'+',\n\
                    "accion":'+'"Consultar"'+'\
                }';
    var data = $(this).data();
    for(var i in data){
        datos = '{"'+i+'":"'+data[i]+'"}';
    }
    alert(datos);
    ActivarDesactivar(datos);*/
});

$("input[type=checkbox]#todos_captura").click(function (){
    var idusr = $(this).data('idusr');
    var valor = $(this).is(':checked') ? 1 : 0;
    ActivarDesactivarTodos(idusr, valor, "Subir Archivos");
    /*var ids_sucs = [];
    var i = 0;
    $("input#activar_desactivar_captura").each(function () {
        var idusr2 = $(this).data('idusr');
        if (idusr == idusr2) {
            ids_sucs[i] = '"'+$(this).data('idsuc')+'"';
            i++;
        }
    });
    var datos = '{\n\
                    "valor":'+'"'+valor+'"'+',\n\
                    "id_usr":'+'"'+idusr+'"'+',\n\
                    "campo":'+'"captura"'+',\n\
                    "ids_sucs":'+'['+ids_sucs+']'+',\n\
                    "accion":'+'"Subir Archivos"'+'\
                }';
    ActivarDesactivar(datos);*/
});

function ActivarDesactivarTodos(idusr, valor, accion) {
    var ids_sucs = [];
    var i = 0;
    $("input#activar_desactivar_consulta").each(function () {
        var idusr2 = $(this).data('idusr');
        if (idusr == idusr2) {
            ids_sucs[i] = '"'+$(this).data('idsuc')+'"';
            i++;
        }
    });
    var datos = '{\n\
                    "valor":'+'"'+valor+'"'+',\n\
                    "id_usr":'+'"'+idusr+'"'+',\n\
                    "campo":'+'"consulta"'+',\n\
                    "ids_sucs":'+'['+ids_sucs+']'+',\n\
                    "accion":'+'"'+accion+'"'+'\
                }';
    
    ActivarDesactivar(datos);
}

function ActivarDesactivar(datos)
{
    $.ajax({
        url: 'ActivarDesactivarAccionSucursal',
        type: "POST",
        data: datos,
        success: function(respuesta) {
            if ($.trim(respuesta) == 1)
               $("#listado").load("ObtenerUsuariosConSucursalesAsignadas");
        }
    });
}

function Modificar(url, id)
{
    $.ajax({
        url:url,
        type:"POST",
        data: "id="+id,
        success: function (respuesta) {
            $("#contenido").html(respuesta);
        }
    });
}

$(document).ready(function() {
    var i =0, j=0, k=0;
    var ids=[];
    $("input[type=checkbox]#activar_desactivar_consulta").each(function(){
       var id = $(this).data('idusr');
       if(ids.indexOf(id) == -1 || ids[0] == "") {
           ids[i]= id;
           i++;
        }
    });
    
    $("input[type=checkbox]#todos_consulta").each( function() {
        var b=1;
        $("input[type=checkbox]#activar_desactivar_consulta").each( function() {
            var id=$(this).data('idusr');
            if (id == ids[j]) {
                if (!$(this).is(':checked')) {
                    b=0;
                }
            }
        });
        j++;
        if (b===1) {
            $(this).attr('checked',true);
        }
    });
    
    $("input[type=checkbox]#todos_captura").each( function() {
        var b=1;
        $("input[type=checkbox]#activar_desactivar_captura").each( function() {
            var id=$(this).data('idusr');
            if (id == ids[k]) {
                if (!$(this).is(':checked')) {
                    b=0;
                }
            }
        });
        k++;
        if (b===1) {
            $(this).attr('checked',true);
        }
    });

    $("#table").DataTable({
        "language": {
            "url": "../public/js/Spanish.js"
        },
                    
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
                        "<tr class='group'><td colspan='5'>"+group+"</td></tr>"
                    );
 
                    last = group;
                }
            });
            
        }
        
    });
    
    $("#table tbody").on( 'click', 'a#Modificar', function() {
        var idusr = $(this).data('idusr');
        Modificar("ModificarUsuarioSucursal", idusr);
    });
    
    $("#table tbody").on( 'click', 'a#Eliminar', function() {
        idusr = $(this).data('idusr');
        $.ajax({
            url: 'EliminarRelacionUsuarioSucursal',
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
                            $("#listado").load("ObtenerUsuariosConSucursalesAsignadas");
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
});