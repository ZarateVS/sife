$(document).ready(function(){
    $("#catalogo").load("ObtenerProductos");
});

$("#Agregar").click(function () {
    document.location.href = "AgregarProducto";
});