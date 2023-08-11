var itemSel = "";
var tipoDoc = "";
$(document).ready(function() {
    $("#fctmnldet").load("ItemsFactura");
    $("#AgrProd").addClass("disabled");

    tipoDoc = $("#serie").find(':selected').data('ts');
    if (tipoDoc == '2')
        $("#cfdiR").show();
    
    $('.selectpicker').selectpicker({
        size: 6
    });
    
    var serie = $("#serie").val();
    if (serie != 0) {
        
    }
    
    var recep = $("#recep").val();
    if (recep != 0 && recep != undefined) {
        var info = {'recep':recep,'fn':'getUsoCfdi'};
        var r = AccionesFactura(info);
        info = JSON.parse(r);
        llenarSelectUsoCfdi(info, 'uso_cfdi');
    }

    var usocfdi_temp = $("#usocfdi_temp").val();
    $("#uso_cfdi").val(usocfdi_temp);
    $("#uso_cfdi_temp").val("");
    $("#serieR").change();

    var fp = $("#formaPago").val();
    if (fp != '01' && fp != '99' && fp != '0')
        $("#no_cta").prop('disabled', false);
 
    var uuid_r = $("#uuid_r").val();
    if (uuid_r != "" && uuid_r != undefined) {
        $("#cfdiR").show();
        $("#folio").prop('disabled', false);
        var info = {'uuid_r':uuid_r, 'fn':'getCfdiR'};
        ObtenerDatosCfdiR(info);
    }
    
    $('.selectpicker').selectpicker('refresh');
});

$("#fch").datepicker({
    minDate: -3,
    maxDate : 0,
    changeMonth: true,
    changeYear: true,
    dateFormat: 'yy-mm-dd'
});

function vaciarSelect(id) {
    var s = document.getElementById(id);
    s.options.length = 0;
    s.options[0] = new Option("-- Seleccione --", "0");
}

function llenarSelectUsoCfdi(info, id) {
    var s = document.getElementById(id);
    for (var i = 0; i < info.length; i++) {
        s.options[i+1] = new Option(info[i].descripcion, info[i].usocfdi);
    }
}

$("#serieR").change(function () {
    var serie = $(this).val();
    var folio = document.getElementById('folio');
    //var valor = ( serie != 0 ) ? false : true;
    var valor = true;
    if (serie != "") {
        valor = false;
    }
    else {
        folio.value = "";
        //$("#monto").val(monto);
        //$("#recepR").val(recep);
    }
    folio.value = "";
    $("#suc, #monto, #recepR").val("");
    folio.disabled = valor;
});

$("#folio").change(function () {
    var folio = $(this).val();
    if (folio != "") {
        var serie = $("#serieR").val();
        var info = {'serie':serie, 'folio':folio, 'fn':'getCfdiR'};
        ObtenerDatosCfdiR(info);
    }
});

function ObtenerDatosCfdiR(info) {
    var r = AccionesFactura(info);
    info = JSON.parse(r);
    var suc = "", monto = "", recep = "", serie = "", folio = "";
    var msj = "";
    switch(info) {
        case 'SD':
            msj = "No se pudo obtener informacion del folio.";
            break;
        case 'CN':
            msj = "Ya existe una nota de credito para este folio.";
            break;
        case 'FI':
            msj = "El folio no esta timbrado.";
            break;
    }
    
    if (msj != "") {
        //$("#folio, #serieR, #Limpiar").prop('disabled', true);
        $().toastmessage('showToast', {
            sticky: true,
            text: msj,
            type: 'warning',
            position: 'middle-center',
            close: function () {
                $("#folio, #serieR").prop('disabled', false);
            }
        });
    }
    else {
        suc = info.sucursal + '-' + info.nombre;
        monto = info.total;
        recep = info.receptor;
        serie = info.serie;
        folio = info.folio;
        $("#serieR").val(serie);
        $("#folio").val(folio);
        $('.selectpicker').selectpicker('refresh');
    }
    $("#suc").val(suc);
    $("#monto").val(monto);
    $("#recepR").val(recep);
    
    /*if (info == "") {
        $("#folio, #serieR, #Limpiar").prop('disabled', true);
        $().toastmessage('showToast', {
            sticky: true,
            text: 'No se encontró ese Folio.',
            type: 'warning',
            position: 'middle-center',
            close: function () {
                $("#folio, #serieR").prop('disabled', false);
            }
        });
    }
    else {
        suc = info.sucursal + '-' + info.nombre;
        monto = info.total;
        recep = info.receptor;
        serie = info.serie;
        folio = info.folio;
        $("#serieR").val(serie);
        $("#folio").val(folio);
        $('.selectpicker').selectpicker('refresh');
    }
    $("#suc").val(suc);
    $("#monto").val(monto);
    $("#recepR").val(recep);*/
}

$("#lugexcp").change(function () {
    var cp = $(this).val();
    var info = {'cp':cp, 'fn':'actLugExp'};
    var r = AccionesFactura(info);
    if (r == '2'){
        $().toastmessage('showToast', {
            sticky: true,
            text: 'No existe CP.',
            type: 'warning',
            position: 'middle-center',
            close: function () {
                $("#lugexedo").val("");
            }
        });
    }
    else {
        $("#lugexedo").val(r);
    }
});

$("#recep").change(function () {
    var recep = $(this).val();
    var info = {'recep':recep, 'fn':'actRec'};
    var r = AccionesFactura(info);
    info = JSON.parse(r);
    vaciarSelect('uso_cfdi');
    llenarSelectUsoCfdi(info, 'uso_cfdi');
    $('.selectpicker').selectpicker('refresh');
});

$("#fch").change(function () {
    var fecha = $(this).val();
    var info = {'campo':'fecha', 'valor':fecha, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#uso_cfdi").change(function () {
    var usocfdi = $(this).val();
    var info = {'campo':'usoCFDI', 'valor':usocfdi, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#tiporel").change(function () {
    var rel = $(this).val();
    var info = {'campo':'tipoRelacion', 'valor':rel, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#cond_pago").change(function () {
    var cond_pago = $(this).val();
    var info = {'campo':'condicionesdepago', 'valor':cond_pago, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#metodoPago").change(function () {
    var mp = $(this).val();
    var info = {'campo':'metododepago', 'valor':mp, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#tip_camb").change(function () {
    var tc = $(this).val();
    var info = {'campo':'tipoCambio', 'valor':tc, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#md").change(function () {
    var md = $(this).val();
    var info = {'campo':'motivodescuento', 'valor':md, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#obs").change(function () {
    var obs = $(this).val();
    var info = {'campo':'observaciones', 'valor':obs, 'fn':'actDato'};
    AccionesFactura(info);
});

$("#formaPago").change(function () {
    var fp = $(this).val();
    var no_cta = document.getElementById('no_cta');
    var valor = false;
    if (fp == '01' || fp == '99' || fp == '0') {
        valor = true;
        no_cta.value = "";
        $("#no_cta").change();
    }
    var info = {'campo':'formadepago', 'valor':fp, 'fn':'actDato'};
    AccionesFactura(info);
    no_cta.disabled = valor;
});

$("#no_cta").change(function () {
    var fp = $("#formaPago").val();
    var no_cta = $(this).val();
    var info = {'campo': 'cuentadepago', 'valor': no_cta, 'fn': 'actDato'};
    AccionesFactura(info);
});

$("#catp, #catg").click(function () {
    $("#codprod, #nomprod").prop('disabled', false);
});

$("#tipo_comp").change(function () {
    var comp = $(this).val();
    var info = {'campo': 'tipodeComprobante', 'valor': comp, 'fn': 'actDato'};
    AccionesFactura(info);
});

$("#cta_pred").change(function (){
   //var cta = $(this).val();
   //var info = {'campo': 'ctapredial', 'valor': cta, 'fn': 'actCtaPred'};
   //AccionesFactura(info);
});


$("#serie").change(function () {
    var serie = $("#serie").val();
    var r = "";
    if (serie != 0) {
        var info = {'serie':serie, 'fn':'actSerie'};
        r = AccionesFactura(info);
        if (r == '2'){
            $("#suc, #monto, #recepR, #serieR, #folio").val("");
            $('.selectpicker').selectpicker('refresh');
            $("#cfdiR").show();
        }
        else {
            $("#cfdiR").hide();
            var info = {'fn':'borrarSRUG'};
            AccionesFactura(info);
        }
    }
    else {
        $("#cfdiR").hide();
        //var info = {'campo': 'serie', 'valor':serie, 'fn':'actDato'};
        //AccionesFactura(info);
    }
});

$("#codprod").change(function () {
    var cod = $(this).val();
    var rad = "input:radio[name='cat']:checked";
    var cat = $(rad).val();
    if (cat != undefined){
        if (cod != "") {
            var info = {'cod':cod,'cat':cat,'fn':'getProdCod'};
            var resp = AccionesFactura(info);
            if (resp == "2") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'El producto ya existe en la factura.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        $("#AgrProd").addClass("disabled");
                        $("#codprod").focus();
                    }
                });
            } else {
                info = JSON.parse(resp);
                $("#concep").val(info.Nombre);
                $("#nomprod").val(info.Nombre);
                $("#um").val(info.Unidad);
                $("#precio").val(info.Precio);
                $("#iva").val(info.IVA);
                $("#cant").focus();
                $("#AgrProd").removeClass("disabled");
            }
        }
        else
            $("#concep, #nomprod, #um, #precio, #iva").val("");
    }
    else {
        $().toastmessage('showToast', {
            sticky: true,
            text: 'Elija un catálogo',
            type: 'warning',
            position: 'middle-center'
        });
    }
});

$("#nomprod").change(function () {
    var nom = $(this).val();
    var rad = "input:radio[name='cat']:checked";
    var cat = $(rad).val();
    if (cat != undefined){
        if (nom != "") {
                var info = {'nom':nom,'cat':cat,'fn':'getProdNom'};
                var resp = AccionesFactura(info);
                if (resp == "2") {
                    $().toastmessage('showToast', {
                        sticky: true,
                        text: 'El producto ya existe en la factura.',
                        type: 'warning',
                        position: 'middle-center'
                    });
                } else {
                    info = JSON.parse(resp);
                    $("#codprod, #nomprod").prop('disabled', false);
                    $("#codprod").val(info.Id_Producto);
                    $("#concep").val(info.Nombre);
                    //$("#nomprod").val(info.Nombre);
                    $("#um").val(info.Unidad);
                    $("#precio").val(info.Precio);
                    $("#iva").val(info.IVA);
                    $("#cant").focus();
                    $("#AgrProd").removeClass("disabled");
                }
        }
        else
            $("#codprod, #concep, #nomprod, #um, #precio, #iva").val("");
    }
    else {
        $().toastmessage('showToast', {
            sticky: true,
            text: 'Elija un catálogo.',
            type: 'warning',
            position: 'middle-center'
        });
    }
});

$("#AgrProd").click(function(){
    if (validaciones()) {
        $("#AgrProd").addClass("disabled");
        $("#um, #iva, #concep").prop('disabled', false);
        var datos = JSON.stringify($("#frmFactura").serializeObject());
        $("#um, #iva, #concep").prop('disabled', true);
        $.ajax({
            async: false,
            url: 'ItemsFactura',
            type: 'POST',
            data: datos,
            success: function (resp) {
                $("#fctmnldet").load("ItemsFactura");
                $("#codprod, #concep, #nomprod, #um, #precio, #iva, #cant, #dcto").val("");
            }
        });
        $("#AgrProd").removeClass("disabled");
    }
});

function AccionesFactura(info) {
    var datos = JSON.stringify(info);
    var r = "";
    $.ajax({
        async: false,
        url: 'AccionesFactura',
        type: 'POST',
        data: datos,
        success: function (respuesta) {
            r = $.trim(respuesta);
            if (r == '0'){
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Error al actualizar.',
                    type: 'warning',
                    position: 'middle-center'
                });
            }
        }
    });
    return r;
}

$("#Limpiar").click(function() {
    document.getElementById("Guardar").disabled = false;
    $(".form-control").each(function (){
        $(this).val("");
    });
    $('.selectpicker').selectpicker('refresh');
    $("#cfdiR").hide();
});

function cmbClr(id, valor, ban){
    var id = document.getElementById(id);
    if(id.className != "slcI"){
        if(valor){ 
            id.className = "sobre";
        }else{
            if(ban) id.className = "outB";
            else id.className = "outC";
        }
    }
}
//-----------------------------------------------------------------------
function slcI(Id, ban){
    var id = document.getElementById(Id);
    if(id.className == "slcI"){
        if(ban) id.className = "outB";
        else id.className = "outC";
        if(itemSel != "" ){
            val = itemSel.split(",");
            itemSel = "";
            for(i = 0; i < val.length; i++){
                if( val[i] != Id ){
                    if( itemSel != "" ) itemSel += ",";
                    itemSel += val[i];
                }
            }
        }
    }else{
        if( itemSel != "" ) itemSel += ",";
        itemSel += Id;
        id.className  = "slcI";
    }
}

$("#RmvProd").click(function(){
    $(".modal-body").html("");
    $(".modal-footer").show();
    if( itemSel == "" ){
        $().toastmessage('showToast', {
            text     :'Debe seleccionar al menos un producto para eliminar.',
            sticky   : true,
            position : 'middle-center',
            type     : 'error',
            closeText: '',
            close    : function () {
            }
        });
    }else{
        var info = {'fn':'elIt', 'items':itemSel};
        var resp = AccionesFactura(info);
        $(".modal-body").html(resp);
        $("#btnModal").click();
    }
});

$("#btnSi").click(function(){
    var info = {'fn':'celIt', 'items':itemSel};
    var resp = AccionesFactura(info);
    if (resp == '2') {
        $().toastmessage('showToast', {
            sticky: true,
            text: 'Error al eliminar.',
            type: 'warning',
            position: 'middle-center'
        });
    } else {
        $().toastmessage('showToast', {
            sticky: true,
            text: 'Los registros se eliminaron correctamente.',
            type: 'success',
            position: 'middle-center'
        });
        $("#fctmnldet").load("ItemsFactura");
    }
});

$("#btnNo").click(function(){
});

$("#btnGenerar").click(function(){
    if (valDat()) {
        $(this).prop('disableb', true);
        var mtpg = $("#metodoPago").val();
        var no_cta = $("#no_cta").val();
        //var serie = $("#serie").val();
        //if (serie != "0") {
            var info = {'mtpg': mtpg, 'cta': no_cta, 'fn': 'genFac'};
            var resp = AccionesFactura(info);
            if (resp == "ok") {
                $().toastmessage('showToast', {
                    text: 'Los datos se verificaron correctamente. Ahora se generara la factura.',
                    sticky: true,
                    position: 'middle-center',
                    type: 'success',
                    closeText: '',
                    close: function () {
                        genFac();
                    }
                });
            } else {
                $().toastmessage('showToast', {
                    text: resp,
                    sticky: true,
                    position: 'middle-center',
                    type: 'error',
                    closeText: '',
                    close: function () {
                        $(this).prop('disableb', false);
                    }
                });
            }
        //}
    }
});

function genFac() {
    $.ajax({
        async: false,
        url: 'GenerarFactura',
        success: function (res) {
            var resp = $.trim(res);
            if( $.trim(resp) == "<b>Error:</b><br>" ){
                showMsjOk( 'El archivo se envio correctamente.' );
            }else{
                showMsjEr( resp );
            }
        }
    });
}

function showMsjOk( msj ){
    $.blockUI({
        message: 'Procesando Factura..',
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
    setTimeout(function(){
        $.unblockUI({
            onUnblock: function(){
                document.location.href = "ReporteFactura";
            }
        });
    }, 40000);
}

function showMsjEr( msj ) {
    $().toastmessage('showToast', {
        text     : msj,
        sticky   : true,
        position : 'middle-center',
        type     : 'error',
        closeText: '',
        close    : function () {
        }
    });
}

function validaciones(){
    var valido = true;
    var texto  = "";

    if( $("#codprod").val() == "" ){ valido = false; texto = texto+"- Codigo<br>"; }
    if( $("#concep").val() == "" ){ valido = false; texto = texto+"- Concepto<br>"; }
    if( $("#um").val() == "" ){ valido = false; texto = texto+"- Unidad de Medida 	<br>"; }
    if( $("#cant").val() == "" ){ valido = false; texto = texto+"- Cantidad<br>"; }
    if( $("#precio").val() == "" ){ valido = false; texto = texto+"- Precio Unitario<br>"; }
    if( $("#dcto").val() == "" ){ valido = false; texto = texto+"- Descuento<br>"; }
    if( $("#iva").val() == "" ){ valido = false; texto = texto+"- Impuesto<br>"; }

    if( !valido ) A("Debe Capturar:<br>"+texto,2);
    return valido;
}

function valDat(){
    var valido = true;
    var texto  = "";
    tipoDoc = $("#serie").find(':selected').data('ts');
    if( $("#recep").val() == 0 ){ valido = false; texto = texto+"- Receptor<br>"; }
    if( $("#serie").val() == 0 ){ valido = false; texto = texto+"- Serie<br>"; }
    if( $("#fch").val() == "" ){ valido = false; texto = texto+"- Fecha<br>"; }

    if( $("#uso_cfdi").val() == 0 ){ valido = false; texto = texto+"- Uso CFDI<br>"; }
    if(tipoDoc == 2){
        if( $("#monto").val() == "" ){ valido = false; texto = texto+"- CFDI Relacionado<br>"; }
        if( $("#tiporel").val() == 0 ){ valido = false; texto = texto+"- Tipo Relacion<br>"; }
    }
    if( $("#tipo_comp").val() == 0 ){ valido = false; texto = texto+"- Tipo de comprobante<br>"; }
    if( $("#lugexedo").val() == "" ){ valido = false; texto = texto+"- Lugar expedicion (CP)<br>"; }

    if( $("#cond_pago").val() == "" ){ valido = false; texto = texto+"- Condiciones de Pago<br>"; }
    if( $("#formaPago").val() == 0 ){ valido = false; texto = texto+"- Forma de Pago<br>"; }
    if( $("#metodoPago").val() == 0 ){ valido = false; texto = texto+"- Metodo de Pago<br>"; }
    if( $("#formaPago").val() != "01" && $("#formaPago").val() != "99" && $("#formaPago").val() != "0" ){
        if( $("#no_cta").val() == "" ){ valido = false; texto = texto+"- Numero de Cuenta<br>"; }
    }

    if( !valido ) A("Debe Capturar:<br>"+texto,2);
    return valido;
}

function A(text, n){
    $(".modal-body").html("");
    $(".modal-footer").hide();
    $("#btnModal").click();
    $(".modal-body").html(text);
}

function soloNumeros(e) {
    key = e.keyCode || e.which;
    teclado = String.fromCharCode(key);
    numeros="0123456789";
    if (numeros.indexOf(teclado)==-1)
        return false;
}
