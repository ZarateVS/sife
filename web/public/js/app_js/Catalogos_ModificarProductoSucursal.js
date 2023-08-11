$(document).ready(function(){
    $('#frmProducto').find('input, button, select').prop('disabled', true);
    $('.selectpicker').selectpicker({
        size: 6
    });
});

$("#Guardar").click(function() {
    document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmProducto").serializeObject());
    var msj = validarInfo(datos);
    if (!msj) {
        //alert("Operación completa.");
        var url_envio = "ModificarProducto";
        var url_catalogo = "ProductosSucursales";
        ModificarProducto(url_envio, url_catalogo, datos);
    }
    else {
        $().toastmessage('showToast', {
            sticky: true,
            text: msj,
            type: 'warning',
            position: 'middle-center',
            close: function () {
                document.getElementById("Guardar").disabled = false;
            }
        });
    }
});

function ModificarProducto(url_envio, url_catalogo, datos) {
    $.ajax({
        url: url_envio,
        type: 'POST',
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
        success: function (respuesta) {
            if ($.trim(respuesta) == 1) {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Operación completa.',
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        document.location.href = url_catalogo;
                    }
                });
            } else {
                if ($.trim(respuesta) != 3) {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: respuesta+' de producto duplicado.',
                        type: 'warning',
                        position: 'middle-center',
                        close: function () {
                            document.getElementById("Guardar").disabled = false;
                            if (respuesta == "Nombre")
                                $("#nombre").focus();
                            else
                                $("#codigo").focus();
                        }
                    });
                } else {
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
            }
        }
    }).always(function () {
        $.unblockUI();
    });
}

$("#Editar").click(function() {
    $('#frmProducto').find('input, button, select').prop('disabled', false);
    $('.selectpicker').selectpicker('refresh');
    document.getElementById("Guardar").style.display = "block";
});

function SoloNumeros(e, n) {
    var key = e.keyCode || e.which;
    var teclado = String.fromCharCode(key);
    var permitidos = (n == 0) ? "0123456789" : "0123456789." ;
        
    if (permitidos.indexOf(teclado) == -1)
        return false;
        
    if (n == 1)
        return verificarPuntoyDecimales(e);
}

function verificarPuntoyDecimales(e) {
    var teclaPulsada = window.event ? window.event.keyCode : e.which;
    var valor = document.getElementById("precio").value;
    if (valor.indexOf(".") != -1) { // Si hay un punto
        if (teclaPulsada == 46)
            return false;
        return true;
    }
    if (valor.length == 12 && teclaPulsada != 46) {
        return false;
    }
    return true;
}

function validarCamposVacios(campos) {
    var cont = 0;
    for (var i in campos) {
        if (campos[i] == "")
            cont++;
    }
    return cont;
}

function validarDecimal(num) {
    if (num.indexOf(".") != -1) {
        var decimal = num.substring(num.indexOf('.')+1, num.length); // obtiene la cadena despues del punto decimal
    
        if (decimal.length > 2) // Si tiene mas de dos decimales
            return 1; // No valido
        else
            return 0; // Valido
    }
    return 0;
}

function validarInfo(datos) {
    var msj = "";
    var campos = JSON.parse(datos);
    
    var codigo = $("#codigo").val();
    var nombre = $("#nombre").val();
    var unidad = $("#unidad").val();
    var precio = $("#precio").val();
    var iva = $("#iva").val();
    
    if (validarCamposVacios(campos))
        return "Complete todos los campos.";
    
    if (codigo.length > 50 || nombre.length > 180 || unidad.length > 50)
        msj = "Un campo del formulario supera la longitud permitida.";
    
    if (precio < 0 || validarDecimal(precio) == 1)
        msj = "El precio debe ser un valor positivo con máximo dos decimales.";
    
    if (iva < 0 || iva.indexOf(".") != -1)
        msj = "El IVA debe ser un valor entero positivo.";
    
    return msj;
}