$(document).ready(function(){
    //LlenarSelect("LlenarSelectsProductosSucursales");
    $("#codigo").focus();
    $('.selectpicker').selectpicker({
        size: 6
    });
});
    
/*$('#cve_unidad').selectpicker({
  size: 6
});*/

/*$('#unidad').selectpicker({
  size: 6
});*/

/*function LlenarSelect(url) {
    $.ajax({
        url:url,
        type:'POST',
        data:'',
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
            $("#ps").html(respuesta);
        }
    }).always(function () {
            $.unblockUI();
    });
}*/

$("#Limpiar").click(function(){
   $(".form-control").each(function (){
       $(this).val("");
   });
   $('.selectpicker').selectpicker('refresh');
   $("#codigo").focus();
});

$("#Guardar").click(function() {
    document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmProducto").serializeObject());
    var msj = validarInfo(datos);
    if (!msj) {
        //alert("Operación completa.");
        var url_envio = "AgregarProducto";
        var url_catalogo = "ProductosSucursales";
        AgregarProducto(url_envio, url_catalogo, datos);
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

function AgregarProducto(url_envio, url_catalogo, datos) {
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
        success: function(respuesta) {
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
                    if ($.trim(respuesta) == 2) {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'Código o nombre de producto duplicado.',
                        type: 'warning',
                        position: 'middle-center',
                        close: function () {
                            document.getElementById("Guardar").disabled = false;
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
    var teclaPulsada=window.event ? window.event.keyCode:e.which;
    var valor = document.getElementById("precio").value;
    if (valor.indexOf(".") != -1) {// Si hay un punto
        if (teclaPulsada==46)
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
    exp = /^\d+(\.\d{1,2})?$/;
    if (num.length >= 1 && num.length <= 15 && exp.test(num)) {
        if (num.indexOf(".") != -1) {
            var decimal = num.substring(num.indexOf('.')+1, num.length); // obtiene la cadena despues del punto decimal
            if (decimal.length > 2) // Si tiene mas de dos decimales
                return 1; // No valido
            else
                return 0; // Valido
        }
        else
            return 0;
    }
    else 
        return 1;
}

/*function ValidarUnidad (valor) {
    var unidad = ['Metro', 'Kilo', 'Hora', 'Dia', 'Litro', 'Botella', 'Piezas', 'Caja', 's/u', 'No aplica'];
    for(var i=0; i<10; i++) {
        if (unidad[i] == valor) {
            return 0;
        }
    }
    return 1;
}*/

function validarInfo(datos)
{
    var msj = "";
    var campos = JSON.parse(datos);
    
    var codigo = $("#codigo").val();
    var nombre = $("#nombre").val();
    var unidad = $("#unidad").val();
    var precio = $("#precio").val();
    var cve_ps = $("#cve_prodserv").val();
    var cve_un = $("#cve_unidad").val();
    var iva = $("#iva").val();
    
    if (validarCamposVacios(campos))
        return "Complete todos los campos.";
    
    if (codigo.length > 50 || nombre.length > 180 || unidad.length > 50 || cve_ps.length > 10 || cve_un.length > 10)
        msj = "Un campo del formulario supera la longitud permitida.";
    
    if (precio < 0 || validarDecimal(precio) == 1)
        msj = "El precio debe ser un valor positivo con máximo dos decimales.";
    
    if (iva < 0 || iva.indexOf(".") != -1)
        msj = "El IVA debe ser un valor entero positivo.";
    
    /*if (ValidarUnidad(unidad) == 1)
        return msj = "Unidad no valida";*/
    
    return msj;
}