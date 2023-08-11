$(document).ready(function() {
    $('#dataGrid').dataTable( {
        "sScrollX": "100%",
        "sScrollXInner": "100%",
        "bScrollCollapse": true,
        "language": {
            "url": "../public/js/Spanish.js"
        }
    });
});

function descargar(src){	
    //dire = "dwnldA.php?rt="+src;
    var rutaExcel = 'http://localhost/sife/web/app_dev.php/DescargaFactura?src='+src;
    window.open(rutaExcel, '_blank' );
}
