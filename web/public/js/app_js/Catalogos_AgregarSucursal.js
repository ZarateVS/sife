$(document).ready(function(){
    $("#sucursal").focus();
});

$("#apertura").click(function(){
   //alert("Ready");
});

$("#apertura").datepicker({
    format: "yyyy-mm-dd",
    startView: 0,
    language: "es",
    autoclose: true
});

$("#Limpiar").click(function(){
   $(".form-control").each(function (){
       $(this).val("");
   });
   $("#sucursal").focus();
});

$("#Guardar").click(function() {
    document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmSucursal").serializeObject());
    //datos = cambiarFormatoFecha(datos);
    var msj = validarInfo(datos);
    if (msj == "")
        AgregarSucursal("AgregarSucursal", datos);
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

function cambiarFormatoFecha(datos){ // De -> DD/MM/YYYY - A -> YYYY-MM-DD
    var info = JSON.parse(datos);
    info.apertura = info.apertura.replace(/^(\d{2})[/](\d{2})[/](\d{4})$/g,'$3-$2-$1');
    datos = JSON.stringify(info);
    return datos;
}

function formato2(texto){ // De -> YYYY-MM-DD - A -> DD/MM/YYYY
    return texto.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$3/$2/$1');
}

function validarFecha(fch) {
    exp = /^([0-2][0-9]|3[0-1])(\/|-)(0[1-9]|1[0-2])\2(\d{4})$/;
    if(exp.test(fch))
        return 0;
    else
        return 1;
}

function AgregarSucursal(url, datos) {
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
                        document.location.href = "Sucursal";
                    }
                });
            } else if ($.trim(respuesta) == 2) {
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
                    text: 'La clave de sucursal ya existe <br> y se encuentra activa.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                        $("#sucursal").focus();
                    }
                });
            }
        }
    });
}

function cambiarMinusculas(e) {
    e.value = e.value.toUpperCase();
}

function Mayusculas() {
    valor = $("#sucursal").val();
    $("#sucursal").val(valor.toUpperCase());
}

function validarCamposVacios() {
    var cont = 0;
    $(".form-control").each(function () {
        var x = $(this).val();
        if (x === "") {
            //$(this).focus();
            cont++;
        }
    });
    return cont;
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

function validarCadenas(cadenas, longs) {
    var cont = 0;
    // Evaluar longitud Varchar
    for (var i = 0; i < cadenas.length; i++) {
        if (cadenas[i].length > longs[i])
            cont++; // No Valido 0
    }
    return cont;
}

function validarInfo(datos)
{
    var info = JSON.parse(datos);
    var msj = "";
    
    var cadenas = [info.sucursal, info.nombre, info.tipo, info.apertura];
    var longs = [6, 100, 1, 10];
    
    if (validarCamposVacios() !== 0)
        return msj = "Complete todos los campos.";
    
    if (validarEnteros(info.rs) !== 0)
        msj = "Valor de razoón social no valido.";
    
    if (validarCadenas(cadenas, longs) !== 0)
        msj = "Un campo del formulario supera la longitud permitida.";
    
    return msj;
}