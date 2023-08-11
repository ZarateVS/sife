$(document).ready(function(){
    $('#nombre').focus();
});

$("#Limpiar").click(function(){
    $('#nombre').focus();
    $('#nombre').val("");
});

$("#Guardar").click(function() {
    document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmEstado").serializeObject());
    var msj = validarInfo(datos);
    if (msj == "")
        AgregarEstado("AgregarEstado", datos);
    else {
        $().toastmessage('showToast', {
            sticky: true,
            text: msj,
            type: 'warning',
            position: 'middle-center',
            close: function () {
                document.getElementById("Guardar").disabled = false;
                ponerFoco("nombre");
            }
        });
    }
});

function AgregarEstado(url, datos) {
    $.ajax({
        url:url,
        type:'POST',
        data:datos,
        success: function(respuesta) {
            if ($.trim(respuesta) == 1) {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Operación completa.',
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        document.location.href = 'Estado';
                    }
                });
            }
            else if ($.trim(respuesta) == 2) {   
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No se pudo realizar la operación.',
                    type: 'error',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            }
            else {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Nombre de estado existente.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                        ponerFoco("nombre");
                    }
                });
            }
        }
    });
}

function validarInfo(datos) {
    var info = JSON.parse(datos);
    var msj = "";
    
    if(info.nombre == "")
        return msj="Complete el campo nombre.";
    
    if (info.nombre.length > 50)
        msj = "El campo nombre supera<br> la longitud permitida.";
        
    return msj;
}

function ponerFoco(id) {
    var obj = $("#"+id);
    var valor = obj.val();
    obj.focus().val("").val(valor);
    obj.focus();
}