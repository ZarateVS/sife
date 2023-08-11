$(document).ready(function(){
    $("#listado").load("ObtenerUsuariosConSucursalesAsignadas");
});

$("#Agregar").click(function () {
    document.location.href = "AsignarUsuarioSucursal";
});