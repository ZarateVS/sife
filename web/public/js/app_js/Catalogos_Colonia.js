$(document).ready(function() {
    $('.selectpicker').selectpicker({
        style: 'alert-primary',
        size: 6,
        language: 'ES'
    });
    $('.selectpicker').selectpicker('refresh');
    $('a[data-toggle="tooltip"]').tooltip(); // Muestra etiqueta del boton Exportar
    
    var edo = $("#estado").data('edo');
    var mpio = $("#municipio").data('mpio');
    obtenerMunicipios(edo);
    $("#estado").val(edo);
    $("#municipio").val(mpio);
    llenarTablaColonias('ObtenerColonias', mpio);
    $('.selectpicker').selectpicker('refresh');
    var id_mpio = $("#municipio").val();
    var valor = (id_mpio === "") ? true : false;
    $('#Exportar').prop('disabled', valor);
});

$("#estado").change(function() {
    var id = $(this).val();
    var valor = false;
    var id_mpio = $("#municipio").val();
    
    if (id === "") {
        vaciarSelect('municipio');
        valor = true;
    }
    else
        obtenerMunicipios(id);
        
    if (id_mpio !== "") {
        llenarTablaColonias('ObtenerColonias', "");   
    }
    
    $('#Exportar').prop('disabled', valor);
    $('.selectpicker').selectpicker('refresh');
});

function obtenerMunicipios(id) {
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
}

function vaciarSelect(id) {
    var s = document.getElementById(id);
    s.options.length = 0;
    s.options[0] = new Option("-- Seleccione municipio --", "");
}

function llenarSelect(info, id) {
    var s = document.getElementById(id);
    for (var i = 0; i < info.length; i++) {
        s.options[i+1] = new Option(info[i].municipio, info[i].id_municipio);
    }
}

$("#municipio").change(function(){
    var id = $(this).val();
    llenarTablaColonias('ObtenerColonias', id);
    /*var valor = (id === "") ? true : false;
    $('#Exportar').prop('disabled', valor);*/
});

$("#Exportar").click(function(){
    var id = $("#estado").val();
    if (id) {
        //var id_edo = $("#municipio").find(':selected').data('id_edo');
        var rutaExcel = 'http://localhost/sife/web/app_dev.php/ExportarTabla?id='+id;
        window.open(rutaExcel, '_blank' );
    }
    else {
        $('#Exportar').prop('disabled', true);
    }
});

function llenarTablaColonias(url, id ){
    $.ajax({
        url:url,
        type:'POST',
        data:'id='+id,
        success: function (respuesta) {
            $("#catalogo").html(respuesta);
        }
    });
}