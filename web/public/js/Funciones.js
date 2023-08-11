function reiniciar() {
    $.when( empezarDetener('Detener') ).then(function() {
        empezarDetener('Empezar');
    });
}

function Limpiarcampos() {
    $(".form-control")[0].reset();
    /*$("input[type=text]").each(function () {
        $(this).val('');
    });

    $("select").each(function () {
        this.selectedIndex = 0;
    });*/
}

function Habilitarcampos() {
    $("#Editar").addClass('disabled');
    $(".form-control").each(function(){
        $(this).attr('readonly', false);
        $(this).attr('disabled', false);
    });
    $("#Agregar").removeClass('hidden');
    $("#Agregar").attr('disabled', false);
    $("a#Agregar").removeClass('hidden');
}

function Mostrarerror(){
    var cont = 0;
    var indice = 0;
    var error = "#error";
    $(".form-control").each(function () {
        var x = $(this).val();
        indice++;
        if (x === "0" || x === "") {
            $(this).focus();
            cont++;
            res = error.concat(indice);
            $(res).show('slow');
        }
    });
    return cont;
}

function Mostrarerrorform(formulario){
    var cont = 0;
    var indice = 0;
    var error = "#error";
    $("#"+formulario+" .form-control").each(function () {
        var x = $(this).val();
        indice++;
        if (x === "0" || x === "") {
            $(this).focus();
            cont++;
            res = error.concat(indice);
            $("#"+formulario+" "+ res).show('slow');
        }
    });
    return cont;
}

function Ocultarerror(diverror){
    $(diverror).hide('slow');
}

/*************************** FUNCIÓN CONVERTIR JSON **********************************/

$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function(){
        if(o[this.name] !== undefined) {
            if(!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '')
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

/*************************** FUNCIÓN VALIDAR DATOS **********************************/

function validarEmail(email) {
    expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (expr.test(email))
        return "1";
    else
        return "0";
}

function validarCurp(curp) {
    expr = /[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9][0-9]/;
    if (expr.test(curp))
        return "1";
    else
        return "0";
}

function validarRfc(rfc) {
    expr = /^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/;
    if (expr.test(rfc))
        return "1";
    else
        return "0";
}

function validarTel(tel) {
    expr = /[0-9]{10}/;
    if (expr.test(tel))
        return "1";
    else
        return "0";
}

function validarNSS(nss){
    if (document.getElementById("empleado_nss").value.length === 11)
        return "1";
    else
        return "0";
}

function validarEdad(fecha) {
    var values = fecha.split("-");
    var dia = values[2];
    var mes = values[1];
    var ano = values[0];
    var fecha_hoy = new Date();
    var ahora_ano = fecha_hoy.getYear();
    var ahora_mes = fecha_hoy.getMonth() + 1;
    var ahora_dia = fecha_hoy.getDate();
    var edad = (ahora_ano + 1900) - ano;
    if (ahora_mes < mes) {
        edad--;
    }
    if ((mes == ahora_mes) && (ahora_dia < dia)) {
        edad--;
    }
    if (edad > 1900) {
        edad -= 1900;
    }
    if (edad > 15)
        return "1";
    else
        return "0";
}

/*************************** FUNCIÓN LLENA SELECTS *****************************************/

//Parametros: identificador SQL, url a ejecutar, identificador de select a llenar
function LlenaSelect(id, url, selectnombre) {
    var idselect = "#"+selectnombre;
    $.ajax({
        data: 'id='+id, url: url, type: 'POST', dataType: 'json',
        beforeSend: function () {
            $(idselect).attr('disabled', true);
            $(idselect).append('<option value="0">Cargando. Por favor espere</option>');
        },
        success: function (data) {
            $(idselect).attr('disabled', false);
            $(idselect).find('option').remove();
            if (data.length == 0) {
                $(idselect).append('<option value="0" selected>Sin resultados para mostrar</option>');
            } else {
                $(idselect).append('<option value="0" selected>Seleccione una opción</option>');
                $(data).each(function (i, j) {
                    $(idselect).append('<option value="' + j.valorid + '">' + j.valornombre + '</option>');
                });
            }
        },
        error: function () {
            $(idselect).append('<option value="0">Ocurrio un error con el servidor</option>');
        }
    });
}

/*************************** FUNCIÓN CARGAR CONTENIDO PARA MODIFICAR *****************************************/

function Update(url, id) {
    $.ajax({
        url: url,
        type: "POST",
        data: "id="+id,
        success: function (respuesta) {
            $("#contenido").html(respuesta);
        },
        error: function () {
            $().toastmessage('showToast', {
                sticky: true,
                text: 'Error al obtener datos.',
                type: 'error',
                position: 'middle-center'
            });
        }
    });
}

/*************************** FUNCIÓN CARGAR CP *****************************************/

function CargarCP(id, url) {
    $.ajax({
        url: url,
        data: "id=" + id,
        type: "POST",
        success: function (data) {
            if(data.length > 0)
                $("#empleado_codigo").val(data[0].cp);
            else
                $("#empleado_codigo").val("CP no dis.");
        }
    });
}

/*************************** FUNCIÓN CARGAR SELECTS COLONIA *****************************************/

function CargaColonia(cp) {
    if(cp == "") {
        $().toastmessage('showToast', {
            sticky: true,
            text: 'Proporcione el código postal.',
            type: 'warning',
            position: 'middle-center'
        });
    } else {
        var datos = [];
        $.ajax({
            data: 'cp='+cp, url: "CargarselectCP", type: 'POST', dataType: 'json',
                success: function (data) {
                if($.trim(data) == "0"){
                    $(".alert").fadeIn(400, function () {
                        setTimeout(function(){
                            $(".alert").fadeOut(400);
                        }, 4000);
                    });
                } else {
                    datos = data;
                    $("#empleado_pais").val(datos[0].id_pais);
                    $.ajax({
                        url: "ObtenerEstadosSelect", data: "id="+datos[0].id_pais, type: "POST",
                        success: function(data){
                            $.when($("#empleado_estado").html(data)).then(function(){
                                $("#empleado_estado").val(datos[0].id_estado);
                                $.ajax({
                                    url: "ObtenerMunicipiosSelect", data: "id="+datos[0].id_estado, type: "POST",
                                    success: function(data){
                                        $.when($("#empleado_municipio").html(data)).then(function(){
                                            $("#empleado_municipio").val(datos[0].id_municipio);
                                            $.ajax({
                                                url: "ObtenerCiudadSelect", data: "id="+datos[0].id_municipio, type: "POST",
                                                success: function(data){
                                                    $.when($("#empleado_ciudad").html(data)).then(function(){
                                                        $("#empleado_ciudad").val(datos[0].id_ciudad);
                                                        $.ajax({
                                                            url: "ObtenerColoniasSelect", data: "id="+datos[0].id_ciudad, type: "POST",
                                                            success: function(data){
                                                                $.when($("#empleado_colonia").html(data)).then(function(){
                                                                    $("#empleado_colonia").val(datos[0].id_colonia);
                                                                });
                                                            }
                                                        });
                                                    });
                                                }
                                            });
                                        });
                                    }
                                });
                            });
                        }
                    });
                }
            },
            error: function () {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No se pudo realizar la operación.',
                    type: 'error',
                    position: 'middle-center'
                });
            }
        });
    }
}
