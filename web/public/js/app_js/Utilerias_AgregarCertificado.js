var form = document.getElementById("frmCertificado");
var time;

$(document).ready(function() {
    form.addEventListener("submit", function(event) {
        event.preventDefault();
    });
    
    $("#input-fa-1").fileinput({
        language: "es",
        theme: 'fa',
        //allowedFileExtensions: ["cer", "key", "pem"],
        hideThumbnailContent: true,
        showUpload: false,
        showRemove: true
        //maxFileCount: 2
    });
    //startTimer();
    //time = setInterval('contador()', 9000);
});

$("#ResetTimer").click(function() {
    startTimer();
});

function startTimer() {
    clearInterval(time);
    time = setInterval('contador()', 9000);
}

function contador(){
    //alert("Hello");
    alert(time);
}

$("#Limpiar").click(function() {
    document.getElementById("Guardar").disabled = false;
    $(".form-control").each(function (){
        $(this).val("");
    });
    $('.selectpicker').selectpicker('refresh');
});


$("#Guardar").click(function() {
    var xhttp = new XMLHttpRequest();
    document.getElementById("Guardar").disabled = true;
    if (validarCampos()) {
        $.blockUI({
            message: 'Procesando. ¡Espere por favor!',
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
        //var form = document.getElementById("frmCertificado");
        var datos = new FormData(form);
        xhttp.open("POST", "AgregarCertificado");
        xhttp.send(datos);
    }
    else {
        $().toastmessage('showToast', {
            sticky: true,
            text: 'Complete todos los campos',
            type: 'warning',
            position: 'middle-center',
            close: function () {
                document.getElementById("Guardar").disabled = false;
            }
        });
    }
    
    xhttp.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
           // Action to be performed when the document is read;
           var respuesta = this.responseText;
           //alert(respuesta);
            if ($.trim(respuesta) == "1") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Operación completa.',
                    type: 'success',
                    position: 'middle-center',
                    close: function () {
                        document.location.href = "Certificado";
                    }
                });
            } else if ($.trim(respuesta) == "2") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Se requiere un certificado (.cer)<br> y una llave privada (.key).',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else if ($.trim(respuesta) == "3") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No ha proporcionado una<br> contraseña o es incorrecta.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else if ($.trim(respuesta) == "4") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'No se pudo agregar el certificado.',
                    type: 'error',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else if ($.trim(respuesta) == "5") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Certificado no vigente.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else if ($.trim(respuesta) == "6") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'El archivo .key no<br> corresponde al certificado.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else if ($.trim(respuesta) == "7") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'El archivo .cer proporcionado<br> no es un Certificado de Sello Digital.',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else if ($.trim(respuesta) == "8") {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: 'Ya existe un certificado con este número de serie',
                    type: 'warning',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            } else {
                $().toastmessage('showToast', {
                    sticky: true,
                    text: respuesta,
                    type: 'error',
                    position: 'middle-center',
                    close: function () {
                        document.getElementById("Guardar").disabled = false;
                    }
                });
            }
            $.unblockUI();
        }
    };

    /*document.getElementById("Guardar").disabled = true;
    var datos = JSON.stringify($("#frmCertificado").serializeObject());
    alert(datos);*/
    
    /*var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function(r) {
        if (this.readyState == 4 && this.status == 200) {
           // Action to be performed when the document is read;
           $("#pwd").val("Metodo post");
           alert(r);
        }
    };
    
    var form = document.getElementById("frmCertificado");
    xhttp.open("POST", "ruta");
    //xhttp.send();
    xhttp.send(new FormData(form));
    //$("#pwd").val();*/
    /*var a_cer = $("#arch_cer").val();
    var a_key = $("#arch_key").val();
    var pwd = $("#pwd").val();
    alert(a_cer);*/
    
    /*$.ajax({
        contentType:"multipart/form-data; charset = UTF-8",
        url: 'SubirArchivos',
        type: "POST",
        success: function(respuesta){
            alert(respuesta);
        }
    });*/
    
    /*var data = new FormData();
    $.each($('#frmCertificado')[0].files, function(i, file) {
        data.append('file-'+i, file);
    });
    
    $.ajax({
        url: 'SubirArchivos',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        type: 'POST', // For jQuery < 1.9
        success: function(data){
            alert(data);
        }
    });*/
    /*document.addEventListener("DOMContentLoaded", function() {
        var form = document.getElementById("frmCertificado");

        form.addEventListener("submit", function(event) {
           event.preventDefault();
           subir_archivo(this);
        });
    });*/
});
function validarCampos(){
    var pwd = $("#pwd").val();
    var rs = $("#rs").val();
    
    if (pwd === "" && rs === "")
        return 0;
    
    if (pwd.length > 50)
        return 0;
    
    return 1;
}