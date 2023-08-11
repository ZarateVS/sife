$("#Guardar").click(function() {
    var datos = JSON.stringify($("#frmEmisor").serializeObject());
    alert("Original listo");
    //var msj = validarInfo(datos);
    //alert(msj);
    
    //$("#telefono, #num_int, #ciudad").removeClass(".form-control");
    //$("#num_int").removeClass(".form-control");
    //$("#ciudad").removeClass(".form-control");
    /*
    errores = ValidarCamposVacios();
    if (errores === 0) {
        alert("Operacion completa.");
    
    $.ajax({
       url:"ModificarEmisor",
       type:"POST",
       data:datos,
       success: function (respuesta) {
           if (respuesta == 1) {
               alert("Operacion completa.");
               //document.location.href = 'Emisor';
           }
           else
               alert(respuesta);
       }
    });
    
    }
    else
        alert("Complete los campos requeridos");
    */
});

/*
$("#colonias").on("change", '#colonia', function() {
    var colonia = $(this).val();
    LlenarCampos(colonia, 3); // Llenar ciudad
});

$( "#cp" ).change(function() {
    var cp = $(this).val();
    $("#ciudad").val("");
    if (cp !== "" && !isNaN(cp)) {
        LlenarCampos(cp, 1); // Llenar estado y municipio
    }
    else {
        $("#estado, #municipio").val("");
        cp = "";
    }
    LlenarCampos(cp, 2); // Llenar colonias
});

$("#estado").change(function() {
    var edo = $(this).val();
    LlenarCampos(edo, 4);
});
*/

$("#municipios").on("change", '#municipio', function() { // Obtiene Estado y Colonias
    var id = $(this).val();
    $("#ciudad").val("");
    var r = "";
    r = LlenarCampos("LlenarCampos", id, 2); // ObtenerEstadoPorMunicipio
    var info = JSON.parse(r);
    $("#estado").val(info.id_edo);
    r = LlenarCampos("LlenarCampos", id, 3); // ObtenerColoniasPorMunicipio
    $("#colonias").html(r);
});

$("#colonias").on("change", '#colonia', function() { // Obtiene la ciudad
    var id = $(this).val();
    if (id != "") {
        var r = LlenarCampos("LlenarCampos", id, 4); // ObtenerCiudad
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
    r = LlenarCampos("LlenarCampos", "", 7); // ObtenerMunicipios
    $("#municipio").html(r);
    if (id !== "" && !isNaN(id)) {
        r = LlenarCampos("LlenarCampos", id, 5); // ObtenerEdoMpio
        var info = JSON.parse(r);
        $("#estado").val(info.id_edo);
        $("#municipio").val(info.id_mpio);
    }
    else {
        $("#estado, #municipio").val("");
        id = "";
    }
    r = LlenarCampos("LlenarCampos", id, 6); // ObtenerColoniasPorCp
    $("#colonias").html(r);
});

$("#estado").change(function() { // Obtiene Municipios y Vacia Colonias
    var id = $(this).val();
    $("#ciudad").val("");
    var r = "";
    if (id == "") {
        r = LlenarCampos("LlenarCampos", id, 7); // ObtenerMunicipios
        $("#municipios").html(r);
    }
    else {
        r = LlenarCampos("LlenarCampos", id, 1); // ObtenerMunicipiosPorEstado
        $("#municipios").html(r);
    }
    r = LlenarCampos("LlenarCampos", "", 3); // ObtenerColoniasPorMunicipio
    $("#colonias").html(r);
});

$("input#tipo_p").click(function() {
    var tp = $(this).val();
    var r = LlenarCampos("LlenarCampos", tp, 8);
    $("#regs_fiscales").html(r);
});

function LlenarCampos(url, id, fn)
{
    var respuesta="";
    $.ajax({
        async: false,
        url:url,
        type:'POST',
        data:'id='+id+'&fn='+fn,
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

function validarCamposVacios()
{
    var cont = 0;
    var indice = 0;
    $(".form-control").each(function () {
        indice++;
        // Los indices 5,8 y 13 no son campos requeridos
        if (indice != 5 && indice != 8 && indice != 13) { // Si indice es diferente de 5,8 y 13
            var x = $(this).val();
            if (x === "") {
                $(this).focus();
                cont++;
            }
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
            if (enteros[i] % 1 !== 0 && enteros[i] <= 0)
                cont++; // No Valido 0
        }
    }
    return cont;
}

function validarCadenas(cadenas, longs) {
    var cont = 0;
    // Evaluar longitud Varchar
    for (var i = 0; i < cadenas.length; i++) {
        if (cadenas[i].length <= 0 || cadenas[i].length > longs[i])
            cont++; // No Valido 0
    }
    return cont;
}

function validarInfo (datos) {
    var info = JSON.parse(datos);

    var cadenas = [info.reg_fiscal, info.rs, info.rfc, info.correo, info.tel, info.calle, info.num_ext, info.num_int, info.cp, info.ciudad];
    var enteros = [info.id_cliente, info.tipo_p, info.estado, info.municipio, info.reg_fiscal, info.colonia, info.tel, info.cp];
    var longs = [10, 120, 13, 100, 15, 100, 10, 10, 10, 50];
    var msj = "Informacion Correcta";
    
    if (validarEnteros(enteros) !== 0)
        msj = "Enteros no validos";
    
    if (validarCadenas(cadenas, longs) !== 0)
        msj = "Cadenas no validos";
    
    if (validarRfc(info.rfc, info.tipo_p) !== 0)
        msj = "RFC no valido";
    
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
        return "1";
    else
        return "0";
}