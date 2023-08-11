$(document).ready(function() {
    $('#nombre').focus();
    $('.selectpicker').selectpicker({
        size: 8,
        language: 'ES'
    });
    $('.selectpicker').selectpicker('refresh');
});

$("#Limpiar").click(function() {
    $(".form-control").each(function (){
        $(this).val("");
    });
    $("#nombre").focus();
    vaciarSelect('municipio');
    $('.selectpicker').selectpicker('refresh');
});

$("#estado").change(function() {
    var id = $(this).val();
    $.ajax({
        async: false,
        url: 'ObtenerMunicipios',
        type: 'POST',
        data: 'id='+id,
        success: function(r) {
            var info = JSON.parse(r);
            vaciarSelect('municipio');
            llenarSelect(info, 'municipio');
        }
    });
    $('.selectpicker').selectpicker('refresh');
});

function vaciarSelect(id) {
    var s = document.getElementById(id);
    s.options.length = 0;
    s.options[0] = new Option("-- Seleccione --", "");
}

function llenarSelect(info, id) {
    var s = document.getElementById(id);
    for (var i = 0; i < info.length; i++) {
        s.options[i+1] = new Option(info[i].municipio, info[i].id_municipio);
    }
}

$("#Guardar").click(function() {
    document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmColonia").serializeObject());
    var msj = validarInfo(datos);
    if (msj == "") {
        AgregarColonia("AgregarColonia", datos);
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

function AgregarColonia(url, datos) {
    $.ajax({
        url: url,
        type: 'POST',
        data: datos,
        success: function(respuesta) {
            if ($.trim(respuesta) == 1) {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Operación completa.',
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        //document.location.href = 'Colonia';
                        var id = $("#municipio").val();
                        document.location.href='Colonia?id_mpio='+id;
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
                    text: 'Nombre de colonia existente.',
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

function soloNumeros(e)
{
    key = e.keyCode || e.which;
    teclado = String.fromCharCode(key);
    numeros="0123456789";
    if (numeros.indexOf(teclado)==-1)
        return false;
}

function ponerFocoFinalTexto(id) {
    var obj = $("#"+id);
    var valor = obj.val();
    obj.focus().val("").val(valor);
    obj.focus();
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

function camposVacios (campos) {
    var cont = 0;
    for (var i = 0; i < campos.length; i++) {
        if (campos[i] === "")
            cont++;
    }
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

function validarInfo(datos) {
    var info = JSON.parse(datos);
    var cadenas = [info.nombre, info.cd, info.cp];
    var enteros = [info.id_estado, info.id_municipio, info.cp];
    var longs = [100, 100, 10];
    var campos = [info.id_estado, info.id_municipio, info.nombre, info.cp];
    
    var msj = "";
    if (camposVacios(campos) !== 0)
        return "Todos los campos con * son requeridos.";
    
    if (validarCadenas(cadenas, longs) > 0)
        msj = "Un campo del formulario <br>supera la longitud permitida.";
    
    if (validarEnteros(enteros) > 0)
        msj = "El código postal debe ser un <br>valor numérico entero positivo.";
    
    return msj;
}