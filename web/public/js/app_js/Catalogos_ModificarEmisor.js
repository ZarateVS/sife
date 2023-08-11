$(document).ready(function(){
    //$('#frmSucursal').find('input, textarea, button, select').attr('disabled','disabled');
    $('#frmEmisor').find('input, button, select').prop('disabled', true);
    $('.selectpicker').selectpicker({
        size: 6,
        language: 'ES'
    });
    $('.selectpicker').selectpicker('refresh');
});

$("#Editar").click(function(){
    $("#frmEmisor").find('input, button, select').prop('disabled', false);
    $("#Guardar").prop('disabled', false);
    ponerFoco("rfc");
    $('.selectpicker').selectpicker('refresh');
    $(this).addClass('disabled');
});

$("#Guardar").click(function() {
    document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmEmisor").serializeObject());
    var msj = validarInfo(datos);
    if (msj == "") {
        //ModificarEmisor('ModificarEmisor', datos);
        alert("Operacion completa");
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

function ModificarEmisor (url, datos) {
    $.ajax({
        url: url,
        type: 'POST',
        data: datos,
        success: function (respuesta) {
            if ($.trim(respuesta) == 1) {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Operación completa.',
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        document.location.href = 'Emisor';
                        //document.getElementById("Guardar").disabled = false;
                    }
                });
            }
            else {
                if ($.trim(respuesta) == 2) {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'Nombre o razón social duplicada',
                        type: 'warning',
                        position: 'middle-center',
                        close: function () {
                            document.getElementById("Guardar").disabled = false;
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
                            document.getElementById("Guardar").disabled = false;
                        }
                    });
                }
            }
        }
    });
}

function LlenarSelect (id, r) {
    var info = JSON.parse(r);
    var s = document.getElementById(id);
    s.options.length = 0;
    s.options[0] = new Option("-- Seleccione --", "");
    switch(id) {
        case 'municipio':
            for (var i = 0; i < info.length; i++) {
                s.options[i+1] = new Option(info[i].municipio, info[i].id_municipio);
            }
            break;
        case 'colonia':
            for (var i = 0; i < info.length; i++) {
                s.options[i+1] = new Option(info[i].colonia, info[i].id_colonia);
            }
            break;
        case 'reg_fiscal':
            for (var i = 0; i < info.length; i++) {
                s.options[i+1] = new Option(info[i].descripcion, info[i].regimenFiscal);
            }
            break;
        default:
            break;
    }
    $('.selectpicker').selectpicker('refresh');
}

$("#municipio").change(function() { // Obtiene Estado y Colonias
    var id = $(this).val();
    $("#ciudad").val("");
    var r = "";
    var info = "";
    r = obtenerInfoCampos("ObtenerInfoCampos", id, 2); // ObtenerEstadoPorMunicipio
    info = JSON.parse(r);
    $("#estado").val(info.id_estado);
    
    r = obtenerInfoCampos("ObtenerInfoCampos", id, 3); // ObtenerColoniasPorMunicipio
    LlenarSelect('colonia', r);
});

$("#colonia").change(function() { // Obtiene la ciudad
    var id = $(this).val();
    if (id != "") {
        var r = obtenerInfoCampos("ObtenerInfoCampos", id, 4); // ObtenerCiudad
        var info = JSON.parse(r);
        $("#ciudad").val(info.ciudad);
        $("#cp").val(info.codigoPostal);
    }
    else {
        $("#ciudad, #cp").val("");
    }
});

$( "#cp" ).change(function() { // Obtiene Municipios, Edo y Mpio y Colonias
    var id = $(this).val();
    $("#ciudad").val("");
    var  r = "";
    r = obtenerInfoCampos("ObtenerInfoCampos", "", 7); // ObtenerMunicipios
    LlenarSelect('municipio', r);
    if (id !== "" && !isNaN(id)) {
        r = obtenerInfoCampos("ObtenerInfoCampos", id, 5); // ObtenerEdoMpio
        var info = JSON.parse(r);
        $("#estado").val(info.id_edo);
        $("#municipio").val(info.id_mpio);
    }
    else {
        $("#estado, #municipio").val("");
        id = "";
    }
    r = obtenerInfoCampos("ObtenerInfoCampos", id, 6); // ObtenerColoniasPorCp
    LlenarSelect('colonia', r);
});

$("#estado").change(function() { // Obtiene Municipios y Vacia Colonias
    var id = $(this).val();
    $("#ciudad").val("");
    var r = "";
    if (id == "")
        r = obtenerInfoCampos("ObtenerInfoCampos", id, 7); // ObtenerMunicipios
    else
        r = obtenerInfoCampos("ObtenerInfoCampos", id, 1); // ObtenerMunicipiosPorEstado
    LlenarSelect('municipio', r);
    var s = document.getElementById("colonia");
    s.options.length = 0;
    s.options[0] = new Option("-- Seleccione --", "");
    $('.selectpicker').selectpicker('refresh');
});

$("input#tipo_p").click(function(){
    var tp = $(this).val();
    var r = obtenerInfoCampos("ObtenerInfoCampos", tp, 8);
    LlenarSelect('reg_fiscal', r);
});

function obtenerInfoCampos(url, id, fn) {
    var respuesta = "";
    $.ajax({
        async: false,
        url: url,
        type: 'POST',
        data: 'id='+id+'&fn='+fn,
        success: function(r) {
            respuesta = r;
        }
    });
    return respuesta;
}

function validarRfc(rfc, tipo_p) {
    // 0 = Persona Moral, 1 = Persona Fisica
    if (tipo_p === "0")
        expr = /^([A-Z,Ñ,&]{3}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/; // RFC Persona Moral
    else 
        expr = /^([A-Z,Ñ,&]{4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/; // RFC Persona Fisica
    
    if (expr.test(rfc))
        return 0; // Valido 0
    else
        return 1; // No Valido 1
}

function validarCamposVacios(campos) {
    var cont = 0;
    for (var i in campos) {
        if (campos[i] == "")
            cont++;
    }
    return cont;
}

function validarEnteros (enteros) {
    var cont = 0;
    for (var i = 0; i < enteros.length; i++) {
        // Evaluar enteros positivos
        // isNaN() = Determina si un valor es un número. Convierte el valor a evaluar a numérico, luego lo evalua
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

function validarInfo (datos) {
    var info = JSON.parse(datos);

    var cadenas = [info.reg_fiscal, info.rs, info.rfc, info.correo, info.tel, info.calle, info.num_ext, info.num_int, info.cp, info.ciudad];
    var enteros = [info.id_cliente, info.tipo_p, info.estado, info.municipio, info.reg_fiscal, info.colonia, info.tel, info.cp];
    var longs = [10, 120, 13, 100, 15, 100, 10, 10, 10, 50];
    var requeridos = [info.reg_fiscal, info.tipo_p, info.rfc, info.rs, info.correo, info.calle, info.num_ext, info.cp, info.estado, info.municipio, info.colonia];
    var msj = "";
    
    if (validarCamposVacios(requeridos) !== 0)
        return "Todos los campos con * son requeridos.";
    
    if (validarEnteros(enteros) !== 0)
        msj = "El teléfono y código postal deben ser números enteros positivos.";
    
    if (validarCadenas(cadenas, longs) !== 0)
        msj = "Un campo del formulario supera la longitud permitida.";
    
    if (validarCorreo(info.correo) !== 0)
        msj = "Correo no valido.";
    
    if (validarRfc(info.rfc, info.tipo_p) !== 0)
        msj = "RFC no valido.";
    
    return msj;
}

function soloNumeros(e)
{
    key = e.keyCode || e.which;
    teclado = String.fromCharCode(key);
    numeros="0123456789";
    if (numeros.indexOf(teclado)==-1)
        return false;
}

function validarCorreo(correo) {
    expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (expr.test(correo))
        return 0;
    else
        return 1;
}

function ponerFoco(id) {
    var obj = $("#"+id);
    var valor = obj.val();
    obj.focus().val("").val(valor);
    obj.focus();
}