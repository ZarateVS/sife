$(document).ready(function(){
    $("#catalogo").load("ObtenerSucursales");
});

$("#Exportar").click(function(){
    window.open('http://localhost/sife/web/app_dev.php/Excel', '_blank' );
});

$("catalogo").on("click", '#Exportar', function() {

});