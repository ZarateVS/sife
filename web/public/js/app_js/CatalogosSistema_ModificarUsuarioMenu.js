$(document).ready(function(){   
   $("#Guardar").addClass("disabled");
});

$("#menus_disp").change(function () {
    $("#pasar, #pasartodos").attr('disabled', false);
});

$("#menus_asignar").change(function () {
    $("#quitar, #quitartodos").attr('disabled', false);
});

$('#pasar').click(function () {
    $('#menus_disp').find('option:selected').remove().appendTo('#menus_asignar');
    $("#menus_asignar").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#pasartodos").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$('#quitar').click(function () {
    $('#menus_asignar').find('option:selected').remove().appendTo('#menus_disp');
    $("#menus_disp").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#quitartodos").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$('#pasartodos').click(function () {
    $('#menus_disp').find('option').each(function () {
        $(this).remove().appendTo('#menus_asignar');
    });
    $("#menus_asignar").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#pasar").attr('disabled', true);
    $("#Guardar").removeClass("disabled");
});

$('#quitartodos').click(function () {
    $('#menus_asignar').find('option').each(function () {
        $(this).remove().appendTo('#menus_disp');
    });
    $("#menus_disp").find("option:selected").prop("selected", false);
    $(this).attr('disabled', true);
    $("#quitar").attr('disabled', true);
    $("#Guardar").addClass("disabled");
});

$("#Guardar").click(function(){
    $("#menus_asignar option").prop('selected', true);
    $("#menus_disp option").prop('selected', true);
    var datos = JSON.stringify($('#frmMenu').serializeObject());
    $("#Guardar").addClass("disabled");
    $.ajax({
        url:"ModificarUsuarioMenu",
        data: datos,
        type:"POST",
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
                    text: "Operación completa.",
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        document.location.href = "UsuariosMenu";
                    }
                });
            }
            else {
                if($.trim(respuesta) == "2") {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'Se debe asignar un menú como mínimo para guardar.',
                        type: 'notice',
                        position: 'middle-center',
                        close: function () {
                            $("#Guardar").removeClass("disabled");
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
                            $("#Guardar").removeClass("disabled");
                        }
                    });
                }
            }
            
        }
    }).always(function () {
            $.unblockUI();
    });
});

$("#Limpiar").click(function(){
    $("#Guardar").addClass("disabled");
    $("#pasar, #pasartodos").attr('disabled', true);
    $("#quitar, #quitartodos").attr('disabled', true);
    document.getElementById('menus_asignar').innerHTML = "";
    document.getElementById('menus_disp').innerHTML = "";
    $('#menus_disp_r').find('option').each(function () {
        $(this).clone().appendTo('#menus_disp');
    });
    $("#menus_disp").find("option:selected").prop("selected", false);
    $('#menus_asignar_r').find('option').each(function () {
        $(this).clone().appendTo('#menus_asignar');
    });
    $("#menus_asignar").find("option:selected").prop("selected", false);
});