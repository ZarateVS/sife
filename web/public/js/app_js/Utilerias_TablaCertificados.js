$(document).ready(function() {
    $("#table").DataTable({
        "language": {
            "url": "../public/js/Spanish.js"
        }
    });
});

$("a#Descargar").click(function(){
    var id = $(this).data('id');
    descargar(id);
});

function descargar(id) {
    /*$.ajax({
       url:'DescargarArchivos',
       type:'POST',
       data:'id='+id,
       success: function(r) {
           if(r) {
                window.open(this.url, '_blank' );
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Descarga exitosa.',
                    type: 'success',
                    position: 'middle-center'
                });
           }
           else {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No se pudo realizar la descarga.',
                    type: 'error',
                    position: 'middle-center'
                });
           }
       }
    });*/
    window.open('http://localhost/sife/web/app_dev.php/DescargarCertificado?id='+id, '_blank' );
}