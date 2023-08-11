$(document).ready(function() {
    var dsc = $("#dsc").text();
    if (dsc == 0) {
        $("#md").text("");
        $("#md").hide();
        var info = {'campo':'motivodescuento','valor':'','fn':'actDato'};
        AccionesFactura(info);
    }
    else {
        $("#md").show();
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