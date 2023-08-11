$(document).ready(function(){
    $("#listado").load("ObtenerUsuariosConMenusAsignados");
});

$("#Agregar").click(function () {
    document.location.href = "AgregarUsuarioMenu";
});