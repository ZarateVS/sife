$(document).ready(function(){   
   $("#Guardar").addClass("disabled");
});

$("#usuario").change(function () {
    $("#pasar, #pasartodos, #quitar, #quitartodos").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$("#sucs_disp").change(function () {
    $("#pasar, #pasartodos").attr('disabled', false);
});

$("#sucs_asignar").change(function () {
    $("#quitar, #quitartodos").attr('disabled', false);
});

$('#pasar').click(function () {
    $('#sucs_disp').find('option:selected').remove().appendTo('#sucs_asignar');
    $("#sucs_asignar").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#pasartodos").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$('#quitar').click(function () {
    $('#sucs_asignar').find('option:selected').remove().appendTo('#sucs_disp');
    $("#sucs_disp").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#quitartodos").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$('#pasartodos').click(function () {
    $('#sucs_disp').find('option').each(function () {
        $(this).remove().appendTo('#sucs_asignar');
    });
    $("#sucs_asignar").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#pasar").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$('#quitartodos').click(function () {
    $('#sucs_asignar').find('option').each(function () {
        $(this).remove().appendTo('#sucs_disp');
    });
    $("#sucs_disp").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#quitar").attr('disabled', true);
    $("#Guardar").addClass("disabled");
});

$("#Guardar").click(function() {
    $("#Guardar").addClass("disabled");
    $("#sucs_asignar option").prop('selected', true);
    var datos = JSON.stringify($('#frmSucursal').serializeObject());
    var msj = validarInfo(datos);
    if (msj == "") {
        asignarSucursales(datos);
    }
    else {
        $().toastmessage('showToast', {
            sticky: true,
            text: msj,
            type: 'warning',
            position: 'middle-center',
            close: function () {
                $("#Guardar").addClass("disabled");
            }
        });
    }
});

function asignarSucursales(datos) {
    $.ajax({
        url:"AgregarUsuarioSucursal",
        type:"POST",
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
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Operación completa.',
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        document.location.href = "UsuariosSucursal";
                    }
                });
            } else if ($.trim(respuesta) == "2") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Se debe asignar una sucursal <br>como mínimo para guardar.',
                    type: 'notice',
                    position: 'middle-center',
                    close: function () {
                        $("#Guardar").addClass("disabled");
                    }
                });
            }
            else {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No se pudo realizar la operación.',
                    type: 'error',
                    position: 'middle-center',
                    close: function () {
                        $("#Guardar").addClass("disabled");
                    }
                });
            }
        }
    }).always(function () {
        $.unblockUI();
    });
}

function validarEnteros (enteros) {
    var cont = 0;
    for (var i = 0; i < enteros.length; i++) {
        // Evaluar enteros positivos
        // isNaN() = Determina si un valor es un número ilegal. Convierte el valor a evaluar a numérico, luego lo evalua
        if (isNaN(enteros[i])) {
            cont++; // No Valido 0
        }
        else {
            // Si (valor % 1 !== 0), valor no es un numero entero
            if (enteros[i] % 1 != 0 || enteros[i] < 0) {
                cont++; // No Valido 0
            }
        }
    }
    return cont;
}

function validarInfo(datos) {
    var info = JSON.parse(datos);
    var msj = "";
    
    if (!info.sucs_asignar)
        return msj = "Se debe asignar una sucursal <br>como mínimo para guardar.";
    
    if (!info.usuario)
        return msj = "Complete el campo usuario.";
    
    if (validarEnteros(info.sucs_asignar) !== 0 || validarEnteros(info.usuario) !== 0)
        msj = "No se pudo realizar la operación.";
        
    return msj;
}

$("#Limpiar").click(function(){
    $("#Guardar").addClass("disabled");
    document.getElementById("usuario").value = "";
    $("#pasar, #pasartodos").attr('disabled', true);
    $("#quitar, #quitartodos").attr('disabled', true);
    document.getElementById('sucs_asignar').innerHTML = "";
    document.getElementById('sucs_disp').innerHTML = "";
    $('#sucs_disp_r').find('option').each(function () {
        $(this).clone().appendTo('#sucs_disp');
    });
    $("#sucs_disp").find("option:selected").prop("selected", false);
});